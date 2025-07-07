<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\Communication;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    /**
     * Display a listing of communications
     */
    public function index(Request $request)
    {
        $query = Communication::with(['creator', 'related']);

        // Apply filters
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('direction')) {
            $query->byDirection($request->direction);
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('related_type') && $request->has('related_id')) {
            $query->where('related_type', $request->related_type)
                  ->where('related_id', $request->related_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('subject_ar', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%")
                  ->orWhere('to_email', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $communications = $query->paginate($request->get('per_page', 15));

        return response()->json($communications);
    }

    /**
     * Store a newly created communication
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'related_type' => 'required|string',
            'related_id' => 'required|integer',
            'type' => ['required', Rule::in(array_keys($this->getTypeOptions()))],
            'direction' => ['required', Rule::in([Communication::DIRECTION_INBOUND, Communication::DIRECTION_OUTBOUND])],
            'subject' => 'required|string|max:255',
            'subject_ar' => 'nullable|string|max:255',
            'content' => 'required|string',
            'content_ar' => 'nullable|string',
            'from_email' => 'nullable|email',
            'to_email' => 'nullable|email',
            'cc_email' => 'nullable|string',
            'bcc_email' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'duration_minutes' => 'nullable|integer|min:0',
            'status' => ['nullable', Rule::in(array_keys($this->getStatusOptions()))],
            'priority' => ['required', Rule::in(array_keys($this->getPriorityOptions()))],
            'scheduled_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? Communication::STATUS_DRAFT;
        $validated['created_by'] = auth()->id();

        $communication = Communication::create($validated);

        return response()->json($communication->load(['creator', 'related']), 201);
    }

    /**
     * Display the specified communication
     */
    public function show(Communication $communication)
    {
        return response()->json($communication->load(['creator', 'related']));
    }

    /**
     * Update the specified communication
     */
    public function update(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'subject_ar' => 'nullable|string|max:255',
            'content' => 'sometimes|required|string',
            'content_ar' => 'nullable|string',
            'from_email' => 'nullable|email',
            'to_email' => 'nullable|email',
            'cc_email' => 'nullable|string',
            'bcc_email' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'duration_minutes' => 'nullable|integer|min:0',
            'status' => ['sometimes', Rule::in(array_keys($this->getStatusOptions()))],
            'priority' => ['sometimes', Rule::in(array_keys($this->getPriorityOptions()))],
            'scheduled_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $communication->update($validated);

        return response()->json($communication->load(['creator', 'related']));
    }

    /**
     * Remove the specified communication
     */
    public function destroy(Communication $communication)
    {
        $communication->delete();

        return response()->json(['message' => 'Communication deleted successfully']);
    }

    /**
     * Mark communication as completed
     */
    public function markAsCompleted(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['duration_minutes'])) {
            $communication->update(['duration_minutes' => $validated['duration_minutes']]);
        }

        $communication->markAsCompleted($validated['notes'] ?? null);

        return response()->json($communication->load(['creator', 'related']));
    }

    /**
     * Mark communication as failed
     */
    public function markAsFailed(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $communication->markAsFailed($validated['reason'] ?? null);

        return response()->json($communication->load(['creator', 'related']));
    }

    /**
     * Schedule communication
     */
    public function schedule(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $communication->schedule($validated['scheduled_at'], $validated['notes'] ?? null);

        return response()->json($communication->load(['creator', 'related']));
    }

    /**
     * Send communication
     */
    public function send(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $communication->send($validated['notes'] ?? null);

        return response()->json($communication->load(['creator', 'related']));
    }

    /**
     * Upload attachment
     */
    public function uploadAttachment(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'file_name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fileName = $validated['file_name'] ?? $file->getClientOriginalName();
        
        // Store file (this would typically use Laravel's file storage)
        $filePath = $file->store('communications/' . $communication->id, 'public');
        
        $communication->addAttachment($filePath, $fileName);

        return response()->json([
            'message' => 'Attachment uploaded successfully',
            'communication' => $communication->load(['creator', 'related']),
        ]);
    }

    /**
     * Remove attachment
     */
    public function removeAttachment(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'attachment_index' => 'required|integer|min:0',
        ]);

        $communication->removeAttachment($validated['attachment_index']);

        return response()->json([
            'message' => 'Attachment removed successfully',
            'communication' => $communication->load(['creator', 'related']),
        ]);
    }

    /**
     * Get communications by related entity
     */
    public function byRelated(Request $request)
    {
        $validated = $request->validate([
            'related_type' => 'required|string',
            'related_id' => 'required|integer',
        ]);

        $communications = Communication::where('related_type', $validated['related_type'])
                                      ->where('related_id', $validated['related_id'])
                                      ->with(['creator'])
                                      ->orderBy('created_at', 'desc')
                                      ->paginate($request->get('per_page', 15));

        return response()->json($communications);
    }

    /**
     * Get overdue communications
     */
    public function overdue(Request $request)
    {
        $communications = Communication::overdue()
                                      ->with(['creator', 'related'])
                                      ->orderBy('scheduled_at')
                                      ->paginate($request->get('per_page', 15));

        return response()->json($communications);
    }

    /**
     * Get communications statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_communications' => Communication::count(),
            'pending_communications' => Communication::pending()->count(),
            'completed_communications' => Communication::completed()->count(),
            'overdue_communications' => Communication::overdue()->count(),
            'by_type' => Communication::selectRaw('type, COUNT(*) as count')
                                     ->groupBy('type')
                                     ->pluck('count', 'type'),
            'by_direction' => Communication::selectRaw('direction, COUNT(*) as count')
                                          ->groupBy('direction')
                                          ->pluck('count', 'direction'),
            'by_status' => Communication::selectRaw('status, COUNT(*) as count')
                                       ->groupBy('status')
                                       ->pluck('count', 'status'),
            'average_duration' => Communication::where('type', Communication::TYPE_PHONE)
                                               ->whereNotNull('duration_minutes')
                                               ->avg('duration_minutes') ?? 0,
        ];

        return response()->json($stats);
    }

    /**
     * Get available options
     */
    public function options()
    {
        return response()->json([
            'types' => $this->getTypeOptions(),
            'directions' => $this->getDirectionOptions(),
            'statuses' => $this->getStatusOptions(),
            'priorities' => $this->getPriorityOptions(),
        ]);
    }

    /**
     * Helper methods
     */
    private function getTypeOptions()
    {
        return [
            Communication::TYPE_EMAIL => 'Email',
            Communication::TYPE_PHONE => 'Phone Call',
            Communication::TYPE_MEETING => 'Meeting',
            Communication::TYPE_SMS => 'SMS',
            Communication::TYPE_WHATSAPP => 'WhatsApp',
            Communication::TYPE_LETTER => 'Letter',
            Communication::TYPE_VISIT => 'Visit',
            Communication::TYPE_OTHER => 'Other',
        ];
    }

    private function getDirectionOptions()
    {
        return [
            Communication::DIRECTION_INBOUND => 'Inbound',
            Communication::DIRECTION_OUTBOUND => 'Outbound',
        ];
    }

    private function getStatusOptions()
    {
        return [
            Communication::STATUS_DRAFT => 'Draft',
            Communication::STATUS_SCHEDULED => 'Scheduled',
            Communication::STATUS_SENT => 'Sent',
            Communication::STATUS_DELIVERED => 'Delivered',
            Communication::STATUS_READ => 'Read',
            Communication::STATUS_REPLIED => 'Replied',
            Communication::STATUS_COMPLETED => 'Completed',
            Communication::STATUS_FAILED => 'Failed',
            Communication::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    private function getPriorityOptions()
    {
        return [
            Communication::PRIORITY_LOW => 'Low',
            Communication::PRIORITY_MEDIUM => 'Medium',
            Communication::PRIORITY_HIGH => 'High',
            Communication::PRIORITY_URGENT => 'Urgent',
        ];
    }
}
