<?php

namespace App\Modules\SalesReps\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\SalesReps\Models\CustomerVisit;
use App\Modules\SalesReps\Models\SalesRepresentative;
use App\Modules\SalesReps\Http\Requests\StoreVisitRequest;
use App\Modules\SalesReps\Http\Requests\UpdateVisitRequest;
use App\Modules\SalesReps\Http\Resources\VisitResource;
use App\Modules\SalesReps\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VisitController extends Controller
{
    protected VisitService $visitService;

    public function __construct(VisitService $visitService)
    {
        $this->visitService = $visitService;
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view_visits')->only(['index', 'show']);
        $this->middleware('permission:create_visits')->only(['store', 'checkIn']);
        $this->middleware('permission:edit_visits')->only(['update', 'checkOut']);
    }

    /**
     * Display a listing of visits
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CustomerVisit::with(['salesRepresentative', 'customer', 'orders', 'payments']);

        // Filter by sales rep (for mobile app)
        if ($request->filled('sales_rep_id')) {
            $query->where('sales_rep_id', $request->sales_rep_id);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by visit type
        if ($request->filled('visit_type')) {
            $query->where('visit_type', $request->visit_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('visit_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('visit_date', '<=', $request->end_date);
        }

        // Filter by sync status (for mobile app)
        if ($request->filled('synced')) {
            $query->where('synced', $request->boolean('synced'));
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'visit_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $visits = $query->paginate($perPage);

        return VisitResource::collection($visits);
    }

    /**
     * Store a newly created visit
     */
    public function store(StoreVisitRequest $request): JsonResponse
    {
        try {
            $visit = $this->visitService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الزيارة بنجاح',
                'data' => new VisitResource($visit->load(['salesRepresentative', 'customer']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الزيارة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified visit
     */
    public function show(CustomerVisit $visit): JsonResponse
    {
        $visit->load(['salesRepresentative', 'customer', 'orders', 'payments', 'tasks']);

        return response()->json([
            'success' => true,
            'data' => new VisitResource($visit)
        ]);
    }

    /**
     * Update the specified visit
     */
    public function update(UpdateVisitRequest $request, CustomerVisit $visit): JsonResponse
    {
        try {
            $updatedVisit = $this->visitService->update($visit, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الزيارة بنجاح',
                'data' => new VisitResource($updatedVisit->load(['salesRepresentative', 'customer']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الزيارة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check in to a visit
     */
    public function checkIn(Request $request, CustomerVisit $visit): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|integer|min:0',
            'device_info' => 'nullable|array'
        ]);

        try {
            $this->visitService->checkIn(
                $visit,
                $request->latitude,
                $request->longitude,
                $request->accuracy,
                $request->device_info ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول للزيارة بنجاح',
                'data' => new VisitResource($visit->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول للزيارة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check out from a visit
     */
    public function checkOut(Request $request, CustomerVisit $visit): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'visit_notes' => 'nullable|string',
            'customer_feedback' => 'nullable|string',
            'next_action_required' => 'nullable|string',
            'next_visit_date' => 'nullable|date',
            'outcome' => 'nullable|string|in:successful,partially_successful,unsuccessful,rescheduled',
            'customer_satisfaction_rating' => 'nullable|integer|between:1,5',
            'visit_quality_rating' => 'nullable|integer|between:1,5',
            'photos' => 'nullable|array',
            'photos.*' => 'string', // Base64 encoded images
            'order_created' => 'nullable|boolean',
            'order_amount' => 'nullable|numeric|min:0',
            'payment_collected' => 'nullable|boolean',
            'payment_amount' => 'nullable|numeric|min:0',
            'complaint_received' => 'nullable|boolean',
            'complaint_details' => 'nullable|string'
        ]);

        try {
            $this->visitService->checkOut($visit, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'تم إنهاء الزيارة بنجاح',
                'data' => new VisitResource($visit->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنهاء الزيارة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get visits for today (mobile app)
     */
    public function today(Request $request): JsonResponse
    {
        $salesRepId = $request->get('sales_rep_id');
        
        if (!$salesRepId) {
            return response()->json([
                'success' => false,
                'message' => 'معرف مندوب المبيعات مطلوب'
            ], 400);
        }

        $visits = CustomerVisit::with(['customer', 'orders', 'payments'])
                              ->where('sales_rep_id', $salesRepId)
                              ->whereDate('visit_date', today())
                              ->orderBy('visit_date')
                              ->get();

        return response()->json([
            'success' => true,
            'data' => VisitResource::collection($visits)
        ]);
    }

    /**
     * Get upcoming visits (mobile app)
     */
    public function upcoming(Request $request): JsonResponse
    {
        $salesRepId = $request->get('sales_rep_id');
        $days = $request->get('days', 7); // Next 7 days by default
        
        if (!$salesRepId) {
            return response()->json([
                'success' => false,
                'message' => 'معرف مندوب المبيعات مطلوب'
            ], 400);
        }

        $visits = CustomerVisit::with(['customer'])
                              ->where('sales_rep_id', $salesRepId)
                              ->where('visit_date', '>', now())
                              ->where('visit_date', '<=', now()->addDays($days))
                              ->where('status', 'planned')
                              ->orderBy('visit_date')
                              ->get();

        return response()->json([
            'success' => true,
            'data' => VisitResource::collection($visits)
        ]);
    }

    /**
     * Sync visits from mobile app
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'visits' => 'required|array',
            'visits.*.id' => 'nullable|exists:customer_visits,id',
            'visits.*.sales_rep_id' => 'required|exists:sales_representatives,id',
            'visits.*.customer_id' => 'required|exists:customers,id',
            'visits.*.visit_date' => 'required|date',
            'visits.*.status' => 'required|string',
            // Add other visit fields validation
        ]);

        try {
            $result = $this->visitService->syncVisits($request->visits);

            return response()->json([
                'success' => true,
                'message' => 'تم مزامنة الزيارات بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء مزامنة الزيارات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get visit statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $salesRepId = $request->get('sales_rep_id');
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = $this->visitService->getStatistics($salesRepId, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Upload visit photos
     */
    public function uploadPhotos(Request $request, CustomerVisit $visit): JsonResponse
    {
        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120' // 5MB max
        ]);

        try {
            $photoUrls = $this->visitService->uploadPhotos($visit, $request->file('photos'));

            return response()->json([
                'success' => true,
                'message' => 'تم رفع الصور بنجاح',
                'data' => ['photo_urls' => $photoUrls]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفع الصور',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
