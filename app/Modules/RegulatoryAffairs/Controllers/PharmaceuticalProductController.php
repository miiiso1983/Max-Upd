<?php

namespace App\Modules\RegulatoryAffairs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalProduct;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PharmaceuticalProductController extends Controller
{
    /**
     * Display a listing of pharmaceutical products
     */
    public function index(Request $request)
    {
        $query = PharmaceuticalProduct::with(['company', 'creator', 'updater']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('trade_name', 'like', "%{$search}%")
                  ->orWhere('trade_name_ar', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('generic_name_ar', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%")
                  ->orWhere('active_ingredient', 'like', "%{$search}%")
                  ->orWhere('active_ingredient_ar', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by license status
        if ($request->filled('license_status')) {
            $query->where('license_status', $request->license_status);
        }

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('pharmaceutical_company_id', $request->company_id);
        }

        // Filter by therapeutic class
        if ($request->filled('therapeutic_class')) {
            $query->where('therapeutic_class', $request->therapeutic_class);
        }

        // Filter by prescription status
        if ($request->filled('prescription_status')) {
            $query->where('prescription_status', $request->prescription_status);
        }

        // Filter by market status
        if ($request->filled('market_status')) {
            $query->where('market_status', $request->market_status);
        }

        // Filter expiring licenses
        if ($request->filled('expiring_licenses')) {
            $days = $request->get('expiring_days', 30);
            $query->licenseExpiring($days);
        }

        // Filter generic/brand
        if ($request->filled('is_generic')) {
            $query->where('is_generic', $request->boolean('is_generic'));
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total' => PharmaceuticalProduct::count(),
            'active' => PharmaceuticalProduct::where('status', 'active')->count(),
            'marketed' => PharmaceuticalProduct::where('market_status', 'marketed')->count(),
            'license_expiring' => PharmaceuticalProduct::licenseExpiring(30)->count(),
            'generic' => PharmaceuticalProduct::where('is_generic', true)->count(),
        ];

        // Get filter options
        $companies = PharmaceuticalCompany::where('status', 'active')
                                        ->orderBy('name')
                                        ->get(['id', 'name', 'name_ar']);

        $therapeuticClasses = PharmaceuticalProduct::distinct('therapeutic_class')
                                                 ->pluck('therapeutic_class')
                                                 ->filter()
                                                 ->sort()
                                                 ->values();

        return view('regulatory-affairs.products.index', compact(
            'products', 
            'stats', 
            'companies', 
            'therapeuticClasses'
        ));
    }

    /**
     * Show the form for creating a new pharmaceutical product
     */
    public function create()
    {
        $companies = PharmaceuticalCompany::where('status', 'active')
                                        ->orderBy('name')
                                        ->get(['id', 'name', 'name_ar']);

        return view('regulatory-affairs.products.create', compact('companies'));
    }

    /**
     * Store a newly created pharmaceutical product
     */
    public function store(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string|unique:pharmaceutical_products',
            'trade_name' => 'required|string|max:255',
            'trade_name_ar' => 'nullable|string|max:255',
            'generic_name' => 'required|string|max:255',
            'active_ingredient' => 'required|string|max:255',
            'strength' => 'required|string|max:100',
            'dosage_form' => 'required|string|max:100',
            'pharmaceutical_company_id' => 'required|exists:pharmaceutical_companies,id',
            'country_of_origin' => 'required|string|max:100',
            'therapeutic_class' => 'required|string|max:255',
            'license_number' => 'required|string|unique:pharmaceutical_products',
            'license_issue_date' => 'required|date',
            'license_expiry_date' => 'required|date|after:license_issue_date',
            'regulatory_authority' => 'required|string|max:255',
            'prescription_status' => 'required|in:prescription,otc,controlled',
            'shelf_life_months' => 'required|integer|min:1|max:120',
            'storage_conditions' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $product = PharmaceuticalProduct::create([
                ...$request->all(),
                'created_by' => auth()->user()?->id,
            ]);

            DB::commit();

            return redirect()->route('regulatory-affairs.products.show', $product)
                           ->with('success', 'تم إنشاء المنتج الدوائي بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء المنتج: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified pharmaceutical product
     */
    public function show(PharmaceuticalProduct $product)
    {
        $product->load(['company', 'batches', 'inspections.creator']);

        // Get product statistics
        $stats = [
            'batches_count' => $product->batches()->count(),
            'active_batches' => $product->batches()->where('batch_status', 'released')->count(),
            'inspections_count' => $product->inspections()->count(),
            'last_inspection' => $product->inspections()->latest('inspection_date')->first(),
            'compliance_score' => $product->getComplianceScore(),
        ];

        // Get recent activities
        $recentBatches = $product->batches()->latest()->take(5)->get();
        $recentInspections = $product->inspections()->latest()->take(5)->get();

        // Check for alerts
        $alerts = [];
        
        if ($product->isLicenseExpiring(30)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'ترخيص المنتج ينتهي خلال ' . $product->getDaysUntilLicenseExpiry() . ' يوم',
            ];
        }

        if ($product->isBioequivalenceExpiring(30)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'دراسة التكافؤ الحيوي تنتهي قريباً',
            ];
        }

        if ($product->needsInspection()) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'المنتج يحتاج إلى تفتيش',
            ];
        }

        return view('regulatory-affairs.products.show', compact(
            'product', 
            'stats', 
            'recentBatches', 
            'recentInspections', 
            'alerts'
        ));
    }

    /**
     * Show the form for editing the specified pharmaceutical product
     */
    public function edit(PharmaceuticalProduct $product)
    {
        $companies = PharmaceuticalCompany::where('status', 'active')
                                        ->orderBy('name')
                                        ->get(['id', 'name', 'name_ar']);

        return view('regulatory-affairs.products.edit', compact('product', 'companies'));
    }

    /**
     * Update the specified pharmaceutical product
     */
    public function update(Request $request, PharmaceuticalProduct $product)
    {
        $request->validate([
            'registration_number' => 'required|string|unique:pharmaceutical_products,registration_number,' . $product->id,
            'trade_name' => 'required|string|max:255',
            'trade_name_ar' => 'nullable|string|max:255',
            'generic_name' => 'required|string|max:255',
            'active_ingredient' => 'required|string|max:255',
            'strength' => 'required|string|max:100',
            'dosage_form' => 'required|string|max:100',
            'pharmaceutical_company_id' => 'required|exists:pharmaceutical_companies,id',
            'country_of_origin' => 'required|string|max:100',
            'therapeutic_class' => 'required|string|max:255',
            'license_number' => 'required|string|unique:pharmaceutical_products,license_number,' . $product->id,
            'license_issue_date' => 'required|date',
            'license_expiry_date' => 'required|date|after:license_issue_date',
            'regulatory_authority' => 'required|string|max:255',
            'prescription_status' => 'required|in:prescription,otc,controlled',
            'shelf_life_months' => 'required|integer|min:1|max:120',
            'storage_conditions' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $product->update([
                ...$request->all(),
                'updated_by' => auth()->user()?->id,
            ]);

            DB::commit();

            return redirect()->route('regulatory-affairs.products.show', $product)
                           ->with('success', 'تم تحديث المنتج الدوائي بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث المنتج: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified pharmaceutical product
     */
    public function destroy(PharmaceuticalProduct $product)
    {
        try {
            DB::beginTransaction();

            // Check if product has batches
            if ($product->batches()->count() > 0) {
                return back()->with('error', 'لا يمكن حذف المنتج لأنه يحتوي على دفعات مسجلة');
            }

            $product->delete();

            DB::commit();

            return redirect()->route('regulatory-affairs.products.index')
                           ->with('success', 'تم حذف المنتج الدوائي بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف المنتج: ' . $e->getMessage());
        }
    }

    /**
     * Get products expiring licenses report
     */
    public function expiringLicenses(Request $request)
    {
        $days = $request->get('days', 30);
        
        $products = PharmaceuticalProduct::licenseExpiring($days)
                                       ->with(['company', 'creator'])
                                       ->orderBy('license_expiry_date', 'asc')
                                       ->paginate(20);

        return view('regulatory-affairs.products.expiring-licenses', compact('products', 'days'));
    }

    /**
     * Update product status
     */
    public function updateStatus(Request $request, PharmaceuticalProduct $product)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,under_review,suspended',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $product->update([
                'status' => $request->status,
                'notes' => $request->reason ? $product->notes . "\n" . now()->format('Y-m-d H:i') . ": " . $request->reason : $product->notes,
                'updated_by' => auth()->user()?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة المنتج بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update market status
     */
    public function updateMarketStatus(Request $request, PharmaceuticalProduct $product)
    {
        $request->validate([
            'market_status' => 'required|in:marketed,not_marketed,discontinued,withdrawn',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $updateData = [
                'market_status' => $request->market_status,
                'updated_by' => auth()->user()?->id,
            ];

            if ($request->market_status === 'marketed' && !$product->market_launch_date) {
                $updateData['market_launch_date'] = now();
            }

            if (in_array($request->market_status, ['discontinued', 'withdrawn']) && $request->reason) {
                $updateData['withdrawal_reason'] = $request->reason;
            }

            $product->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة التسويق بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة التسويق: ' . $e->getMessage(),
            ], 500);
        }
    }
}
