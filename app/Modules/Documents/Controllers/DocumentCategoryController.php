<?php

namespace App\Modules\Documents\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of document categories
     */
    public function index(Request $request)
    {
        $query = DocumentCategory::with(['parent', 'creator']);

        // Apply filters
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === '') {
                $query->rootCategories();
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
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
        $query->ordered();

        if ($request->get('paginate', true)) {
            $categories = $query->paginate($request->get('per_page', 15));
        } else {
            $categories = $query->get();
        }

        return response()->json($categories);
    }

    /**
     * Store a newly created document category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'parent_id' => 'nullable|exists:document_categories,id',
            'color' => 'nullable|string|max:7', // Hex color
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $category = DocumentCategory::create($validated);

        return response()->json($category->load(['parent', 'creator']), 201);
    }

    /**
     * Display the specified document category
     */
    public function show(DocumentCategory $documentCategory)
    {
        return response()->json($documentCategory->load([
            'parent',
            'children.documents',
            'documents',
            'creator',
            'updater'
        ]));
    }

    /**
     * Update the specified document category
     */
    public function update(Request $request, DocumentCategory $documentCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'parent_id' => 'nullable|exists:document_categories,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id'] == $documentCategory->id) {
            return response()->json(['message' => 'Category cannot be its own parent'], 400);
        }

        $validated['updated_by'] = auth()->id();

        $documentCategory->update($validated);

        return response()->json($documentCategory->load(['parent', 'creator', 'updater']));
    }

    /**
     * Remove the specified document category
     */
    public function destroy(DocumentCategory $documentCategory)
    {
        if (!$documentCategory->canDelete()) {
            return response()->json([
                'message' => 'Cannot delete category with documents or subcategories'
            ], 400);
        }

        $documentCategory->delete();

        return response()->json(['message' => 'Document category deleted successfully']);
    }

    /**
     * Get category tree
     */
    public function tree(Request $request)
    {
        $categories = DocumentCategory::active()
                                     ->rootCategories()
                                     ->with(['children' => function ($query) {
                                         $query->active()->ordered();
                                     }])
                                     ->ordered()
                                     ->get();

        return response()->json($categories);
    }

    /**
     * Get category statistics
     */
    public function statistics(DocumentCategory $documentCategory)
    {
        $stats = [
            'total_documents' => $documentCategory->getAllDocuments()->count(),
            'direct_documents' => $documentCategory->documents()->count(),
            'subcategories' => $documentCategory->children()->count(),
            'total_subcategories' => $documentCategory->getAllChildren()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:document_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['categories'] as $categoryData) {
            DocumentCategory::where('id', $categoryData['id'])
                           ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return response()->json(['message' => 'Categories reordered successfully']);
    }
}
