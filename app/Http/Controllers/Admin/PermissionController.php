<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view permissions')->only(['index', 'show']);
        $this->middleware('permission:create permissions')->only(['create', 'store']);
        $this->middleware('permission:edit permissions')->only(['edit', 'update']);
        $this->middleware('permission:delete permissions')->only(['destroy']);
    }

    /**
     * Display a listing of permissions
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('group', 'like', "%{$search}%");
            });
        }

        // Filter by group
        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        $permissions = $query->latest()->paginate(20);
        $groups = Permission::distinct()->pluck('group')->filter();
        $roles = Role::all();

        return view('admin.permissions.index', compact('permissions', 'groups', 'roles'));
    }

    /**
     * Show the form for creating a new permission
     */
    public function create()
    {
        $groups = Permission::distinct()->pluck('group')->filter();
        return view('admin.permissions.create', compact('groups'));
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'group' => 'required|string|max:100',
        ]);

        $permission = Permission::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الصلاحية بنجاح',
                'permission' => $permission
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'تم إنشاء الصلاحية بنجاح');
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit(Permission $permission)
    {
        $groups = Permission::distinct()->pluck('group')->filter();

        // Return JSON for AJAX requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'permission' => $permission,
                'groups' => $groups
            ]);
        }

        return view('admin.permissions.edit', compact('permission', 'groups'));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'group' => 'required|string|max:100',
        ]);

        $permission->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الصلاحية بنجاح',
                'permission' => $permission
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'تم تحديث الصلاحية بنجاح');
    }

    /**
     * Remove the specified permission
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is assigned to any role
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف صلاحية مرتبطة بأدوار'
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصلاحية بنجاح'
        ]);
    }

    /**
     * Assign permission to role via AJAX
     */
    public function assignToRole(Request $request)
    {
        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $permission = Permission::findOrFail($validated['permission_id']);
        $role = Role::findOrFail($validated['role_id']);

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            $action = 'removed';
            $message = 'تم إزالة الصلاحية من الدور';
        } else {
            $role->givePermissionTo($permission);
            $action = 'added';
            $message = 'تم إضافة الصلاحية للدور';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'action' => $action,
            'has_permission' => $role->hasPermissionTo($permission)
        ]);
    }

    /**
     * Get permissions by group for AJAX
     */
    public function getByGroup(Request $request)
    {
        $group = $request->get('group');
        $permissions = Permission::where('group', $group)->get();

        return response()->json([
            'permissions' => $permissions
        ]);
    }

    /**
     * Bulk assign permissions to role
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $role->syncPermissions($validated['permissions'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث صلاحيات الدور بنجاح',
            'permissions_count' => count($validated['permissions'] ?? [])
        ]);
    }
}
