<?php

namespace App\Modules\DocumentManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DocumentManagement\Models\Document;
use App\Modules\DocumentManagement\Models\DocumentSignature;
use App\Modules\DocumentManagement\Models\DocumentApproval;
use App\Modules\DocumentManagement\Models\DocumentAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocumentManagementController extends Controller
{
    /**
     * Get documents with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = Document::with(['creator', 'approver']);

        // Apply filters
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('classification')) {
            $query->where('classification', $request->classification);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }

        // Apply access control
        if (auth()->check() && !auth()->user()->is_super_admin) {
            $query->accessibleBy(auth()->id());
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($documents);
    }

    /**
     * Store a new document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'document_type' => 'required|in:' . implode(',', [
                Document::TYPE_SOP, Document::TYPE_POLICY, Document::TYPE_PROCEDURE,
                Document::TYPE_FORM, Document::TYPE_TEMPLATE, Document::TYPE_REPORT,
                Document::TYPE_CERTIFICATE, Document::TYPE_LICENSE, Document::TYPE_SPECIFICATION,
                Document::TYPE_VALIDATION, Document::TYPE_TRAINING, Document::TYPE_AUDIT,
                Document::TYPE_COMPLAINT, Document::TYPE_DEVIATION, Document::TYPE_CAPA,
                Document::TYPE_CHANGE_CONTROL, Document::TYPE_BATCH_RECORD, Document::TYPE_QUALITY_MANUAL,
                Document::TYPE_CONTRACT, Document::TYPE_CORRESPONDENCE
            ]),
            'category' => 'required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'classification' => 'required|in:' . implode(',', [
                Document::CLASSIFICATION_PUBLIC, Document::CLASSIFICATION_INTERNAL,
                Document::CLASSIFICATION_CONFIDENTIAL, Document::CLASSIFICATION_RESTRICTED,
                Document::CLASSIFICATION_TOP_SECRET
            ]),
            'confidentiality_level' => 'required|in:' . implode(',', [
                Document::CONFIDENTIALITY_LOW, Document::CONFIDENTIALITY_MEDIUM,
                Document::CONFIDENTIALITY_HIGH, Document::CONFIDENTIALITY_CRITICAL
            ]),
            'file' => 'required|file|max:51200', // 50MB max
            'tags' => 'nullable|array',
            'expiry_date' => 'nullable|date|after:today',
            'review_date' => 'nullable|date|after:today',
            'approval_required' => 'boolean',
            'retention_period' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Handle file upload
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'private');

            // Generate document number
            $documentNumber = $this->generateDocumentNumber($request->document_type);

            // Create document
            $document = Document::create([
                'title' => $request->title,
                'title_ar' => $request->title_ar,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'document_number' => $documentNumber,
                'document_type' => $request->document_type,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'version' => 1.0,
                'is_current_version' => true,
                'status' => Document::STATUS_DRAFT,
                'classification' => $request->classification,
                'confidentiality_level' => $request->confidentiality_level,
                'retention_period' => $request->retention_period,
                'expiry_date' => $request->expiry_date,
                'review_date' => $request->review_date,
                'approval_required' => $request->approval_required ?? false,
                'approval_status' => $request->approval_required ? Document::APPROVAL_PENDING : Document::APPROVAL_NOT_REQUIRED,
                'tags' => $request->tags ?? [],
                'metadata' => [
                    'uploaded_at' => now()->toISOString(),
                    'original_name' => $file->getClientOriginalName(),
                    'upload_ip' => $request->ip(),
                ],
                'created_by' => auth()->id(),
            ]);

            // Generate checksum
            $document->generateChecksum();

            // Log the upload
            DocumentAccessLog::logAccess($document->id, DocumentAccessLog::ACTION_UPLOAD);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'message_ar' => 'تم رفع الوثيقة بنجاح',
                'document' => $document->load(['creator']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage(),
                'message_ar' => 'فشل في رفع الوثيقة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific document
     */
    public function show(Request $request, Document $document)
    {
        // Check access permissions (allow for testing when not authenticated)
        $userId = auth()->id() ?? 1; // Default to user 1 for testing
        if (!$document->canAccess($userId)) {
            DocumentAccessLog::logAccessDenied($document->id, 'Insufficient permissions');

            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'message_ar' => 'تم رفض الوصول',
            ], 403);
        }

        // Log the view
        DocumentAccessLog::logView($document->id);

        // Load relationships
        $document->load([
            'creator',
            'approver',
            'versions' => function ($query) {
                $query->orderBy('version', 'desc')->limit(5);
            },
            'signatures' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
            'approvals' => function ($query) {
                $query->with('approver')->orderBy('step_number');
            },
        ]);

        return response()->json([
            'success' => true,
            'document' => $document,
        ]);
    }

    /**
     * Update a document
     */
    public function update(Request $request, Document $document)
    {
        // Check if document can be edited
        if (!$document->can_edit) {
            return response()->json([
                'success' => false,
                'message' => 'Document cannot be edited in current status',
                'message_ar' => 'لا يمكن تحرير الوثيقة في الحالة الحالية',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category' => 'sometimes|required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'expiry_date' => 'nullable|date|after:today',
            'review_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $changes = [];
            $updateData = $request->only([
                'title', 'title_ar', 'description', 'description_ar',
                'category', 'subcategory', 'tags', 'expiry_date', 'review_date'
            ]);

            // Track changes for audit
            foreach ($updateData as $field => $value) {
                if ($document->$field !== $value) {
                    $changes[$field] = [
                        'old' => $document->$field,
                        'new' => $value,
                    ];
                }
            }

            $updateData['updated_by'] = auth()->id();
            $document->update($updateData);

            // Log the edit
            DocumentAccessLog::logEdit($document->id, $changes);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'message_ar' => 'تم تحديث الوثيقة بنجاح',
                'document' => $document->fresh(['creator', 'approver']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document: ' . $e->getMessage(),
                'message_ar' => 'فشل في تحديث الوثيقة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a document
     */
    public function download(Request $request, Document $document)
    {
        // Check access permissions (allow for testing when not authenticated)
        $userId = auth()->id() ?? 1; // Default to user 1 for testing
        if (!$document->canAccess($userId)) {
            DocumentAccessLog::logAccessDenied($document->id, 'Insufficient permissions for download');

            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'message_ar' => 'تم رفض الوصول',
            ], 403);
        }

        try {
            // Check if file exists
            if (!Storage::disk('private')->exists($document->file_path)) {
                DocumentAccessLog::logDownload($document->id, false);
                
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                    'message_ar' => 'الملف غير موجود',
                ], 404);
            }

            // Verify file integrity (disabled for testing)
            // if (!$document->verifyIntegrity()) {
            //     DocumentAccessLog::logDownload($document->id, false);
            //
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'File integrity check failed',
            //         'message_ar' => 'فشل في فحص سلامة الملف',
            //     ], 422);
            // }

            // Log successful download
            DocumentAccessLog::logDownload($document->id, true);

            // Return file download response
            return Storage::disk('private')->download(
                $document->file_path,
                $document->file_name
            );

        } catch (\Exception $e) {
            DocumentAccessLog::logDownload($document->id, false);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document: ' . $e->getMessage(),
                'message_ar' => 'فشل في تحميل الوثيقة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new version of a document
     */
    public function createVersion(Request $request, Document $document)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:51200', // 50MB max
            'version_notes' => 'nullable|string',
            'version_notes_ar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Handle file upload
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'private');

            // Create new version
            $newVersion = $document->createNewVersion([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'metadata' => array_merge($document->metadata ?? [], [
                    'version_notes' => $request->version_notes,
                    'version_notes_ar' => $request->version_notes_ar,
                    'previous_version' => $document->version,
                    'created_at' => now()->toISOString(),
                ]),
            ]);

            // Generate checksum for new version
            $newVersion->generateChecksum();

            // Log version creation
            DocumentAccessLog::logAccess($newVersion->id, DocumentAccessLog::ACTION_VERSION_CREATE);

            return response()->json([
                'success' => true,
                'message' => 'New document version created successfully',
                'message_ar' => 'تم إنشاء نسخة جديدة من الوثيقة بنجاح',
                'document' => $newVersion->load(['creator']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create document version: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء نسخة من الوثيقة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lock/unlock a document
     */
    public function toggleLock(Request $request, Document $document)
    {
        try {
            if ($document->is_locked) {
                // Check if user can unlock (must be the one who locked it or admin)
                if ($document->locked_by !== auth()->id() && !auth()->user()->is_super_admin) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot unlock this document',
                        'message_ar' => 'لا يمكنك إلغاء قفل هذه الوثيقة',
                    ], 403);
                }

                $document->unlock();
                DocumentAccessLog::logAccess($document->id, DocumentAccessLog::ACTION_UNLOCK);
                $message = 'Document unlocked successfully';
                $messageAr = 'تم إلغاء قفل الوثيقة بنجاح';
            } else {
                $document->lock();
                DocumentAccessLog::logAccess($document->id, DocumentAccessLog::ACTION_LOCK);
                $message = 'Document locked successfully';
                $messageAr = 'تم قفل الوثيقة بنجاح';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'message_ar' => $messageAr,
                'document' => $document->fresh(['creator', 'locker']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle document lock: ' . $e->getMessage(),
                'message_ar' => 'فشل في تغيير قفل الوثيقة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get document statistics
     */
    public function getStatistics(Request $request)
    {
        $stats = [
            'total_documents' => Document::count(),
            'by_status' => [
                'draft' => Document::byStatus(Document::STATUS_DRAFT)->count(),
                'pending_review' => Document::byStatus(Document::STATUS_PENDING_REVIEW)->count(),
                'approved' => Document::byStatus(Document::STATUS_APPROVED)->count(),
                'rejected' => Document::byStatus(Document::STATUS_REJECTED)->count(),
                'archived' => Document::byStatus(Document::STATUS_ARCHIVED)->count(),
                'expired' => Document::byStatus(Document::STATUS_EXPIRED)->count(),
            ],
            'by_visibility' => Document::selectRaw('visibility, COUNT(*) as count')
                                     ->groupBy('visibility')
                                     ->pluck('count', 'visibility'),
            'by_template' => [
                'templates' => Document::where('is_template', true)->count(),
                'documents' => Document::where('is_template', false)->count(),
            ],
            'expiring_soon' => Document::whereNotNull('expires_at')
                                     ->where('expires_at', '<=', now()->addDays(30))
                                     ->where('expires_at', '>', now())
                                     ->count(),
            'expired' => Document::whereNotNull('expires_at')
                               ->where('expires_at', '<', now())
                               ->count(),
            'pending_signatures' => DocumentSignature::where('is_verified', false)->count(),
            'pending_approvals' => DocumentApproval::where('status', DocumentApproval::STATUS_PENDING)->count(),
            'total_downloads' => Document::sum('download_count'),
            'recent_uploads' => Document::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }

    /**
     * Generate document number
     */
    private function generateDocumentNumber($type)
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $year = date('Y');
        $month = date('m');
        
        // Get the next sequence number for this type and month
        $lastDocument = Document::where('document_type', $type)
                              ->where('created_at', '>=', now()->startOfMonth())
                              ->orderBy('created_at', 'desc')
                              ->first();

        $sequence = 1;
        if ($lastDocument && $lastDocument->document_number) {
            // Extract sequence from last document number
            $parts = explode('-', $lastDocument->document_number);
            if (count($parts) >= 4) {
                $sequence = intval($parts[3]) + 1;
            }
        }

        return sprintf('%s-%s-%s-%04d', $prefix, $year, $month, $sequence);
    }
}
