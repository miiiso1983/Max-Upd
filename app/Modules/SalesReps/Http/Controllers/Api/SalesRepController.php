<?php

namespace App\Modules\SalesReps\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\SalesReps\Models\SalesRepresentative;
use App\Modules\SalesReps\Http\Requests\StoreSalesRepRequest;
use App\Modules\SalesReps\Http\Requests\UpdateSalesRepRequest;
use App\Modules\SalesReps\Http\Resources\SalesRepResource;
use App\Modules\SalesReps\Services\SalesRepService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SalesRepController extends Controller
{
    protected SalesRepService $salesRepService;

    public function __construct(SalesRepService $salesRepService)
    {
        $this->salesRepService = $salesRepService;
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view_sales_reps')->only(['index', 'show']);
        $this->middleware('permission:create_sales_reps')->only(['store']);
        $this->middleware('permission:edit_sales_reps')->only(['update']);
        $this->middleware('permission:delete_sales_reps')->only(['destroy']);
    }

    /**
     * Display a listing of sales representatives
     */
    public function index(Request $request): AnonymousResourceCollection
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

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $salesReps = $query->paginate($perPage);

        return SalesRepResource::collection($salesReps);
    }

    /**
     * Store a newly created sales representative
     */
    public function store(StoreSalesRepRequest $request): JsonResponse
    {
        try {
            $salesRep = $this->salesRepService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء مندوب المبيعات بنجاح',
                'data' => new SalesRepResource($salesRep->load(['user', 'supervisor', 'territories', 'customers']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء مندوب المبيعات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sales representative
     */
    public function show(SalesRepresentative $salesRep): JsonResponse
    {
        $salesRep->load([
            'user', 
            'supervisor', 
            'subordinates', 
            'territories.customers', 
            'customers',
            'performanceMetrics' => function ($query) {
                $query->where('metric_date', '>=', now()->startOfMonth())
                      ->where('period_type', 'monthly');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => new SalesRepResource($salesRep)
        ]);
    }

    /**
     * Update the specified sales representative
     */
    public function update(UpdateSalesRepRequest $request, SalesRepresentative $salesRep): JsonResponse
    {
        try {
            $updatedSalesRep = $this->salesRepService->update($salesRep, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات مندوب المبيعات بنجاح',
                'data' => new SalesRepResource($updatedSalesRep->load(['user', 'supervisor', 'territories', 'customers']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث بيانات مندوب المبيعات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified sales representative
     */
    public function destroy(SalesRepresentative $salesRep): JsonResponse
    {
        try {
            $this->salesRepService->delete($salesRep);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف مندوب المبيعات بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف مندوب المبيعات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales representative performance summary
     */
    public function performance(Request $request, SalesRepresentative $salesRep): JsonResponse
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $performance = $this->salesRepService->getPerformanceData($salesRep, $period, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $performance
        ]);
    }

    /**
     * Get sales representative current location
     */
    public function location(SalesRepresentative $salesRep): JsonResponse
    {
        $location = $salesRep->getCurrentLocation();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد بيانات موقع متاحة'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $location
        ]);
    }

    /**
     * Update sales representative location
     */
    public function updateLocation(Request $request, SalesRepresentative $salesRep): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|integer|min:0',
            'activity_type' => 'nullable|string|in:working,break,lunch,travel,meeting,training,off_duty'
        ]);

        try {
            $this->salesRepService->updateLocation(
                $salesRep,
                $request->latitude,
                $request->longitude,
                $request->accuracy,
                $request->activity_type
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الموقع بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الموقع',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales representative assigned customers
     */
    public function customers(Request $request, SalesRepresentative $salesRep): JsonResponse
    {
        $customers = $salesRep->customers()
                             ->wherePivot('is_active', true)
                             ->with(['visits' => function ($query) {
                                 $query->where('sales_rep_id', request()->route('salesRep')->id)
                                       ->latest()
                                       ->limit(5);
                             }])
                             ->get();

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Get sales representative assigned territories
     */
    public function territories(SalesRepresentative $salesRep): JsonResponse
    {
        $territories = $salesRep->territories()
                               ->wherePivot('is_active', true)
                               ->with(['customers'])
                               ->get();

        return response()->json([
            'success' => true,
            'data' => $territories
        ]);
    }

    /**
     * Bulk operations on sales representatives
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
}
