<?php

namespace App\Modules\RegulatoryAffairs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalBatch;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PharmaceuticalBatchController extends Controller
{
    /**
     * Display a listing of pharmaceutical batches
     */
    public function index(Request $request)
    {
        $query = PharmaceuticalBatch::with(['product.company', 'creator', 'updater']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhere('lot_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('trade_name', 'like', "%{$search}%")
                         ->orWhere('trade_name_ar', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('pharmaceutical_product_id', $request->product_id);
        }

        // Filter by batch status
        if ($request->filled('batch_status')) {
            $query->where('batch_status', $request->batch_status);
        }

        // Filter by testing status
        if ($request->filled('testing_status')) {
            $query->where('testing_status', $request->testing_status);
        }

        // Filter by manufacturing date
        if ($request->filled('manufacturing_date_from')) {
            $query->where('manufacturing_date', '>=', $request->manufacturing_date_from);
        }

        if ($request->filled('manufacturing_date_to')) {
            $query->where('manufacturing_date', '<=', $request->manufacturing_date_to);
        }

        // Filter by expiry date
        if ($request->filled('expiry_date_from')) {
            $query->where('expiry_date', '>=', $request->expiry_date_from);
        }

        if ($request->filled('expiry_date_to')) {
            $query->where('expiry_date', '<=', $request->expiry_date_to);
        }

        // Filter expiring batches
        if ($request->filled('expiring_batches')) {
            $days = $request->get('expiring_days', 30);
            $query->expiring($days);
        }

        // Filter recalled batches
        if ($request->filled('recalled_batches')) {
            $query->recalled();
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total' => PharmaceuticalBatch::count(),
            'released' => PharmaceuticalBatch::where('batch_status', 'released')->count(),
            'in_testing' => PharmaceuticalBatch::where('batch_status', 'testing')->count(),
            'rejected' => PharmaceuticalBatch::where('batch_status', 'rejected')->count(),
            'expiring' => PharmaceuticalBatch::expiring(30)->count(),
            'recalled' => PharmaceuticalBatch::where('recall_issued', true)->count(),
        ];

        // Get filter options
        $products = PharmaceuticalProduct::where('status', 'active')
                                       ->orderBy('trade_name')
                                       ->get(['id', 'trade_name', 'trade_name_ar']);

        return view('regulatory-affairs.batches.index', compact('batches', 'stats', 'products'));
    }

    /**
     * Show the form for creating a new pharmaceutical batch
     */
    public function create(Request $request)
    {
        $products = PharmaceuticalProduct::where('status', 'active')
                                       ->orderBy('trade_name')
                                       ->get(['id', 'trade_name', 'trade_name_ar']);

        $selectedProduct = null;
        if ($request->filled('product_id')) {
            $selectedProduct = PharmaceuticalProduct::find($request->product_id);
        }

        return view('regulatory-affairs.batches.create', compact('products', 'selectedProduct'));
    }

    /**
     * Store a newly created pharmaceutical batch
     */
    public function store(Request $request)
    {
        $request->validate([
            'pharmaceutical_product_id' => 'required|exists:pharmaceutical_products,id',
            'batch_number' => 'required|string|unique:pharmaceutical_batches',
            'manufacturing_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'required|date|after:manufacturing_date',
            'quantity_manufactured' => 'required|integer|min:1',
            'manufacturing_site' => 'required|string|max:255',
            'production_line' => 'nullable|string|max:100',
            'supervisor' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $batch = PharmaceuticalBatch::create([
                ...$request->all(),
                'created_by' => auth()->user()?->id,
            ]);

            DB::commit();

            return redirect()->route('regulatory-affairs.batches.show', $batch)
                           ->with('success', 'تم إنشاء الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified pharmaceutical batch
     */
    public function show(PharmaceuticalBatch $batch)
    {
        $batch->load(['product.company', 'tests', 'inspections.creator']);

        // Get batch statistics
        $stats = [
            'tests_count' => $batch->tests()->count(),
            'passed_tests' => $batch->getPassedTestsCount(),
            'failed_tests' => $batch->getFailedTestsCount(),
            'pending_tests' => $batch->getPendingTestsCount(),
            'testing_progress' => $batch->getTestingProgress(),
            'compliance_score' => $batch->getComplianceScore(),
            'quantity_available' => $batch->getQuantityAvailable(),
            'shelf_life_remaining' => $batch->getShelfLifeRemaining(),
        ];

        // Get recent activities
        $recentTests = $batch->tests()->latest()->take(5)->get();
        $recentInspections = $batch->inspections()->latest()->take(5)->get();

        // Check for alerts
        $alerts = [];
        
        if ($batch->isExpiring(30)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'الدفعة تنتهي خلال ' . $batch->getDaysUntilExpiry() . ' يوم',
            ];
        }

        if ($batch->hasFailedTests()) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'الدفعة تحتوي على فحوصات فاشلة',
            ];
        }

        if ($batch->needsTesting()) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'الدفعة تحتاج إلى فحوصات',
            ];
        }

        if ($batch->isRecalled()) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'تم سحب هذه الدفعة من السوق',
            ];
        }

        return view('regulatory-affairs.batches.show', compact(
            'batch', 
            'stats', 
            'recentTests', 
            'recentInspections', 
            'alerts'
        ));
    }

    /**
     * Show the form for editing the specified pharmaceutical batch
     */
    public function edit(PharmaceuticalBatch $batch)
    {
        $products = PharmaceuticalProduct::where('status', 'active')
                                       ->orderBy('trade_name')
                                       ->get(['id', 'trade_name', 'trade_name_ar']);

        return view('regulatory-affairs.batches.edit', compact('batch', 'products'));
    }

    /**
     * Update the specified pharmaceutical batch
     */
    public function update(Request $request, PharmaceuticalBatch $batch)
    {
        $request->validate([
            'pharmaceutical_product_id' => 'required|exists:pharmaceutical_products,id',
            'batch_number' => 'required|string|unique:pharmaceutical_batches,batch_number,' . $batch->id,
            'manufacturing_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'required|date|after:manufacturing_date',
            'quantity_manufactured' => 'required|integer|min:1',
            'manufacturing_site' => 'required|string|max:255',
            'production_line' => 'nullable|string|max:100',
            'supervisor' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $batch->update([
                ...$request->all(),
                'updated_by' => auth()->user()?->id,
            ]);

            DB::commit();

            return redirect()->route('regulatory-affairs.batches.show', $batch)
                           ->with('success', 'تم تحديث الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified pharmaceutical batch
     */
    public function destroy(PharmaceuticalBatch $batch)
    {
        try {
            DB::beginTransaction();

            // Check if batch has tests
            if ($batch->tests()->count() > 0) {
                return back()->with('error', 'لا يمكن حذف الدفعة لأنها تحتوي على فحوصات مسجلة');
            }

            // Check if batch is released
            if ($batch->batch_status === 'released') {
                return back()->with('error', 'لا يمكن حذف دفعة مطلقة');
            }

            $batch->delete();

            DB::commit();

            return redirect()->route('regulatory-affairs.batches.index')
                           ->with('success', 'تم حذف الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Get expiring batches report
     */
    public function expiringBatches(Request $request)
    {
        $days = $request->get('days', 30);
        
        $batches = PharmaceuticalBatch::expiring($days)
                                    ->with(['product.company', 'creator'])
                                    ->orderBy('expiry_date', 'asc')
                                    ->paginate(20);

        return view('regulatory-affairs.batches.expiring', compact('batches', 'days'));
    }

    /**
     * Update batch status
     */
    public function updateStatus(Request $request, PharmaceuticalBatch $batch)
    {
        $request->validate([
            'batch_status' => 'required|in:in_production,testing,released,quarantine,rejected,recalled,destroyed',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $updateData = [
                'batch_status' => $request->batch_status,
                'updated_by' => auth()->user()?->id,
            ];

            if ($request->batch_status === 'released' && !$batch->release_date) {
                $updateData['release_date'] = now();
                $updateData['released_by'] = auth()->user()?->name;
            }

            if ($request->reason) {
                $updateData['notes'] = $batch->notes . "\n" . now()->format('Y-m-d H:i') . ": " . $request->reason;
            }

            $batch->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الدفعة بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Issue recall for batch
     */
    public function issueRecall(Request $request, PharmaceuticalBatch $batch)
    {
        $request->validate([
            'recall_reason' => 'required|string|max:1000',
            'recall_level' => 'required|in:consumer,retail,wholesale',
        ]);

        try {
            $batch->update([
                'recall_issued' => true,
                'recall_date' => now(),
                'recall_reason' => $request->recall_reason,
                'recall_level' => $request->recall_level,
                'batch_status' => 'recalled',
                'updated_by' => auth()->user()?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إصدار أمر سحب الدفعة بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إصدار أمر السحب: ' . $e->getMessage(),
            ], 500);
        }
    }
}
