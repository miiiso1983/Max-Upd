<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Models\DocumentFolder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentFolderController extends Controller
{
    /**
     * Display a listing of document folders
     */
    public function index(Request $request)
    {
        $query = DocumentFolder::with(['parent', 'creator']);

        // Apply filters
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === '') {
                $query->rootFolders();
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        if ($request->has('visibility')) {
            $query->byVisibility($request->visibility);
        }

        if ($request->has('is_shared')) {
            $query->where('is_shared', $request->boolean('is_shared'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply access control
        $user = $request->user();
        if ($user && !(method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            $query->accessibleBy($user->id);
        }

        // Apply sorting
        $query->ordered();

        if ($request->get('paginate', true)) {
            $folders = $query->paginate($request->get('per_page', 15));
        } else {
            $folders = $query->get();
        }

        return response()->json($folders);
    }

    /**
     * Store a newly created document folder
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'parent_id' => 'nullable|exists:document_folders,id',
            'color' => 'nullable|string|max:7', // Hex color
            'icon' => 'nullable|string|max:50',
            'visibility' => ['required', Rule::in(array_keys($this->getVisibilityOptions()))],
            'is_shared' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_shared'] = $validated['is_shared'] ?? false;

        $folder = DocumentFolder::create($validated);

        return response()->json($folder->load(['parent', 'creator']), 201);
    }

    /**
     * Display the specified document folder
     */
    public function show(DocumentFolder $documentFolder)
    {
        // Check access permission
        if (!$this->canAccess($documentFolder)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json($documentFolder->load([
            'parent',
            'children.documents',
            'documents',
            'creator',
            'updater',
            'permissions.user'
        ]));
    }

    /**
     * Update the specified document folder
     */
    public function update(Request $request, DocumentFolder $documentFolder)
    {
        // Check access permission
        if (!$this->canAccess($documentFolder, 'write')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'parent_id' => 'nullable|exists:document_folders,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'visibility' => ['sometimes', Rule::in(array_keys($this->getVisibilityOptions()))],
            'is_shared' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id'] == $documentFolder->id) {
            return response()->json(['message' => 'Folder cannot be its own parent'], 400);
        }

        $validated['updated_by'] = auth()->id();

        $documentFolder->update($validated);

        // Update path if name changed
        if (isset($validated['name'])) {
            $documentFolder->updatePath();
        }

        return response()->json($documentFolder->load(['parent', 'creator', 'updater']));
    }

    /**
     * Remove the specified document folder
     */
    public function destroy(DocumentFolder $documentFolder)
    {
        // Check access permission
        if (!$this->canAccess($documentFolder, 'delete')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!$documentFolder->canDelete()) {
            return response()->json([
                'message' => 'Cannot delete folder with documents or subfolders'
            ], 400);
        }

        $documentFolder->delete();

        return response()->json(['message' => 'Document folder deleted successfully']);
    }

    /**
     * Get folder tree
     */
    public function tree(Request $request)
    {
        $query = DocumentFolder::rootFolders()
                              ->with(['children' => function ($q) {
                                  $q->ordered();
                              }])
                              ->ordered();

        // Apply access control
        $user = $request->user();
        if ($user && !(method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            $query->accessibleBy($user->id);
        }

        $folders = $query->get();

        return response()->json($folders);
    }

    /**
     * Get folder contents
     */
    public function contents(DocumentFolder $documentFolder)
    {
        // Check access permission
        if (!$this->canAccess($documentFolder)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $subfolders = $documentFolder->children()
                                    ->ordered()
                                    ->get();

        $documents = $documentFolder->documents()
                                   ->with(['category', 'creator'])
                                   ->orderBy('title')
                                   ->get();

        return response()->json([
            'folder' => $documentFolder,
            'subfolders' => $subfolders,
            'documents' => $documents,
        ]);
    }

    /**
     * Get folder statistics
     */
    public function statistics(DocumentFolder $documentFolder)
    {
        // Check access permission
        if (!$this->canAccess($documentFolder)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $stats = [
            'total_documents' => $documentFolder->getAllDocuments()->count(),
            'direct_documents' => $documentFolder->documents()->count(),
            'subfolders' => $documentFolder->children()->count(),
            'total_subfolders' => $documentFolder->getAllChildren()->count(),
            'total_size' => $documentFolder->getAllDocuments()->sum('file_size'),
        ];

        return response()->json($stats);
    }

    /**
     * Grant folder permission
     */
    public function grantPermission(Request $request, DocumentFolder $documentFolder)
    {
        // Check access permission
        if (!$this->canAccess($documentFolder, 'share')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|in:read,write,delete,share',
        ]);

        $permission = $documentFolder->grantPermission($validated['user_id'], $validated['permission']);

        return response()->json([
            'message' => 'Permission granted successfully',
            'permission' => $permission->load('user'),
        ]);
    }

    /**
     * Revoke folder permission
     */
    public function revokePermission(Request $request, DocumentFolder $documentFolder)
    {
        // Check access permission
        if (!$this->canAccess($documentFolder, 'share')) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $documentFolder->revokePermission($validated['user_id']);

        return response()->json(['message' => 'Permission revoked successfully']);
    }

    /**
     * Reorder folders
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'folders' => 'required|array',
            'folders.*.id' => 'required|exists:document_folders,id',
            'folders.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['folders'] as $folderData) {
            DocumentFolder::where('id', $folderData['id'])
                         ->update(['sort_order' => $folderData['sort_order']]);
        }

        return response()->json(['message' => 'Folders reordered successfully']);
    }

    /**
     * Helper methods
     */
    private function canAccess($folder, $permission = 'read')
    {
        $user = auth()->user();
        
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }
        
        return $folder->hasPermission($user->id, $permission) || 
               $folder->visibility === DocumentFolder::VISIBILITY_PUBLIC ||
               ($folder->visibility === DocumentFolder::VISIBILITY_INTERNAL && $user);
    }

    private function getVisibilityOptions()
    {
        return [
            DocumentFolder::VISIBILITY_PRIVATE => 'Private',
            DocumentFolder::VISIBILITY_INTERNAL => 'Internal',
            DocumentFolder::VISIBILITY_PUBLIC => 'Public',
            DocumentFolder::VISIBILITY_RESTRICTED => 'Restricted',
        ];
    }
}
