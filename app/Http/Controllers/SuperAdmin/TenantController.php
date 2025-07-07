<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // If this is an API request, return JSON
        if ($request->expectsJson()) {
            return $this->getTenantsApi($request);
        }

        $query = Tenant::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->withExpiredLicense();
            }
        }

        // Filter by company type
        if ($request->has('company_type')) {
            $query->where('company_type', $request->get('company_type'));
        }

        // Filter by governorate
        if ($request->has('governorate')) {
            $query->where('governorate', $request->get('governorate'));
        }

        $tenants = $query->with('creator')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        // Add statistics for each tenant
        $tenants->getCollection()->transform(function ($tenant) {
            $tenant->statistics = $tenant->getStatistics();
            return $tenant;
        });

        return view('super-admin.tenants.index', [
            'tenants' => $tenants,
            'filters' => [
                'company_types' => ['pharmacy', 'medical_distributor', 'clinic', 'hospital', 'other'],
                'governorates' => $this->getIraqGovernorates(),
            ],
            'request' => $request
        ]);
    }

    /**
     * API method for tenants listing
     */
    private function getTenantsApi(Request $request)
    {
        $query = Tenant::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by subscription status
        if ($request->has('subscription_status')) {
            $query->where('subscription_status', $request->get('subscription_status'));
        }

        // Filter by company type
        if ($request->has('company_type')) {
            $query->where('company_type', $request->get('company_type'));
        }

        // Filter by governorate
        if ($request->has('governorate')) {
            $query->where('governorate', $request->get('governorate'));
        }

        $tenants = $query->with('creator')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        // Add statistics for each tenant
        $tenants->getCollection()->transform(function ($tenant) {
            $tenant->statistics = $tenant->getStatistics();
            return $tenant;
        });

        return response()->json([
            'tenants' => $tenants,
            'filters' => [
                'company_types' => ['pharmacy', 'medical_distributor', 'clinic', 'hospital', 'other'],
                'governorates' => $this->getIraqGovernorates(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => [
                'required',
                'string',
                'max:255',
                'unique:tenants,domain',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$|^[a-zA-Z0-9]$/',
                function ($attribute, $value, $fail) {
                    // Convert to lowercase for storage
                    $value = strtolower($value);

                    // Check if it contains consecutive hyphens
                    if (strpos($value, '--') !== false) {
                        $fail('The domain field cannot contain consecutive hyphens.');
                    }

                    // Check if it starts or ends with hyphen (except single character)
                    if (strlen($value) > 1 && (str_starts_with($value, '-') || str_ends_with($value, '-'))) {
                        $fail('The domain field cannot start or end with a hyphen.');
                    }

                    // Check for reserved words
                    $reserved = ['www', 'api', 'admin', 'mail', 'ftp', 'localhost', 'test'];
                    if (in_array($value, $reserved)) {
                        $fail('The domain field cannot use reserved words.');
                    }
                }
            ],
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:pharmacy,medical_distributor,clinic,hospital,other',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'governorate' => 'required|string|max:255',
            'max_users' => 'required|integer|min:1|max:1000',
            'license_expires_at' => 'nullable|date|after:today',
            // Admin user fields (optional for backward compatibility)
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => 'nullable|email|unique:users,email',
            'admin_password' => 'nullable|string|min:8',
        ], [
            'domain.regex' => 'The domain field must contain only letters, numbers, and hyphens. / يجب أن يحتوي حقل النطاق على أحرف وأرقام وشرطات فقط.',
            'domain.unique' => 'This domain is already taken. / هذا النطاق مستخدم بالفعل.',
            'domain.required' => 'The domain field is required. / حقل النطاق مطلوب.',
            'email.unique' => 'This email is already registered. / هذا البريد الإلكتروني مسجل بالفعل.',
            'company_type.in' => 'Please select a valid company type. / يرجى اختيار نوع شركة صحيح.',
        ]);

        try {
            // Normalize domain to lowercase
            $validated['domain'] = strtolower($validated['domain']);

            // Generate unique license key
            $validated['license_key'] = 'MAXCON-' . strtoupper($validated['domain']) . '-' . Str::upper(Str::random(8));
            $validated['database'] = 'maxcon_tenant_' . $validated['domain'];
            $validated['created_by'] = auth()->id();

            // Create tenant
            $tenant = Tenant::create($validated);

            // Create tenant admin user if admin details provided
            if (!empty($validated['admin_name']) && !empty($validated['admin_email']) && !empty($validated['admin_password'])) {
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
            } else {
                // Create default admin user with auto-generated credentials
                $defaultPassword = Str::random(12);
                $adminUser = User::create([
                    'name' => $validated['contact_person'],
                    'email' => $validated['email'],
                    'password' => Hash::make($defaultPassword),
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                // Assign tenant-admin role
                $adminUser->assignRole('tenant-admin');

                // Store the generated password to return it
                $tenant->default_password = $defaultPassword;
            }

            // Create tenant database
            $this->createTenantDatabase($tenant->database);

            // Run tenant migrations (commented out for now)
            // $this->runTenantMigrations($tenant);

            $response = [
                'message' => 'Tenant created successfully',
                'tenant' => $tenant->load(['creator', 'users']),
            ];

            // Include default password if generated
            if (isset($defaultPassword)) {
                $response['admin_credentials'] = [
                    'email' => $validated['email'],
                    'password' => $defaultPassword,
                    'message' => 'Default admin credentials generated. Please share these with the tenant admin.'
                ];
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tenant = Tenant::with('creator')->findOrFail($id);
        $tenant->statistics = $tenant->getStatistics();

        return response()->json([
            'tenant' => $tenant
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tenant = Tenant::with('creator')->findOrFail($id);

        if (request()->expectsJson()) {
            return response()->json([
                'tenant' => $tenant,
                'filters' => [
                    'company_types' => ['pharmacy', 'medical_distributor', 'clinic', 'hospital', 'other'],
                    'governorates' => $this->getIraqGovernorates(),
                ]
            ]);
        }

        return view('super-admin.tenants.edit', [
            'tenant' => $tenant,
            'filters' => [
                'company_types' => ['pharmacy', 'medical_distributor', 'clinic', 'hospital', 'other'],
                'governorates' => $this->getIraqGovernorates(),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:pharmacy,medical_distributor,clinic,hospital,other',
            'contact_person' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('tenants')->ignore($tenant->id)],
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'governorate' => 'required|string|max:255',
            'max_users' => 'required|integer|min:1|max:1000',
            'license_expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $tenant->update($validated);

        return response()->json([
            'message' => 'Tenant updated successfully',
            'tenant' => $tenant->fresh()->load('creator'),
        ]);
    }





    /**
     * Extend tenant license
     */
    public function extendLicense(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:60',
        ]);

        $currentExpiry = $tenant->license_expires_at ?: now();
        $newExpiry = $currentExpiry->addMonths($validated['months']);

        $tenant->update(['license_expires_at' => $newExpiry]);

        return response()->json([
            'message' => 'License extended successfully',
            'tenant' => $tenant->fresh(),
            'new_expiry' => $newExpiry->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get tenant statistics
     */
    public function statistics()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::active()->count();
        $expiredLicenses = Tenant::withExpiredLicense()->count();
        $tenantsByType = Tenant::select('company_type', DB::raw('count(*) as count'))
                              ->groupBy('company_type')
                              ->get();

        return response()->json([
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'inactive_tenants' => $totalTenants - $activeTenants,
            'expired_licenses' => $expiredLicenses,
            'tenants_by_type' => $tenantsByType,
            'recent_tenants' => Tenant::latest()->take(5)->get(),
        ]);
    }

    /**
     * Create tenant database
     */
    private function createTenantDatabase(string $databaseName): void
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Run migrations for tenant database
     */
    private function runTenantMigrations(Tenant $tenant): void
    {
        // This would typically run tenant-specific migrations
        // For now, we'll just ensure the tenant can be made current
        $tenant->makeCurrent();

        // Here you would run: Artisan::call('migrate', ['--database' => 'tenant']);
        // But we'll implement this when we create the tenant-specific migrations
    }

    /**
     * Get Iraq governorates
     */
    private function getIraqGovernorates(): array
    {
        return [
            'Baghdad',
            'Basra',
            'Nineveh',
            'Erbil',
            'Sulaymaniyah',
            'Dohuk',
            'Anbar',
            'Babylon',
            'Karbala',
            'Najaf',
            'Qadisiyyah',
            'Muthanna',
            'Dhi Qar',
            'Maysan',
            'Wasit',
            'Saladin',
            'Diyala',
            'Kirkuk',
        ];
    }

    /**
     * Generate a valid domain from company name
     */
    public function generateDomain(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
        ]);

        $companyName = $request->company_name;

        // Convert to lowercase and remove special characters
        $domain = strtolower($companyName);

        // Replace Arabic characters with English equivalents
        $arabicToEnglish = [
            'ا' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j',
            'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'th', 'ر' => 'r',
            'ز' => 'z', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd',
            'ط' => 't', 'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f',
            'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ة' => 'h', 'ى' => 'a',
            'ء' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'a', 'ؤ' => 'w',
            'ئ' => 'y', 'لا' => 'la'
        ];

        $domain = str_replace(array_keys($arabicToEnglish), array_values($arabicToEnglish), $domain);

        // Remove non-alphanumeric characters except hyphens
        $domain = preg_replace('/[^a-z0-9\s-]/', '', $domain);

        // Replace spaces with hyphens
        $domain = preg_replace('/\s+/', '-', $domain);

        // Remove consecutive hyphens
        $domain = preg_replace('/-+/', '-', $domain);

        // Remove leading/trailing hyphens
        $domain = trim($domain, '-');

        // Ensure minimum length
        if (strlen($domain) < 3) {
            $domain = $domain . '-company';
        }

        // Ensure maximum length
        if (strlen($domain) > 50) {
            $domain = substr($domain, 0, 50);
            $domain = rtrim($domain, '-');
        }

        // Check if domain exists and add number if needed
        $originalDomain = $domain;
        $counter = 1;

        while (Tenant::where('domain', $domain)->exists()) {
            $domain = $originalDomain . '-' . $counter;
            $counter++;
        }

        return response()->json([
            'suggested_domain' => $domain,
            'is_available' => !Tenant::where('domain', $domain)->exists(),
        ]);
    }

    /**
     * Check domain availability
     */
    public function checkDomain(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $domain = strtolower($request->domain);
        $isAvailable = !Tenant::where('domain', $domain)->exists();

        // Validate domain format
        $isValidFormat = preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$|^[a-zA-Z0-9]$/', $domain);

        // Check for reserved words
        $reserved = ['www', 'api', 'admin', 'mail', 'ftp', 'localhost', 'test'];
        $isReserved = in_array($domain, $reserved);

        return response()->json([
            'domain' => $domain,
            'is_available' => $isAvailable,
            'is_valid_format' => (bool) $isValidFormat,
            'is_reserved' => $isReserved,
            'message' => $this->getDomainValidationMessage($isAvailable, $isValidFormat, $isReserved),
        ]);
    }

    /**
     * Get domain validation message
     */
    private function getDomainValidationMessage(bool $isAvailable, bool $isValidFormat, bool $isReserved): string
    {
        if ($isReserved) {
            return 'This domain uses a reserved word and cannot be used. / هذا النطاق يستخدم كلمة محجوزة ولا يمكن استخدامه.';
        }

        if (!$isValidFormat) {
            return 'Invalid domain format. Use only letters, numbers, and hyphens. / تنسيق النطاق غير صحيح. استخدم الأحرف والأرقام والشرطات فقط.';
        }

        if (!$isAvailable) {
            return 'This domain is already taken. / هذا النطاق مستخدم بالفعل.';
        }

        return 'Domain is available! / النطاق متاح!';
    }

    /**
     * Get tenant information by domain (public endpoint)
     */
    public function getTenantInfo(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $domain = strtolower($request->domain);
        $tenant = Tenant::where('domain', $domain)->where('is_active', true)->first();

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found or inactive',
            ], 404);
        }

        // Check if tenant license is valid
        if (!$tenant->hasValidLicense()) {
            return response()->json([
                'message' => 'Tenant license has expired',
            ], 403);
        }

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'company_name' => $tenant->company_name,
                'company_type' => $tenant->company_type,
                'is_active' => $tenant->is_active,
            ]
        ]);
    }

    /**
     * Toggle tenant status
     */
    public function toggleStatus(Request $request, Tenant $tenant)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $tenant->update([
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->is_active ? 'تم تفعيل المستأجر بنجاح' : 'تم إيقاف المستأجر بنجاح'
        ]);
    }

    /**
     * Delete tenant
     */
    public function destroy(Tenant $tenant)
    {
        try {
            // Check if tenant has active data
            if ($tenant->hasActiveData()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف المستأجر لوجود بيانات نشطة'
                ], 400);
            }

            $tenant->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المستأجر بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المستأجر'
            ], 500);
        }
    }
}
