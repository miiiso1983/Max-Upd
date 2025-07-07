<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    /**
     * Display a listing of leads
     */
    public function index(Request $request)
    {
        $query = Lead::with(['assignedTo', 'creator']);

        // Apply filters
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->has('source')) {
            $query->bySource($request->source);
        }

        if ($request->has('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('company_name_ar', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate($request->get('per_page', 15));

        return response()->json($leads);
    }

    /**
     * Store a newly created lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_name_ar' => 'nullable|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_person_ar' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'city_ar' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'country_ar' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'industry_ar' => 'nullable|string|max:100',
            'source' => ['required', Rule::in(array_keys($this->getSourceOptions()))],
            'source_ar' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in(array_keys($this->getStatusOptions()))],
            'priority' => ['required', Rule::in(array_keys($this->getPriorityOptions()))],
            'estimated_value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date|after:today',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['status'] = $validated['status'] ?? Lead::STATUS_NEW;
        $validated['created_by'] = auth()->id();

        $lead = Lead::create($validated);

        // Log creation activity
        $lead->logActivity(LeadActivity::TYPE_CREATED, "Lead created: {$lead->company_name}");

        return response()->json($lead->load(['assignedTo', 'creator']), 201);
    }

    /**
     * Display the specified lead
     */
    public function show(Lead $lead)
    {
        return response()->json($lead->load([
            'assignedTo',
            'creator',
            'customer',
            'activities.creator',
            'opportunities',
            'communications'
        ]));
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'company_name_ar' => 'nullable|string|max:255',
            'contact_person' => 'sometimes|required|string|max:255',
            'contact_person_ar' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'city_ar' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'country_ar' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'industry_ar' => 'nullable|string|max:100',
            'source' => ['sometimes', Rule::in(array_keys($this->getSourceOptions()))],
            'source_ar' => 'nullable|string|max:100',
            'status' => ['sometimes', Rule::in(array_keys($this->getStatusOptions()))],
            'priority' => ['sometimes', Rule::in(array_keys($this->getPriorityOptions()))],
            'estimated_value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date|after:today',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['updated_by'] = auth()->id();

        $lead->update($validated);

        return response()->json($lead->load(['assignedTo', 'creator']));
    }

    /**
     * Remove the specified lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully']);
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->getStatusOptions()))],
            'notes' => 'nullable|string',
        ]);

        $lead->updateStatus($validated['status'], $validated['notes'] ?? null);

        return response()->json($lead->load(['assignedTo', 'creator']));
    }

    /**
     * Convert lead to customer
     */
    public function convertToCustomer(Request $request, Lead $lead)
    {
        if ($lead->status === Lead::STATUS_CONVERTED) {
            return response()->json(['message' => 'Lead is already converted'], 400);
        }

        $validated = $request->validate([
            'customer_data' => 'nullable|array',
        ]);

        $customer = $lead->convertToCustomer($validated['customer_data'] ?? []);

        return response()->json([
            'message' => 'Lead converted to customer successfully',
            'lead' => $lead->load(['assignedTo', 'creator', 'customer']),
            'customer' => $customer,
        ]);
    }

    /**
     * Schedule follow-up
     */
    public function scheduleFollowUp(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'follow_up_date' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $lead->scheduleFollowUp($validated['follow_up_date'], $validated['notes'] ?? null);

        return response()->json($lead->load(['assignedTo', 'creator']));
    }

    /**
     * Get lead activities
     */
    public function activities(Lead $lead)
    {
        $activities = $lead->activities()
                          ->with('creator')
                          ->orderBy('activity_date', 'desc')
                          ->paginate(20);

        return response()->json($activities);
    }

    /**
     * Add activity to lead
     */
    public function addActivity(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in([
                LeadActivity::TYPE_CONTACTED,
                LeadActivity::TYPE_EMAIL_SENT,
                LeadActivity::TYPE_CALL_MADE,
                LeadActivity::TYPE_MEETING_HELD,
                LeadActivity::TYPE_NOTE_ADDED,
                LeadActivity::TYPE_OTHER,
            ])],
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
        ]);

        $activity = $lead->logActivity(
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
     * Get leads statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_leads' => Lead::count(),
            'active_leads' => Lead::active()->count(),
            'converted_leads' => Lead::converted()->count(),
            'lost_leads' => Lead::lost()->count(),
            'overdue_leads' => Lead::overdue()->count(),
            'by_status' => Lead::selectRaw('status, COUNT(*) as count')
                              ->groupBy('status')
                              ->pluck('count', 'status'),
            'by_priority' => Lead::selectRaw('priority, COUNT(*) as count')
                                ->groupBy('priority')
                                ->pluck('count', 'priority'),
            'by_source' => Lead::selectRaw('source, COUNT(*) as count')
                              ->groupBy('source')
                              ->pluck('count', 'source'),
            'conversion_rate' => Lead::count() > 0 ? 
                (Lead::converted()->count() / Lead::count()) * 100 : 0,
            'total_estimated_value' => Lead::active()->sum('estimated_value'),
        ];

        return response()->json($stats);
    }

    /**
     * Get available options
     */
    public function options()
    {
        return response()->json([
            'statuses' => $this->getStatusOptions(),
            'priorities' => $this->getPriorityOptions(),
            'sources' => $this->getSourceOptions(),
        ]);
    }

    /**
     * Helper methods
     */
    private function getStatusOptions()
    {
        return [
            Lead::STATUS_NEW => 'New',
            Lead::STATUS_CONTACTED => 'Contacted',
            Lead::STATUS_QUALIFIED => 'Qualified',
            Lead::STATUS_PROPOSAL => 'Proposal Sent',
            Lead::STATUS_NEGOTIATION => 'In Negotiation',
            Lead::STATUS_CONVERTED => 'Converted',
            Lead::STATUS_LOST => 'Lost',
            Lead::STATUS_UNQUALIFIED => 'Unqualified',
        ];
    }

    private function getPriorityOptions()
    {
        return [
            Lead::PRIORITY_LOW => 'Low',
            Lead::PRIORITY_MEDIUM => 'Medium',
            Lead::PRIORITY_HIGH => 'High',
            Lead::PRIORITY_URGENT => 'Urgent',
        ];
    }

    private function getSourceOptions()
    {
        return [
            Lead::SOURCE_WEBSITE => 'Website',
            Lead::SOURCE_REFERRAL => 'Referral',
            Lead::SOURCE_COLD_CALL => 'Cold Call',
            Lead::SOURCE_EMAIL => 'Email',
            Lead::SOURCE_SOCIAL_MEDIA => 'Social Media',
            Lead::SOURCE_TRADE_SHOW => 'Trade Show',
            Lead::SOURCE_ADVERTISEMENT => 'Advertisement',
            Lead::SOURCE_OTHER => 'Other',
        ];
    }
}
