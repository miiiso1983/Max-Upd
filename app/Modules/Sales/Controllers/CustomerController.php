<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // If this is an API request, return JSON
        if ($request->expectsJson()) {
            return $this->getCustomersApi($request);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->get('type'));
        }

        // Filter by governorate
        if ($request->has('governorate')) {
            $query->where('governorate', $request->get('governorate'));
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Filter by outstanding balance
        if ($request->has('has_outstanding')) {
            $query->withOutstandingBalance();
        }

        $customers = $query->orderBy('name')
                          ->paginate(20);

        // Add calculated fields
        $customers->getCollection()->transform(function ($customer) {
            $customer->total_sales = $customer->getTotalSales();
            $customer->outstanding_balance = $customer->getOutstandingBalance();
            $customer->available_credit = $customer->getAvailableCredit();
            $customer->last_order_date = $customer->getLastOrderDate();
            $customer->total_orders = $customer->getTotalOrdersCount();
            return $customer;
        });

        return view('sales.customers.index', [
            'customers' => $customers,
            'filters' => [
                'types' => Customer::getTypes(),
                'types_ar' => Customer::getTypesAr(),
                'governorates' => $this->getGovernorates(),
            ],
            'request' => $request
        ]);
    }

    /**
     * API method for customers listing
     */
    private function getCustomersApi(Request $request)
    {
        $query = Customer::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->get('type'));
        }

        // Filter by governorate
        if ($request->has('governorate')) {
            $query->where('governorate', $request->get('governorate'));
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Filter by outstanding balance
        if ($request->has('has_outstanding')) {
            $query->withOutstandingBalance();
        }

        $customers = $query->orderBy('name')
                          ->paginate(20);

        // Add calculated fields
        $customers->getCollection()->transform(function ($customer) {
            $customer->total_sales = $customer->getTotalSales();
            $customer->outstanding_balance = $customer->getOutstandingBalance();
            $customer->available_credit = $customer->getAvailableCredit();
            $customer->last_order_date = $customer->getLastOrderDate();
            $customer->total_orders = $customer->getTotalOrdersCount();
            return $customer;
        });

        return response()->json([
            'customers' => $customers,
            'filters' => [
                'types' => Customer::getTypes(),
                'types_ar' => Customer::getTypesAr(),
                'governorates' => $this->getGovernorates(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        return view('sales.customers.create', [
            'types' => Customer::getTypes(),
            'types_ar' => Customer::getTypesAr(),
            'governorates' => $this->getGovernorates(),
        ]);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Customer::getTypes())),
            'code' => 'nullable|string|max:255|unique:customers,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer
        ], 201);
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        $customer->load(['salesOrders.items.product', 'invoices.payments']);
        
        $customer->total_sales = $customer->getTotalSales();
        $customer->outstanding_balance = $customer->getOutstandingBalance();
        $customer->available_credit = $customer->getAvailableCredit();
        $customer->last_order_date = $customer->getLastOrderDate();
        $customer->total_orders = $customer->getTotalOrdersCount();

        // Get recent orders
        $recentOrders = $customer->salesOrders()
                               ->with(['items.product'])
                               ->latest()
                               ->take(10)
                               ->get();

        // Get recent invoices
        $recentInvoices = $customer->invoices()
                                 ->with(['payments'])
                                 ->latest()
                                 ->take(10)
                                 ->get();

        // Get sales history (last 12 months)
        $salesHistory = $customer->invoices()
                               ->selectRaw('YEAR(invoice_date) as year, MONTH(invoice_date) as month, SUM(total_amount) as total_sales')
                               ->where('status', 'paid')
                               ->where('invoice_date', '>=', now()->subYear())
                               ->groupBy('year', 'month')
                               ->orderBy('year')
                               ->orderBy('month')
                               ->get();

        return response()->json([
            'customer' => $customer,
            'recent_orders' => $recentOrders,
            'recent_invoices' => $recentInvoices,
            'sales_history' => $salesHistory,
        ]);
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(Customer $customer)
    {
        return view('sales.customers.edit', [
            'customer' => $customer,
            'types' => Customer::getTypes(),
            'types_ar' => Customer::getTypesAr(),
            'governorates' => $this->getGovernorates(),
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Customer::getTypes())),
            'code' => ['nullable', 'string', 'max:255', Rule::unique('customers')->ignore($customer->id)],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer->fresh()
        ]);
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has orders or invoices
        if ($customer->salesOrders()->exists() || $customer->invoices()->exists()) {
            return response()->json([
                'message' => 'Cannot delete customer with existing orders or invoices'
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Get customer statistics
     */
    public function statistics(Customer $customer, Request $request)
    {
        $startDate = $request->get('start_date', now()->subYear());
        $endDate = $request->get('end_date', now());

        $stats = [
            'total_orders' => $customer->salesOrders()
                                     ->whereBetween('order_date', [$startDate, $endDate])
                                     ->count(),
            'total_sales' => $customer->invoices()
                                    ->where('status', 'paid')
                                    ->whereBetween('invoice_date', [$startDate, $endDate])
                                    ->sum('total_amount'),
            'average_order_value' => $customer->salesOrders()
                                            ->whereBetween('order_date', [$startDate, $endDate])
                                            ->avg('total_amount') ?? 0,
            'outstanding_balance' => $customer->getOutstandingBalance(),
            'available_credit' => $customer->getAvailableCredit(),
            'last_order_date' => $customer->getLastOrderDate(),
            'payment_history' => $customer->invoices()
                                        ->with('payments')
                                        ->whereBetween('invoice_date', [$startDate, $endDate])
                                        ->get()
                                        ->flatMap->payments
                                        ->where('status', 'completed')
                                        ->groupBy('payment_method')
                                        ->map(function ($payments, $method) {
                                            return [
                                                'method' => $method,
                                                'count' => $payments->count(),
                                                'total_amount' => $payments->sum('amount'),
                                            ];
                                        })
                                        ->values(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk update customers
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
            'updates' => 'required|array',
        ]);

        $customerIds = $request->get('customer_ids');
        $updates = $request->get('updates');

        // Add updated_by to updates
        $updates['updated_by'] = auth()->id();

        Customer::whereIn('id', $customerIds)->update($updates);

        return response()->json([
            'message' => 'Customers updated successfully',
            'updated_count' => count($customerIds)
        ]);
    }

    /**
     * Export customers to Excel
     */
    public function export(Request $request)
    {
        // This would implement Excel export functionality
        // For now, return a placeholder response
        return response()->json([
            'message' => 'Export functionality will be implemented with Laravel Excel'
        ]);
    }

    /**
     * Import customers from Excel
     */
    public function import(Request $request)
    {
        // This would implement Excel import functionality
        // For now, return a placeholder response
        return response()->json([
            'message' => 'Import functionality will be implemented with Laravel Excel'
        ]);
    }

    /**
     * Get Iraqi governorates
     */
    private function getGovernorates()
    {
        return [
            'Baghdad' => 'بغداد',
            'Basra' => 'البصرة',
            'Nineveh' => 'نينوى',
            'Erbil' => 'أربيل',
            'Sulaymaniyah' => 'السليمانية',
            'Dohuk' => 'دهوك',
            'Anbar' => 'الأنبار',
            'Babylon' => 'بابل',
            'Karbala' => 'كربلاء',
            'Najaf' => 'النجف',
            'Qadisiyyah' => 'القادسية',
            'Muthanna' => 'المثنى',
            'Dhi Qar' => 'ذي قار',
            'Maysan' => 'ميسان',
            'Wasit' => 'واسط',
            'Saladin' => 'صلاح الدين',
            'Diyala' => 'ديالى',
            'Kirkuk' => 'كركوك',
        ];
    }

    /**
     * Toggle customer status
     */
    public function toggleStatus(Request $request, Customer $customer)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $customer->update([
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->is_active ? 'تم تفعيل العميل بنجاح' : 'تم إيقاف العميل بنجاح'
        ]);
    }

    /**
     * Get customer orders
     */
    public function orders(Customer $customer)
    {
        $orders = $customer->salesOrders()
                          ->with(['items.product'])
                          ->orderBy('order_date', 'desc')
                          ->paginate(20);

        return response()->json(['orders' => $orders]);
    }

    /**
     * Get customer invoices
     */
    public function invoices(Customer $customer)
    {
        $invoices = $customer->invoices()
                            ->with(['payments'])
                            ->orderBy('invoice_date', 'desc')
                            ->paginate(20);

        return response()->json(['invoices' => $invoices]);
    }

    /**
     * Get customer payments
     */
    public function payments(Customer $customer)
    {
        $payments = $customer->payments()
                            ->with(['invoice'])
                            ->orderBy('payment_date', 'desc')
                            ->paginate(20);

        return response()->json(['payments' => $payments]);
    }




}
