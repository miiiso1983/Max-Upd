<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Opportunity;
use App\Modules\CRM\Models\OpportunityActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OpportunityController extends Controller
{
    /**
     * Display a listing of opportunities
     */
    public function index(Request $request)
    {
        $query = Opportunity::with(['customer', 'lead', 'assignedTo', 'creator']);

        // Apply filters
        if ($request->has('stage')) {
            $query->byStage($request->stage);
        }

        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->has('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $opportunities = $query->paginate($request->get('per_page', 15));

        return response()->json($opportunities);
    }

    /**
     * Store a newly created opportunity
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'stage' => ['required', Rule::in(array_keys($this->getStageOptions()))],
            'probability' => 'required|integer|min:0|max:100',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'expected_close_date' => 'required|date|after:today',
            'source' => 'nullable|string|max:100',
            'source_ar' => 'nullable|string|max:100',
            'type' => ['required', Rule::in(array_keys($this->getTypeOptions()))],
            'priority' => ['required', Rule::in(array_keys($this->getPriorityOptions()))],
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
        ]);

        $validated['currency'] = $validated['currency'] ?? 'IQD';
        $validated['created_by'] = auth()->id();

        $opportunity = Opportunity::create($validated);

        // Log creation activity
        $opportunity->logActivity(OpportunityActivity::TYPE_CREATED, "Opportunity created: {$opportunity->name}");

        return response()->json($opportunity->load(['customer', 'lead', 'assignedTo', 'creator']), 201);
    }

    /**
     * Display the specified opportunity
     */
    public function show(Opportunity $opportunity)
    {
        return response()->json($opportunity->load([
            'customer',
            'lead',
            'assignedTo',
            'creator',
            'activities.creator',
            'communications'
        ]));
    }

    /**
     * Update the specified opportunity
     */
    public function update(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'stage' => ['sometimes', Rule::in(array_keys($this->getStageOptions()))],
            'probability' => 'sometimes|integer|min:0|max:100',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'expected_close_date' => 'sometimes|date|after:today',
            'source' => 'nullable|string|max:100',
            'source_ar' => 'nullable|string|max:100',
            'type' => ['sometimes', Rule::in(array_keys($this->getTypeOptions()))],
            'priority' => ['sometimes', Rule::in(array_keys($this->getPriorityOptions()))],
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $opportunity->update($validated);

        return response()->json($opportunity->load(['customer', 'lead', 'assignedTo', 'creator']));
    }

    /**
     * Remove the specified opportunity
     */
    public function destroy(Opportunity $opportunity)
    {
        $opportunity->delete();

        return response()->json(['message' => 'Opportunity deleted successfully']);
    }

    /**
     * Move opportunity to stage
     */
    public function moveToStage(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'stage' => ['required', Rule::in(array_keys($this->getStageOptions()))],
            'notes' => 'nullable|string',
        ]);

        $opportunity->moveToStage($validated['stage'], $validated['notes'] ?? null);

        return response()->json($opportunity->load(['customer', 'lead', 'assignedTo', 'creator']));
    }

    /**
     * Mark opportunity as won
     */
    public function markAsWon(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $opportunity->markAsWon($validated['notes'] ?? null);

        return response()->json($opportunity->load(['customer', 'lead', 'assignedTo', 'creator']));
    }

    /**
     * Mark opportunity as lost
     */
    public function markAsLost(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'lost_reason' => 'nullable|string',
            'lost_reason_ar' => 'nullable|string',
            'competitor' => 'nullable|string',
            'competitor_ar' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $opportunity->markAsLost(
            $validated['lost_reason'] ?? null,
            $validated['competitor'] ?? null,
            $validated['notes'] ?? null
        );

        if (isset($validated['lost_reason_ar'])) {
            $opportunity->update(['lost_reason_ar' => $validated['lost_reason_ar']]);
        }

        if (isset($validated['competitor_ar'])) {
            $opportunity->update(['competitor_ar' => $validated['competitor_ar']]);
        }

        return response()->json($opportunity->load(['customer', 'lead', 'assignedTo', 'creator']));
    }

    /**
     * Update opportunity amount
     */
    public function updateAmount(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $opportunity->updateAmount($validated['amount'], $validated['notes'] ?? null);

        return response()->json($opportunity->load(['customer', 'lead', 'assignedTo', 'creator']));
    }

    /**
     * Update opportunity close date
     */
    public function updateCloseDate(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'expected_close_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $opportunity->updateCloseDate($validated['expected_close_date'], $validated['notes'] ?? null);

        return response()->json($opportunity->load(['customer', 'lead', 'assignedTo', 'creator']));
    }

    /**
     * Get opportunity activities
     */
    public function activities(Opportunity $opportunity)
    {
        $activities = $opportunity->activities()
                                 ->with('creator')
                                 ->orderBy('activity_date', 'desc')
                                 ->paginate(20);

        return response()->json($activities);
    }

    /**
     * Add activity to opportunity
     */
    public function addActivity(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in([
                OpportunityActivity::TYPE_EMAIL_SENT,
                OpportunityActivity::TYPE_CALL_MADE,
                OpportunityActivity::TYPE_MEETING_HELD,
                OpportunityActivity::TYPE_PROPOSAL_SENT,
                OpportunityActivity::TYPE_NOTE_ADDED,
                OpportunityActivity::TYPE_OTHER,
            ])],
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
        ]);

        $activity = $opportunity->logActivity(
            $validated['type'],
            $validated['description'],
            auth()->id()
        );

        if (isset($validated['description_ar'])) {
            $activity->update(['description_ar' => $validated['description_ar']]);
        }

        return response()->json($activity->load('creator'), 201);
    }

    /**
     * Get sales pipeline
     */
    public function pipeline(Request $request)
    {
        $pipeline = [];
        $stages = $this->getStageOptions();

        foreach ($stages as $stage => $label) {
            $opportunities = Opportunity::byStage($stage)
                                       ->with(['customer', 'assignedTo'])
                                       ->get();

            $pipeline[] = [
                'stage' => $stage,
                'stage_label' => $label,
                'stage_label_ar' => $this->getStageLabelsAr()[$stage] ?? $label,
                'count' => $opportunities->count(),
                'total_amount' => $opportunities->sum('amount'),
                'weighted_amount' => $opportunities->sum('weighted_amount'),
                'opportunities' => $opportunities,
            ];
        }

        return response()->json($pipeline);
    }

    /**
     * Get opportunities statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_opportunities' => Opportunity::count(),
            'open_opportunities' => Opportunity::open()->count(),
            'won_opportunities' => Opportunity::won()->count(),
            'lost_opportunities' => Opportunity::lost()->count(),
            'overdue_opportunities' => Opportunity::overdue()->count(),
            'closing_soon' => Opportunity::closingSoon()->count(),
            'by_stage' => Opportunity::selectRaw('stage, COUNT(*) as count, SUM(amount) as total_amount')
                                    ->groupBy('stage')
                                    ->get()
                                    ->keyBy('stage'),
            'by_priority' => Opportunity::selectRaw('priority, COUNT(*) as count')
                                      ->groupBy('priority')
                                      ->pluck('count', 'priority'),
            'total_pipeline_value' => Opportunity::open()->sum('amount'),
            'weighted_pipeline_value' => Opportunity::open()->get()->sum('weighted_amount'),
            'win_rate' => Opportunity::whereIn('stage', [Opportunity::STAGE_CLOSED_WON, Opportunity::STAGE_CLOSED_LOST])->count() > 0 ?
                (Opportunity::won()->count() / Opportunity::whereIn('stage', [Opportunity::STAGE_CLOSED_WON, Opportunity::STAGE_CLOSED_LOST])->count()) * 100 : 0,
            'average_deal_size' => Opportunity::won()->avg('amount') ?? 0,
        ];

        return response()->json($stats);
    }

    /**
     * Get available options
     */
    public function options()
    {
        return response()->json([
            'stages' => $this->getStageOptions(),
            'types' => $this->getTypeOptions(),
            'priorities' => $this->getPriorityOptions(),
        ]);
    }

    /**
     * Helper methods
     */
    private function getStageOptions()
    {
        return [
            Opportunity::STAGE_PROSPECTING => 'Prospecting',
            Opportunity::STAGE_QUALIFICATION => 'Qualification',
            Opportunity::STAGE_NEEDS_ANALYSIS => 'Needs Analysis',
            Opportunity::STAGE_PROPOSAL => 'Proposal',
            Opportunity::STAGE_NEGOTIATION => 'Negotiation',
            Opportunity::STAGE_CLOSED_WON => 'Closed Won',
            Opportunity::STAGE_CLOSED_LOST => 'Closed Lost',
        ];
    }

    private function getStageLabelsAr()
    {
        return [
            Opportunity::STAGE_PROSPECTING => 'البحث عن العملاء',
            Opportunity::STAGE_QUALIFICATION => 'التأهيل',
            Opportunity::STAGE_NEEDS_ANALYSIS => 'تحليل الاحتياجات',
            Opportunity::STAGE_PROPOSAL => 'العرض',
            Opportunity::STAGE_NEGOTIATION => 'التفاوض',
            Opportunity::STAGE_CLOSED_WON => 'مغلق - فوز',
            Opportunity::STAGE_CLOSED_LOST => 'مغلق - خسارة',
        ];
    }

    private function getTypeOptions()
    {
        return [
            Opportunity::TYPE_NEW_BUSINESS => 'New Business',
            Opportunity::TYPE_EXISTING_BUSINESS => 'Existing Business',
            Opportunity::TYPE_RENEWAL => 'Renewal',
            Opportunity::TYPE_UPSELL => 'Upsell',
            Opportunity::TYPE_CROSS_SELL => 'Cross-sell',
        ];
    }

    private function getPriorityOptions()
    {
        return [
            Opportunity::PRIORITY_LOW => 'Low',
            Opportunity::PRIORITY_MEDIUM => 'Medium',
            Opportunity::PRIORITY_HIGH => 'High',
            Opportunity::PRIORITY_URGENT => 'Urgent',
        ];
    }
}
