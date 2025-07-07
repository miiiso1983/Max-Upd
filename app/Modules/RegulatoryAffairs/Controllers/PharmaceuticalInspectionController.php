<?php

namespace App\Modules\RegulatoryAffairs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalInspection;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalProduct;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalBatch;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PharmaceuticalInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = PharmaceuticalInspection::with(['company', 'product', 'batch']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('inspection_number', 'like', "%{$search}%")
                  ->orWhere('inspection_scope', 'like', "%{$search}%")
                  ->orWhere('regulatory_authority', 'like', "%{$search}%")
                  ->orWhere('inspection_team_lead', 'like', "%{$search}%");
            });
        }

        if ($request->filled('inspection_type')) {
            $query->where('inspection_type', $request->get('inspection_type'));
        }

        if ($request->filled('inspection_status')) {
            $query->where('inspection_status', $request->get('inspection_status'));
        }

        if ($request->filled('inspection_result')) {
            $query->where('inspection_result', $request->get('inspection_result'));
        }

        if ($request->filled('inspecting_authority')) {
            $query->where('regulatory_authority', $request->get('inspecting_authority'));
        }

        if ($request->filled('company_id')) {
            $query->where('pharmaceutical_company_id', $request->get('company_id'));
        }

        $inspections = $query->orderBy('inspection_date', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total' => PharmaceuticalInspection::count(),
            'satisfactory' => PharmaceuticalInspection::where('inspection_result', 'satisfactory')->count(),
            'minor_deficiencies' => PharmaceuticalInspection::where('inspection_result', 'minor_deficiencies')->count(),
            'major_deficiencies' => PharmaceuticalInspection::where('inspection_result', 'major_deficiencies')->count(),
            'scheduled' => PharmaceuticalInspection::where('inspection_status', 'scheduled')->count(),
        ];

        // Get unique inspecting authorities for filter
        $inspectingAuthorities = PharmaceuticalInspection::distinct()
            ->pluck('regulatory_authority')
            ->filter()
            ->sort()
            ->values();

        return view('regulatory-affairs.inspections.index', compact(
            'inspections',
            'stats',
            'inspectingAuthorities'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $companies = PharmaceuticalCompany::where('status', 'active')->get();
        $products = PharmaceuticalProduct::where('license_status', 'active')->get();
        $batches = PharmaceuticalBatch::where('batch_status', 'released')->get();

        // Pre-select based on request parameters
        $selectedCompany = $request->get('company_id');
        $selectedProduct = $request->get('product_id');
        $selectedBatch = $request->get('batch_id');

        return view('regulatory-affairs.inspections.create', compact(
            'companies',
            'products',
            'batches',
            'selectedCompany',
            'selectedProduct',
            'selectedBatch'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'inspection_number' => 'required|string|unique:pharmaceutical_inspections',
            'inspection_type' => 'required|in:routine,for_cause,pre_approval,surveillance,follow_up',
            'inspection_scope' => 'required|string',
            'inspected_entity_type' => 'required|in:company,product,batch',
            'pharmaceutical_company_id' => 'nullable|exists:pharmaceutical_companies,id',
            'pharmaceutical_product_id' => 'nullable|exists:pharmaceutical_products,id',
            'pharmaceutical_batch_id' => 'nullable|exists:pharmaceutical_batches,id',
            'regulatory_authority' => 'required|string',
            'inspection_team_lead' => 'required|string',
            'inspection_date' => 'required|date',
            'inspection_end_date' => 'nullable|date|after_or_equal:inspection_date',
            'inspection_status' => 'required|in:scheduled,in_progress,completed,cancelled,postponed',
            'inspection_result' => 'nullable|in:satisfactory,minor_deficiencies,major_deficiencies,critical_deficiencies,non_compliant',
        ]);

        $validated['created_by'] = auth()->id();

        $inspection = PharmaceuticalInspection::create($validated);

        return redirect()
            ->route('regulatory-affairs.inspections.show', $inspection)
            ->with('success', 'تم إنشاء التفتيش بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(PharmaceuticalInspection $inspection): View
    {
        $inspection->load(['company', 'product', 'batch', 'creator']);

        // Get related inspections
        $relatedInspections = PharmaceuticalInspection::where('id', '!=', $inspection->id)
            ->where(function ($query) use ($inspection) {
                if ($inspection->pharmaceutical_company_id) {
                    $query->where('pharmaceutical_company_id', $inspection->pharmaceutical_company_id);
                }
                if ($inspection->pharmaceutical_product_id) {
                    $query->orWhere('pharmaceutical_product_id', $inspection->pharmaceutical_product_id);
                }
                if ($inspection->pharmaceutical_batch_id) {
                    $query->orWhere('pharmaceutical_batch_id', $inspection->pharmaceutical_batch_id);
                }
            })
            ->orderBy('inspection_date', 'desc')
            ->limit(5)
            ->get();

        // Generate alerts
        $alerts = [];
        
        if ($inspection->inspection_status === 'scheduled' && $inspection->inspection_date->isPast()) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'تاريخ التفتيش قد مضى ولم يتم تحديث الحالة'
            ];
        }

        if ($inspection->corrective_actions_required && $inspection->corrective_action_deadline && $inspection->corrective_action_deadline->isPast()) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'انتهت مهلة الإجراءات التصحيحية المطلوبة'
            ];
        }

        if ($inspection->follow_up_required && $inspection->follow_up_date && $inspection->follow_up_date->isPast()) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'حان موعد المتابعة المطلوبة'
            ];
        }

        return view('regulatory-affairs.inspections.show', compact(
            'inspection',
            'relatedInspections',
            'alerts'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PharmaceuticalInspection $inspection): View
    {
        $companies = PharmaceuticalCompany::where('status', 'active')->get();
        $products = PharmaceuticalProduct::where('license_status', 'active')->get();
        $batches = PharmaceuticalBatch::where('batch_status', 'released')->get();

        return view('regulatory-affairs.inspections.edit', compact(
            'inspection',
            'companies',
            'products',
            'batches'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PharmaceuticalInspection $inspection): RedirectResponse
    {
        $validated = $request->validate([
            'inspection_number' => 'required|string|unique:pharmaceutical_inspections,inspection_number,' . $inspection->id,
            'inspection_type' => 'required|in:routine,for_cause,pre_approval,surveillance,follow_up',
            'inspection_scope' => 'required|string',
            'inspected_entity_type' => 'required|in:company,product,batch',
            'pharmaceutical_company_id' => 'nullable|exists:pharmaceutical_companies,id',
            'pharmaceutical_product_id' => 'nullable|exists:pharmaceutical_products,id',
            'pharmaceutical_batch_id' => 'nullable|exists:pharmaceutical_batches,id',
            'regulatory_authority' => 'required|string',
            'inspection_team_lead' => 'required|string',
            'inspection_date' => 'required|date',
            'inspection_end_date' => 'nullable|date|after_or_equal:inspection_date',
            'inspection_status' => 'required|in:scheduled,in_progress,completed,cancelled,postponed',
            'inspection_result' => 'nullable|in:satisfactory,minor_deficiencies,major_deficiencies,critical_deficiencies,non_compliant',
        ]);

        $inspection->update($validated);

        return redirect()
            ->route('regulatory-affairs.inspections.show', $inspection)
            ->with('success', 'تم تحديث التفتيش بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PharmaceuticalInspection $inspection): RedirectResponse
    {
        $inspection->delete();

        return redirect()
            ->route('regulatory-affairs.inspections.index')
            ->with('success', 'تم حذف التفتيش بنجاح');
    }

    /**
     * Update inspection status
     */
    public function updateStatus(Request $request, PharmaceuticalInspection $inspection): RedirectResponse
    {
        $validated = $request->validate([
            'inspection_status' => 'required|in:scheduled,in_progress,completed,cancelled,postponed',
            'inspection_result' => 'nullable|in:satisfactory,minor_deficiencies,major_deficiencies,critical_deficiencies,non_compliant',
        ]);

        $inspection->update($validated);

        return back()->with('success', 'تم تحديث حالة التفتيش بنجاح');
    }

    /**
     * Generate inspection report
     */
    public function generateReport(PharmaceuticalInspection $inspection)
    {
        // This would generate a PDF report
        // Implementation depends on your PDF library (e.g., DomPDF, TCPDF)
        
        return response()->json([
            'message' => 'سيتم إنشاء تقرير PDF للتفتيش',
            'inspection_id' => $inspection->id
        ]);
    }
}
