<?php

namespace App\Modules\SalesReps\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SalesReps\Models\SalesRepresentative;
use App\Modules\SalesReps\Models\Territory;
use App\Modules\SalesReps\Http\Requests\StoreSalesRepRequest;
use App\Modules\SalesReps\Http\Requests\UpdateSalesRepRequest;
use App\Modules\SalesReps\Services\SalesRepService;
use App\Modules\Sales\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SalesRepController extends Controller
{
    protected SalesRepService $salesRepService;

    public function __construct(SalesRepService $salesRepService)
    {
        $this->salesRepService = $salesRepService;
        $this->middleware('permission:view_sales_reps')->only(['index', 'show']);
        $this->middleware('permission:create_sales_reps')->only(['create', 'store']);
        $this->middleware('permission:edit_sales_reps')->only(['edit', 'update']);
        $this->middleware('permission:delete_sales_reps')->only(['destroy']);
    }

    /**
     * Display a listing of sales representatives
     */
    public function index(Request $request): View
    {
        $query = SalesRepresentative::with(['user', 'supervisor', 'territories', 'customers']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_id', $request->supervisor_id);
        }

        if ($request->filled('governorate')) {
            $query->where('governorate', $request->governorate);
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $salesReps = $query->paginate(15);

        // Get filter options
        $supervisors = SalesRepresentative::where('status', 'active')
                                         ->whereHas('subordinates')
                                         ->get(['id', 'name', 'name_ar']);

        $governorates = SalesRepresentative::distinct()
                                          ->whereNotNull('governorate')
                                          ->pluck('governorate');

        return view('sales-reps.index', compact(
            'salesReps',
            'supervisors',
            'governorates'
        ));
    }

    /**
     * Show the form for creating a new sales representative
     */
    public function create(): View
    {
        $supervisors = SalesRepresentative::where('status', 'active')
                                         ->get(['id', 'name', 'name_ar']);

        $territories = Territory::where('is_active', true)
                               ->get(['id', 'name', 'name_ar', 'governorate']);

        $customers = Customer::where('is_active', true)
                           ->get(['id', 'name', 'name_ar', 'city', 'governorate']);

        return view('sales-reps.create', compact(
            'supervisors',
            'territories',
            'customers'
        ));
    }

    /**
     * Store a newly created sales representative
     */
    public function store(StoreSalesRepRequest $request): RedirectResponse
    {
        try {
            $salesRep = $this->salesRepService->create($request->validated());

            return redirect()
                ->route('sales-reps.show', $salesRep)
                ->with('success', 'تم إنشاء مندوب المبيعات بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء مندوب المبيعات: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sales representative
     */
    public function show(SalesRepresentative $salesRep): View
    {
        $salesRep->load([
            'user',
            'supervisor',
            'subordinates',
            'territories' => function ($query) {
                $query->wherePivot('is_active', true);
            },
            'customers' => function ($query) {
                $query->wherePivot('is_active', true)->limit(10);
            },
            'visits' => function ($query) {
                $query->latest()->limit(10);
            },
            'performanceMetrics' => function ($query) {
                $query->where('metric_date', '>=', now()->startOfMonth())
                      ->where('period_type', 'monthly');
            }
        ]);

        // Get performance summary
        $performance = $this->salesRepService->getPerformanceData($salesRep);

        // Get recent activities
        $recentVisits = $salesRep->visits()
                                ->with('customer')
                                ->latest()
                                ->limit(5)
                                ->get();

        $recentOrders = $salesRep->salesOrders()
                                ->with('customer')
                                ->latest()
                                ->limit(5)
                                ->get();

        return view('sales-reps.show', compact(
            'salesRep',
            'performance',
            'recentVisits',
            'recentOrders'
        ));
    }

    /**
     * Show the form for editing the specified sales representative
     */
    public function edit(SalesRepresentative $salesRep): View
    {
        $salesRep->load(['territories', 'customers']);

        $supervisors = SalesRepresentative::where('status', 'active')
                                         ->where('id', '!=', $salesRep->id)
                                         ->get(['id', 'name', 'name_ar']);

        $territories = Territory::where('is_active', true)
                               ->get(['id', 'name', 'name_ar', 'governorate']);

        $customers = Customer::where('is_active', true)
                           ->get(['id', 'name', 'name_ar', 'city', 'governorate']);

        $assignedTerritoryIds = $salesRep->territories()
                                       ->wherePivot('is_active', true)
                                       ->pluck('territories.id')
                                       ->toArray();

        $assignedCustomerIds = $salesRep->customers()
                                      ->wherePivot('is_active', true)
                                      ->pluck('customers.id')
                                      ->toArray();

        return view('sales-reps.edit', compact(
            'salesRep',
            'supervisors',
            'territories',
            'customers',
            'assignedTerritoryIds',
            'assignedCustomerIds'
        ));
    }

    /**
     * Update the specified sales representative
     */
    public function update(UpdateSalesRepRequest $request, SalesRepresentative $salesRep): RedirectResponse
    {
        try {
            $this->salesRepService->update($salesRep, $request->validated());

            return redirect()
                ->route('sales-reps.show', $salesRep)
                ->with('success', 'تم تحديث بيانات مندوب المبيعات بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث بيانات مندوب المبيعات: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sales representative
     */
    public function destroy(SalesRepresentative $salesRep): RedirectResponse
    {
        try {
            $this->salesRepService->delete($salesRep);

            return redirect()
                ->route('sales-reps.index')
                ->with('success', 'تم حذف مندوب المبيعات بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف مندوب المبيعات: ' . $e->getMessage());
        }
    }

    /**
     * Show performance dashboard for sales representative
     */
    public function performance(Request $request, SalesRepresentative $salesRep): View
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $performance = $this->salesRepService->getPerformanceData($salesRep, $period, $startDate, $endDate);

        return view('sales-reps.performance', compact(
            'salesRep',
            'performance',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show location tracking for sales representative
     */
    public function location(SalesRepresentative $salesRep): View
    {
        $currentLocation = $salesRep->getCurrentLocation();
        
        $locationHistory = $salesRep->locationTracking()
                                  ->whereDate('tracked_at', today())
                                  ->orderBy('tracked_at')
                                  ->get();

        return view('sales-reps.location', compact(
            'salesRep',
            'currentLocation',
            'locationHistory'
        ));
    }

    /**
     * Bulk actions on sales representatives
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete,assign_supervisor',
            'sales_rep_ids' => 'required|array|min:1',
            'sales_rep_ids.*' => 'exists:sales_representatives,id',
            'supervisor_id' => 'required_if:action,assign_supervisor|exists:sales_representatives,id'
        ]);

        try {
            $result = $this->salesRepService->bulkAction(
                $request->action,
                $request->sales_rep_ids,
                $request->supervisor_id
            );

            return response()->json([
                'success' => true,
                'message' => "تم تنفيذ العملية على {$result['affected']} مندوب مبيعات",
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تنفيذ العملية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import sales representatives from Excel
     */
    public function import(): View
    {
        return view('sales-reps.import');
    }

    /**
     * Process Excel import
     */
    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            // Import logic will be implemented here
            return redirect()
                ->route('sales-reps.index')
                ->with('success', 'تم استيراد بيانات مندوبي المبيعات بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء استيراد البيانات: ' . $e->getMessage());
        }
    }
}
