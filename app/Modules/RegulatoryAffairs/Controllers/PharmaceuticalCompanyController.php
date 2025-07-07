<?php

namespace App\Modules\RegulatoryAffairs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PharmaceuticalCompanyController extends Controller
{
    /**
     * Display a listing of pharmaceutical companies
     */
    public function index(Request $request)
    {
        $query = PharmaceuticalCompany::with(['creator', 'updater']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%")
                  ->orWhere('trade_name', 'like', "%{$search}%")
                  ->orWhere('trade_name_ar', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by license status
        if ($request->filled('license_status')) {
            $query->where('license_status', $request->license_status);
        }

        // Filter by company type
        if ($request->filled('company_type')) {
            $query->where('company_type', $request->company_type);
        }

        // Filter by country
        if ($request->filled('country_of_origin')) {
            $query->where('country_of_origin', $request->country_of_origin);
        }

        // Filter by risk level
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        // Filter expiring licenses
        if ($request->filled('expiring_licenses')) {
            $days = $request->get('expiring_days', 30);
            $query->licenseExpiring($days);
        }

        $companies = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total' => PharmaceuticalCompany::count(),
            'active' => PharmaceuticalCompany::where('status', 'active')->count(),
            'license_expiring' => PharmaceuticalCompany::licenseExpiring(30)->count(),
            'gmp_expiring' => PharmaceuticalCompany::gmpExpiring(30)->count(),
        ];

        // Get filter options
        $countries = PharmaceuticalCompany::distinct('country_of_origin')
                                        ->pluck('country_of_origin')
                                        ->filter()
                                        ->sort()
                                        ->values();

        return view('regulatory-affairs.companies.index', compact('companies', 'stats', 'countries'));
    }

    /**
     * Show the form for creating a new pharmaceutical company
     */
    public function create()
    {
        return view('regulatory-affairs.companies.create');
    }

    /**
     * Store a newly created pharmaceutical company
     */
    public function store(Request $request)
    {
        $request->validate([
            'registration_number' => 'required|string|unique:pharmaceutical_companies',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'country_of_origin' => 'required|string|max:100',
            'company_type' => 'required|in:manufacturer,distributor,importer,exporter',
            'license_number' => 'required|string|unique:pharmaceutical_companies',
            'license_issue_date' => 'required|date',
            'license_expiry_date' => 'required|date|after:license_issue_date',
            'regulatory_authority' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_person' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $company = PharmaceuticalCompany::create([
                ...$request->all(),
                'created_by' => auth()->user()?->id,
            ]);

            DB::commit();

            return redirect()->route('regulatory-affairs.companies.show', $company)
                           ->with('success', 'تم إنشاء الشركة الدوائية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء الشركة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified pharmaceutical company
     *
     * @param PharmaceuticalCompany $company
     * @return \Illuminate\View\View
     */
    public function show(PharmaceuticalCompany $company)
    {
        $company->load(['products', 'inspections.creator']);

        // Get company statistics
        $stats = [
            'products_count' => $company->products()->count(),
            'active_products' => $company->products()->where('status', 'active')->count(),
            'inspections_count' => $company->inspections()->count(),
            'last_inspection' => $company->inspections()->latest('inspection_date')->first(),
            'compliance_score' => $company->getComplianceScore(),
        ];

        // Get recent activities
        $recentProducts = $company->products()->latest()->take(5)->get();
        $recentInspections = $company->inspections()->latest()->take(5)->get();

        // Check for alerts
        $alerts = [];
        
        if ($company->isLicenseExpiring(30)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'ترخيص الشركة ينتهي خلال ' . $company->getDaysUntilLicenseExpiry() . ' يوم',
            ];
        }

        if ($company->isGmpExpiring(30)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'شهادة GMP تنتهي خلال ' . $company->getDaysUntilGmpExpiry() . ' يوم',
            ];
        }

        if ($company->needsInspection()) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'الشركة تحتاج إلى تفتيش',
            ];
        }

        return view('regulatory-affairs.companies.show', compact(
            'company', 
            'stats', 
            'recentProducts', 
            'recentInspections', 
            'alerts'
        ));
    }

    /**
     * Show the form for editing the specified pharmaceutical company
     */
    public function edit(PharmaceuticalCompany $company)
    {
        return view('regulatory-affairs.companies.edit', compact('company'));
    }

    /**
     * Update the specified pharmaceutical company
     */
    public function update(Request $request, PharmaceuticalCompany $company)
    {
        $request->validate([
            'registration_number' => 'required|string|unique:pharmaceutical_companies,registration_number,' . $company->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'country_of_origin' => 'required|string|max:100',
            'company_type' => 'required|in:manufacturer,distributor,importer,exporter',
            'license_number' => 'required|string|unique:pharmaceutical_companies,license_number,' . $company->id,
            'license_issue_date' => 'required|date',
            'license_expiry_date' => 'required|date|after:license_issue_date',
            'regulatory_authority' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'contact_person' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $company->update([
                ...$request->all(),
                'updated_by' => auth()->user()?->id,
            ]);

            DB::commit();

            return redirect()->route('regulatory-affairs.companies.show', $company)
                           ->with('success', 'تم تحديث الشركة الدوائية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث الشركة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified pharmaceutical company
     */
    public function destroy(PharmaceuticalCompany $company)
    {
        try {
            DB::beginTransaction();

            // Check if company has products
            if ($company->products()->count() > 0) {
                return back()->with('error', 'لا يمكن حذف الشركة لأنها تحتوي على منتجات مسجلة');
            }

            $company->delete();

            DB::commit();

            return redirect()->route('regulatory-affairs.companies.index')
                           ->with('success', 'تم حذف الشركة الدوائية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء حذف الشركة: ' . $e->getMessage());
        }
    }

    /**
     * Get companies expiring licenses report
     */
    public function expiringLicenses(Request $request)
    {
        $days = $request->get('days', 30);
        
        $companies = PharmaceuticalCompany::licenseExpiring($days)
                                        ->with(['creator'])
                                        ->orderBy('license_expiry_date', 'asc')
                                        ->paginate(20);

        return view('regulatory-affairs.companies.expiring-licenses', compact('companies', 'days'));
    }

    /**
     * Get companies GMP expiring report
     */
    public function expiringGmp(Request $request)
    {
        $days = $request->get('days', 30);
        
        $companies = PharmaceuticalCompany::gmpExpiring($days)
                                        ->with(['creator'])
                                        ->orderBy('gmp_expiry_date', 'asc')
                                        ->paginate(20);

        return view('regulatory-affairs.companies.expiring-gmp', compact('companies', 'days'));
    }

    /**
     * Update company status
     */
    public function updateStatus(Request $request, PharmaceuticalCompany $company)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,under_review,suspended',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $company->update([
                'status' => $request->status,
                'notes' => $request->reason ? $company->notes . "\n" . now()->format('Y-m-d H:i') . ": " . $request->reason : $company->notes,
                'updated_by' => auth()->user()?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الشركة بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get company dashboard data
     */
    public function dashboard()
    {
        $stats = [
            'total_companies' => PharmaceuticalCompany::count(),
            'active_companies' => PharmaceuticalCompany::where('status', 'active')->count(),
            'license_expiring' => PharmaceuticalCompany::licenseExpiring(30)->count(),
            'gmp_expiring' => PharmaceuticalCompany::gmpExpiring(30)->count(),
            'high_risk' => PharmaceuticalCompany::where('risk_level', 'high')->count(),
        ];

        // Get recent companies
        $recentCompanies = PharmaceuticalCompany::with(['creator'])
                                              ->latest()
                                              ->take(5)
                                              ->get();

        // Get companies by type
        $companiesByType = PharmaceuticalCompany::select('company_type', DB::raw('count(*) as count'))
                                              ->groupBy('company_type')
                                              ->get();

        // Get companies by country
        $companiesByCountry = PharmaceuticalCompany::select('country_of_origin', DB::raw('count(*) as count'))
                                                 ->groupBy('country_of_origin')
                                                 ->orderBy('count', 'desc')
                                                 ->take(10)
                                                 ->get();

        return view('regulatory-affairs.companies.dashboard', compact(
            'stats',
            'recentCompanies',
            'companiesByType',
            'companiesByCountry'
        ));
    }
}
