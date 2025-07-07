<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|tenant-admin|super_admin']);
    }

    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::with('permissions')->paginate(10);
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم إنشاء الدور بنجاح');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        // Prevent editing super admin role by non-super admins
        if ($role->name === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'غير مسموح بتعديل دور المدير العام');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing super admin role by non-super admins
        if ($role->name === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'غير مسموح بتعديل دور المدير العام');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array'
        ]);

        $role->update([
            'name' => $request->name
        ]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم تحديث الدور بنجاح');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deleting system roles
        $systemRoles = ['super_admin', 'tenant-admin', 'manager', 'sales-rep', 'inventory-manager', 'accountant', 'hr-manager', 'medical-rep', 'cashier'];
        
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'لا يمكن حذف الأدوار الأساسية للنظام');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'لا يمكن حذف دور مرتبط بمستخدمين');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'تم حذف الدور بنجاح');
    }

    /**
     * API endpoints
     */
    public function apiIndex()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'), 201);
    }

    public function apiUpdate(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'));
    }

    public function apiDestroy(Role $role)
    {
        $systemRoles = ['super_admin', 'tenant-admin', 'manager', 'sales-rep', 'inventory-manager', 'accountant', 'hr-manager', 'medical-rep', 'cashier'];
        
        if (in_array($role->name, $systemRoles)) {
            return response()->json(['message' => 'Cannot delete system roles'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 403);
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    /**
     * Get role permissions for AJAX
     */
    public function permissions(Role $role)
    {
        $role->load('permissions');
        $allPermissions = Permission::all()->groupBy('group');

        return response()->json([
            'success' => true,
            'role' => $role,
            'permissions' => $role->permissions->pluck('name')->toArray(),
            'all_permissions' => $allPermissions
        ]);
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث صلاحيات الدور بنجاح'
        ]);
    }
}
