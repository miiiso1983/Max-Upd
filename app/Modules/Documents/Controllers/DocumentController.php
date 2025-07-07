<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\DocumentActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $query = Document::with(['category', 'folder', 'creator', 'approver']);

        // Apply filters
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('visibility')) {
            $query->byVisibility($request->visibility);
        }

        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->has('folder_id')) {
            $query->byFolder($request->folder_id);
        }

        if ($request->has('is_template')) {
            $query->where('is_template', $request->boolean('is_template'));
        }

        if ($request->has('related_type') && $request->has('related_id')) {
            $query->where('related_type', $request->related_type)
                  ->where('related_id', $request->related_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        // Apply access control
        $user = $request->user();
        if ($user && !(method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            $query->accessibleBy($user->id);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $documents = $query->paginate($request->get('per_page', 15));

        return response()->json($documents);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'file' => 'required|file|max:51200', // 50MB max
            'category_id' => 'nullable|exists:document_categories,id',
            'folder_id' => 'nullable|exists:document_folders,id',
            'related_type' => 'nullable|string',
            'related_id' => 'nullable|integer',
            'status' => ['nullable', Rule::in(array_keys($this->getStatusOptions()))],
            'visibility' => ['required', Rule::in(array_keys($this->getVisibilityOptions()))],
            'is_template' => 'nullable|boolean',
            'template_type' => 'nullable|string',
            'tags' => 'nullable|array',
            'metadata' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $file = $request->file('file');
        
        // Store file
        $filePath = $file->store('documents/' . now()->format('Y/m'), 'private');
        
        // Calculate checksum
        $checksum = md5_file($file->getRealPath());
        
        // Create document record
        $document = Document::create(array_merge($validated, [
            'file_name' => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'checksum' => $checksum,
            'status' => $validated['status'] ?? Document::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]));

        // Log activity
        $document->logActivity(DocumentActivity::TYPE_UPLOADED, "Document uploaded: {$document->title}");

        return response()->json($document->load(['category', 'folder', 'creator']), 201);
    }

    /**
     * Display the specified document
     */
    public function show(Document $document)
    {
        // Check access permission
        if (!$this->canAccess($document, 'read')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Log view activity
        $document->logActivity(DocumentActivity::TYPE_VIEWED, "Document viewed: {$document->title}");

        return response()->json($document->load([
            'category',
            'folder',
            'creator',
            'updater',
            'approver',
            'parentDocument',
            'versions',
            'permissions.user',
            'signatures.user',
            'activities.creator'
        ]));
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, Document $document)
    {
        // Check access permission
        if (!$this->canAccess($document, 'write')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category_id' => 'nullable|exists:document_categories,id',
            'folder_id' => 'nullable|exists:document_folders,id',
            'status' => ['sometimes', Rule::in(array_keys($this->getStatusOptions()))],
            'visibility' => ['sometimes', Rule::in(array_keys($this->getVisibilityOptions()))],
            'tags' => 'nullable|array',
            'metadata' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $validated['updated_by'] = auth()->id();

        $document->update($validated);

        // Log activity
        $document->logActivity(DocumentActivity::TYPE_EDITED, "Document updated: {$document->title}");

        return response()->json($document->load(['category', 'folder', 'creator', 'updater']));
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document)
    {
        // Check access permission
        if (!$this->canAccess($document, 'delete')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        // Log activity before deletion
        $document->logActivity(DocumentActivity::TYPE_DELETED, "Document deleted: {$document->title}");

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        // Check access permission
        if (!$this->canAccess($document, 'read')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!Storage::disk('private')->exists($document->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Increment download count
        $document->incrementDownloadCount();

        return Storage::disk('private')->download($document->file_path, $document->original_name);
    }

    /**
     * Preview document
     */
    public function preview(Document $document)
    {
        // Check access permission
        if (!$this->canAccess($document, 'read')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!Storage::disk('private')->exists($document->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Log view activity
        $document->logActivity(DocumentActivity::TYPE_VIEWED, "Document previewed: {$document->title}");

        $headers = [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
        ];

        return Storage::disk('private')->response($document->file_path, $document->original_name, $headers);
    }

    /**
     * Upload new version
     */
    public function uploadVersion(Request $request, Document $document)
    {
        // Check access permission
        if (!$this->canAccess($document, 'write')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'notes' => 'nullable|string',
        ]);

        $file = $request->file('file');
        
        // Store new version file
        $filePath = $file->store('documents/' . now()->format('Y/m'), 'private');
        
        // Create new version
        $newVersion = $document->createNewVersion($filePath, auth()->id());
        
        // Update new version with file details
        $newVersion->update([
            'file_name' => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'checksum' => md5_file($file->getRealPath()),
        ]);

        return response()->json([
            'message' => 'New version uploaded successfully',
            'document' => $newVersion->load(['category', 'folder', 'creator']),
        ], 201);
    }

    /**
     * Approve document
     */
    public function approve(Request $request, Document $document)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $document->approve(auth()->id(), $validated['notes'] ?? null);

        return response()->json($document->load(['category', 'folder', 'creator', 'approver']));
    }

    /**
     * Reject document
     */
    public function reject(Request $request, Document $document)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $document->reject(auth()->id(), $validated['reason'] ?? null);

        return response()->json($document->load(['category', 'folder', 'creator']));
    }

    /**
     * Archive document
     */
    public function archive(Request $request, Document $document)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $document->archive(auth()->id(), $validated['reason'] ?? null);

        return response()->json($document->load(['category', 'folder', 'creator']));
    }

    /**
     * Grant permission
     */
    public function grantPermission(Request $request, Document $document)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:read,write,delete,share,approve',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $permission = $document->grantPermission($validated['user_id'], $validated['permission']);
        
        if (isset($validated['expires_at'])) {
            $permission->update(['expires_at' => $validated['expires_at']]);
        }

        $document->logActivity(
            DocumentActivity::TYPE_PERMISSION_GRANTED,
            "Permission '{$validated['permission']}' granted to user {$validated['user_id']}"
        );

        return response()->json([
            'message' => 'Permission granted successfully',
            'permission' => $permission->load('user'),
        ]);
    }

    /**
     * Revoke permission
     */
    public function revokePermission(Request $request, Document $document)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $document->revokePermission($validated['user_id']);

        $document->logActivity(
            DocumentActivity::TYPE_PERMISSION_REVOKED,
            "Permission revoked from user {$validated['user_id']}"
        );

        return response()->json(['message' => 'Permission revoked successfully']);
    }

    /**
     * Get document activities
     */
    public function activities(Document $document)
    {
        // Check access permission
        if (!$this->canAccess($document, 'read')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $activities = $document->activities()
                              ->with('creator')
                              ->orderBy('activity_date', 'desc')
                              ->paginate(20);

        return response()->json($activities);
    }

    /**
     * Get documents statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_documents' => Document::count(),
            'active_documents' => Document::active()->count(),
            'pending_approval' => Document::byStatus(Document::STATUS_PENDING_REVIEW)->count(),
            'approved_documents' => Document::byStatus(Document::STATUS_APPROVED)->count(),
            'expired_documents' => Document::expired()->count(),
            'expiring_soon' => Document::expiringSoon()->count(),
            'by_status' => Document::selectRaw('status, COUNT(*) as count')
                                  ->groupBy('status')
                                  ->pluck('count', 'status'),
            'by_visibility' => Document::selectRaw('visibility, COUNT(*) as count')
                                      ->groupBy('visibility')
                                      ->pluck('count', 'visibility'),
            'total_file_size' => Document::sum('file_size'),
            'total_downloads' => Document::sum('download_count'),
            'recent_uploads' => Document::whereBetween('created_at', [$startDate, $endDate])->count(),
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
            'visibilities' => $this->getVisibilityOptions(),
            'template_types' => $this->getTemplateTypeOptions(),
        ]);
    }

    /**
     * Helper methods
     */
    private function canAccess($document, $permission)
    {
        $user = auth()->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        return $document->hasPermission($user->id, $permission) ||
               $document->visibility === Document::VISIBILITY_PUBLIC ||
               ($document->visibility === Document::VISIBILITY_INTERNAL && $user);
    }

    private function getStatusOptions()
    {
        return [
            Document::STATUS_DRAFT => 'Draft',
            Document::STATUS_PENDING_REVIEW => 'Pending Review',
            Document::STATUS_APPROVED => 'Approved',
            Document::STATUS_REJECTED => 'Rejected',
            Document::STATUS_ARCHIVED => 'Archived',
            Document::STATUS_EXPIRED => 'Expired',
        ];
    }

    private function getVisibilityOptions()
    {
        return [
            Document::VISIBILITY_PRIVATE => 'Private',
            Document::VISIBILITY_INTERNAL => 'Internal',
            Document::VISIBILITY_PUBLIC => 'Public',
            Document::VISIBILITY_RESTRICTED => 'Restricted',
        ];
    }

    private function getTemplateTypeOptions()
    {
        return [
            Document::TEMPLATE_CONTRACT => 'Contract',
            Document::TEMPLATE_INVOICE => 'Invoice',
            Document::TEMPLATE_PROPOSAL => 'Proposal',
            Document::TEMPLATE_REPORT => 'Report',
            Document::TEMPLATE_LETTER => 'Letter',
            Document::TEMPLATE_FORM => 'Form',
        ];
    }
}
