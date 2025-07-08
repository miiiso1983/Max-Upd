<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin|super_admin');
    }

    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['creator', 'users']);

        // Apply filters
        if ($request->has('status')) {
            switch ($request->get('status')) {
                case 'active':
                    $query->active();
                    break;
                case 'inactive':
                    $query->inactive();
                    break;
                case 'expired':
                    $query->withExpiredLicense();
                    break;
                case 'expiring':
                    $query->where('license_expires_at', '<=', now()->addDays(30))
                          ->where('license_expires_at', '>', now());
                    break;
            }
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_type', 'like', "%{$search}%");
            });
        }

        $tenants = $query->latest()->paginate(20);

        // Add calculated fields
        $tenants->getCollection()->transform(function ($tenant) {
            $tenant->total_users = $tenant->users()->count();
            $tenant->active_users = $tenant->users()->where('is_active', true)->count();
            $tenant->license_status = $tenant->hasValidLicense() ? 'valid' : 'expired';
            $tenant->days_until_expiry = $tenant->license_expires_at ? 
                now()->diffInDays($tenant->license_expires_at, false) : null;
            return $tenant;
        });

        if ($request->expectsJson()) {
            return response()->json(['tenants' => $tenants]);
        }

        return view('master-admin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('master-admin.tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain',
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'company_type' => 'required|string|in:pharmacy,medical_distributor,clinic,hospital,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'governorate' => 'required|string|max:100',
            'max_users' => 'required|integer|min:1|max:1000',
            'license_expires_at' => 'required|date|after:today',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'database' => 'tenant_' . $validated['domain'],
            'company_name' => $validated['company_name'],
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company_type' => $validated['company_type'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'governorate' => $validated['governorate'],
            'license_key' => Str::uuid(),
            'license_expires_at' => $validated['license_expires_at'],
            'max_users' => $validated['max_users'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        // Create tenant admin user
        $adminUser = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign tenant-admin role
        $adminUser->assignRole('tenant-admin');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم إنشاء المستأجر بنجاح',
                'tenant' => $tenant->load('users')
            ], 201);
        }

        return redirect()->route('master-admin.tenants.index')
                        ->with('success', 'تم إنشاء المستأجر بنجاح');
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['creator', 'users.roles']);
        
        $stats = [
            'total_users' => $tenant->users()->count(),
            'active_users' => $tenant->users()->where('is_active', true)->count(),
            'admin_users' => $tenant->users()->role('tenant-admin')->count(),
            'regular_users' => $tenant->users()->whereDoesntHave('roles', function($q) {
                $q->where('name', 'tenant-admin');
            })->count(),
            'last_login' => $tenant->users()->whereNotNull('last_login_at')
                                          ->orderBy('last_login_at', 'desc')
                                          ->first()?->last_login_at,
            'storage_used' => $this->calculateStorageUsed($tenant),
            'monthly_revenue' => $this->calculateMonthlyRevenue($tenant),
        ];

        if (request()->expectsJson()) {
            return response()->json([
                'tenant' => $tenant,
                'stats' => $stats,
            ]);
        }

        return view('master-admin.tenants.show', compact('tenant', 'stats'));
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        return view('master-admin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain,' . $tenant->id,
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'company_type' => 'required|string|in:pharmacy,medical_distributor,clinic,hospital,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'governorate' => 'required|string|max:100',
            'max_users' => 'required|integer|min:1|max:1000',
            'license_expires_at' => 'required|date',
            'is_active' => 'boolean',
        ]);

        // Update tenant (exclude domain and database from updates for safety)
        $updateData = $validated;
        unset($updateData['domain']); // Don't allow domain changes after creation

        $tenant->update($updateData);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم تحديث المستأجر بنجاح',
                'tenant' => $tenant
            ]);
        }

        return redirect()->route('master-admin.tenants.index')
                        ->with('success', 'تم تحديث المستأجر بنجاح');
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant)
    {
        // Check if tenant has active data
        if ($tenant->hasActiveData()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'لا يمكن حذف المستأجر لوجود بيانات نشطة'
                ], 422);
            }
            
            return redirect()->route('master-admin.tenants.index')
                            ->with('error', 'لا يمكن حذف المستأجر لوجود بيانات نشطة');
        }

        $tenant->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'تم حذف المستأجر بنجاح'
            ]);
        }

        return redirect()->route('master-admin.tenants.index')
                        ->with('success', 'تم حذف المستأجر بنجاح');
    }

    /**
     * Toggle tenant status
     */
    public function toggleStatus(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);

        $status = $tenant->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $status . ' المستأجر بنجاح',
                'tenant' => $tenant
            ]);
        }

        return redirect()->route('master-admin.tenants.index')
                        ->with('success', $status . ' المستأجر بنجاح');
    }

    /**
     * Extend tenant license
     */
    public function extendLicense(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:24',
        ]);

        $currentExpiry = $tenant->license_expires_at ?: now();
        $newExpiry = Carbon::parse($currentExpiry)->addMonths($validated['months']);
        
        $tenant->update(['license_expires_at' => $newExpiry]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'تم تمديد الترخيص بنجاح',
                'tenant' => $tenant,
                'new_expiry' => $newExpiry->format('Y-m-d')
            ]);
        }

        return redirect()->route('master-admin.tenants.show', $tenant)
                        ->with('success', 'تم تمديد الترخيص بنجاح');
    }

    /**
     * Show pending tenants (inactive tenants that might need approval)
     */
    public function pending()
    {
        // Since we don't have a status column, we'll consider inactive tenants as "pending"
        // or tenants that were recently created but not yet activated
        $tenants = Tenant::where('is_active', false)
                        ->where('created_at', '>=', now()->subDays(30)) // Recent inactive tenants
                        ->latest()
                        ->paginate(20);
        return view('master-admin.tenants.pending', compact('tenants'));
    }

    /**
     * Show expired tenants
     */
    public function expired()
    {
        $tenants = Tenant::withExpiredLicense()->latest()->paginate(20);
        return view('master-admin.tenants.expired', compact('tenants'));
    }

    /**
     * Calculate storage used by tenant (simulated)
     */
    private function calculateStorageUsed(Tenant $tenant): string
    {
        $userCount = $tenant->users()->count();
        $estimatedMB = $userCount * 50; // 50MB per user estimate
        return $estimatedMB . ' MB';
    }

    /**
     * Calculate monthly revenue from tenant (simulated)
     */
    private function calculateMonthlyRevenue(Tenant $tenant): int
    {
        $basePrice = 500000; // Base price in IQD
        $userPrice = 50000; // Price per additional user
        $additionalUsers = max(0, $tenant->users()->count() - 5); // First 5 users included
        
        return $basePrice + ($additionalUsers * $userPrice);
    }
}
