<?php

namespace App\Modules\RegulatoryAffairs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RegulatoryAffairs\Models\RegulatoryDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Upload a document
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'entity_type' => 'required|in:company,product,batch,test,inspection',
            'entity_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'document_type' => 'required|in:license,certificate,report,specification,sop,protocol,validation,registration,inspection_report,test_report,other',
            'document_category' => 'nullable|string|max:255',
            'document_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:document_date',
            'confidentiality_level' => 'nullable|in:public,internal,confidential,restricted',
            'is_required' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $entityType = $request->input('entity_type');
            $entityId = $request->input('entity_id');
            $documentType = $request->input('document_type');

            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;
            
            // Store file
            $path = "regulatory-documents/{$entityType}/{$entityId}";
            $filePath = $file->storeAs($path, $filename, 'public');

            // Generate document number
            $documentNumber = RegulatoryDocument::generateDocumentNumber($entityType, $documentType);

            // Create document record
            $document = RegulatoryDocument::create([
                'document_number' => $documentNumber,
                'title' => $request->input('title'),
                'title_ar' => $request->input('title_ar'),
                'description' => $request->input('description'),
                'description_ar' => $request->input('description_ar'),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'file_name' => $originalName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'document_type' => $documentType,
                'document_category' => $request->input('document_category'),
                'document_date' => $request->input('document_date'),
                'expiry_date' => $request->input('expiry_date'),
                'confidentiality_level' => $request->input('confidentiality_level', 'internal'),
                'uploaded_by' => auth()->id(),
                'is_required' => $request->boolean('is_required'),
                'notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم رفع الملف بنجاح',
                'document' => [
                    'id' => $document->id,
                    'document_number' => $document->document_number,
                    'title' => $document->display_title,
                    'file_name' => $document->file_name,
                    'file_size' => $document->formatted_file_size,
                    'document_type' => $document->document_type_arabic,
                    'upload_date' => $document->created_at->format('Y-m-d H:i'),
                    'download_url' => "/regulatory-affairs/documents/{$document->id}/download",
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفع الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents for an entity
     */
    public function getDocuments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:company,product,batch,test,inspection',
            'entity_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $documents = RegulatoryDocument::forEntity(
            $request->input('entity_type'),
            $request->input('entity_id')
        )
        ->with(['uploader', 'approver'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($document) {
            return [
                'id' => $document->id,
                'document_number' => $document->document_number,
                'title' => $document->display_title,
                'description' => $document->display_description,
                'file_name' => $document->file_name,
                'file_size' => $document->formatted_file_size,
                'document_type' => $document->document_type_arabic,
                'status' => $document->status_arabic,
                'confidentiality_level' => $document->confidentiality_level_arabic,
                'document_date' => $document->document_date?->format('Y-m-d'),
                'expiry_date' => $document->expiry_date?->format('Y-m-d'),
                'is_expired' => $document->isExpired(),
                'is_expiring_soon' => $document->isExpiringSoon(),
                'days_until_expiry' => $document->getDaysUntilExpiry(),
                'uploaded_by' => $document->uploader->name,
                'upload_date' => $document->created_at->format('Y-m-d H:i'),
                'approved_by' => $document->approver?->name,
                'approved_at' => $document->approved_at?->format('Y-m-d H:i'),
                'notes' => $document->notes,
                'download_url' => "/regulatory-affairs/documents/{$document->id}/download",
                'view_url' => "/regulatory-affairs/documents/{$document->id}/view",
            ];
        });

        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }

    /**
     * Download a document
     */
    public function download(RegulatoryDocument $document)
    {
        if (!Storage::exists($document->file_path)) {
            abort(404, 'الملف غير موجود');
        }

        return Storage::download($document->file_path, $document->file_name);
    }

    /**
     * View a document in browser
     */
    public function view(RegulatoryDocument $document)
    {
        if (!Storage::exists($document->file_path)) {
            abort(404, 'الملف غير موجود');
        }

        $headers = [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"'
        ];

        return Storage::response($document->file_path, $document->file_name, $headers);
    }

    /**
     * Delete a document
     */
    public function destroy(RegulatoryDocument $document): JsonResponse
    {
        try {
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الملف بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update document details
     */
    public function update(Request $request, RegulatoryDocument $document): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'document_type' => 'required|in:license,certificate,report,specification,sop,protocol,validation,registration,inspection_report,test_report,other',
            'document_category' => 'nullable|string|max:255',
            'document_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:document_date',
            'confidentiality_level' => 'nullable|in:public,internal,confidential,restricted',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $document->update($request->only([
                'title', 'title_ar', 'description', 'description_ar',
                'document_type', 'document_category', 'document_date',
                'expiry_date', 'confidentiality_level', 'notes'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات الملف بنجاح',
                'document' => [
                    'id' => $document->id,
                    'title' => $document->display_title,
                    'document_type' => $document->document_type_arabic,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a document
     */
    public function approve(RegulatoryDocument $document): JsonResponse
    {
        try {
            $document->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم اعتماد الملف بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء اعتماد الملف: ' . $e->getMessage()
            ], 500);
        }
    }
}
