<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy']);
    }

    /**
     * Display a listing of tenant users
     */
    public function index(Request $request)
    {
        // Get current tenant ID
        $tenantId = Auth::user()->tenant_id ?? session('tenant_id');

        // If no tenant context, redirect to tenant selection or show error
        if (!$tenantId) {
            return redirect()->route('dashboard')->with('error', 'يجب تحديد المستأجر أولاً');
        }

        $query = User::with(['roles', 'tenant'])
                    ->where('tenant_id', $tenantId)
                    ->whereNotNull('tenant_id'); // Exclude super admin users

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role (only tenant-specific roles)
        if ($request->has('role')) {
            $query->role($request->get('role'));
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        $users = $query->latest()->paginate(20);

        // Get only tenant-specific roles (exclude super admin roles)
        $roles = Role::whereNotIn('name', ['super_admin'])->get();

        $stats = [
            'total_users' => User::where('tenant_id', $tenantId)->count(),
            'active_users' => User::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'inactive_users' => User::where('tenant_id', $tenantId)->where('is_active', false)->count(),
            'admins' => User::where('tenant_id', $tenantId)->role('admin')->count(),
            'managers' => User::where('tenant_id', $tenantId)->role('manager')->count(),
            'regular_users' => User::whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['super_admin', 'admin']);
            })->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'users' => $users,
                'roles' => $roles,
                'stats' => $stats,
            ]);
        }

        return view('admin.users.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        // Get only tenant-specific roles (exclude super admin roles)
        $roles = Role::whereNotIn('name', ['super_admin'])->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created tenant user
     */
    public function store(Request $request)
    {
        // Get current tenant ID
        $tenantId = Auth::user()->tenant_id ?? session('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تحديد المستأجر أولاً'
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        // Ensure the role is not super_admin
        if ($validated['role'] === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إنشاء مستخدم بدور المدير العام'
            ], 400);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
            'tenant_id' => $tenantId,
        ]);

        // Assign role to user
        $user->assignRole($validated['role']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المستخدم بنجاح',
                'user' => $user->load('roles')
            ], 201);
        }

        return redirect()->route('admin.users.index')
                        ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['roles', 'tenant']);
        
        $userStats = [
            'last_login' => $user->last_login_at,
            'created_at' => $user->created_at,
            'roles_count' => $user->roles()->count(),
            'is_active' => $user->is_active,
        ];

        if (request()->expectsJson()) {
            return response()->json([
                'user' => $user,
                'stats' => $userStats,
            ]);
        }

        return view('admin.users.show', compact('user', 'userStats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        // Check if user belongs to current tenant
        $tenantId = Auth::user()->tenant_id ?? session('tenant_id');

        if ($user->tenant_id !== $tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المستخدم'
            ], 403);
        }

        // Get only tenant-specific roles (exclude super admin roles)
        $roles = Role::whereNotIn('name', ['super_admin'])->get();
        $user->load('roles');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'roles' => $roles
            ]);
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified tenant user
     */
    public function update(Request $request, User $user)
    {
        // Check if user belongs to current tenant
        $tenantId = Auth::user()->tenant_id ?? session('tenant_id');

        if ($user->tenant_id !== $tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المستخدم'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        // Ensure the role is not super_admin
        if ($validated['role'] === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعيين دور المدير العام'
            ], 400);
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        // Update user role
        $user->syncRoles([$validated['role']]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المستخدم بنجاح',
                'user' => $user->load('roles')
            ]);
        }

        return redirect()->route('admin.users.index')
                        ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * Remove the specified tenant user
     */
    public function destroy(User $user)
    {
        // Check if user belongs to current tenant
        $tenantId = Auth::user()->tenant_id ?? session('tenant_id');

        if ($user->tenant_id !== $tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا المستخدم'
            ], 403);
        }

        // Prevent deleting super admin users or users without tenant
        if ($user->hasRole('super_admin') || !$user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف هذا المستخدم'
            ], 422);
        }

        // Prevent users from deleting themselves
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك حذف حسابك الخاص'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ]);
    }

    /**
     * Toggle tenant user status
     */
    public function toggleStatus(User $user)
    {
        // Check if user belongs to current tenant
        $tenantId = Auth::user()->tenant_id ?? session('tenant_id');

        if ($user->tenant_id !== $tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المستخدم'
            ], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        return response()->json([
            'success' => true,
            'message' => $status . ' المستخدم بنجاح',
            'user' => $user
        ]);
    }
}
