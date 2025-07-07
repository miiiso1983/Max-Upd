@extends('layouts.app')

@section('title', 'المبيعات - MaxCon ERP')
@section('page-title', 'إدارة المبيعات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">إدارة المبيعات</h1>
                <p class="text-blue-100">تتبع وإدارة جميع عمليات المبيعات والفواتير والمدفوعات</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">مبيعات اليوم</p>
                    <p class="text-2xl font-bold text-gray-900" id="today-sales">0</p>
                    <p class="text-xs text-green-600 flex items-center mt-1">
                        <i class="fas fa-arrow-up ml-1"></i>
                        <span id="today-growth">0%</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">طلبات جديدة</p>
                    <p class="text-2xl font-bold text-gray-900" id="new-orders">0</p>
                    <p class="text-xs text-blue-600 flex items-center mt-1">
                        <i class="fas fa-shopping-cart ml-1"></i>
                        اليوم
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">فواتير معلقة</p>
                    <p class="text-2xl font-bold text-gray-900" id="pending-invoices">0</p>
                    <p class="text-xs text-orange-600 flex items-center mt-1">
                        <i class="fas fa-clock ml-1"></i>
                        تحتاج متابعة
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">متوسط قيمة الطلب</p>
                    <p class="text-2xl font-bold text-gray-900" id="avg-order-value">0</p>
                    <p class="text-xs text-purple-600 flex items-center mt-1">
                        <i class="fas fa-chart-bar ml-1"></i>
                        هذا الشهر
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('sales.orders.create') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors hover-scale">
                <i class="fas fa-plus-circle text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">طلب مبيعات جديد</span>
            </a>
            <a href="{{ route('sales.invoices.create') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors hover-scale">
                <i class="fas fa-file-invoice text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-800">فاتورة جديدة</span>
            </a>
            <a href="{{ route('sales.customers.create') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors hover-scale">
                <i class="fas fa-user-plus text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">عميل جديد</span>
            </a>
            <a href="{{ route('sales.payments.create') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors hover-scale">
                <i class="fas fa-credit-card text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">تسجيل دفعة</span>
            </a>
        </div>
    </div>

    <!-- Sales Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أحدث الطلبات</h3>
                <a href="{{ route('sales.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800">عرض الكل</a>
            </div>
            <div class="space-y-3" id="recent-orders">
                <!-- Orders will be loaded here -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">جاري التحميل...</p>
                </div>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أحدث الفواتير</h3>
                <a href="{{ route('sales.invoices.index') }}" class="text-sm text-blue-600 hover:text-blue-800">عرض الكل</a>
            </div>
            <div class="space-y-3" id="recent-invoices">
                <!-- Invoices will be loaded here -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">جاري التحميل...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">اتجاه المبيعات</h3>
            <div class="flex space-x-2 space-x-reverse">
                <button class="px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded-full" onclick="loadSalesChart('week')">أسبوع</button>
                <button class="px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded-full" onclick="loadSalesChart('month')">شهر</button>
                <button class="px-3 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded-full" onclick="loadSalesChart('year')">سنة</button>
            </div>
        </div>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg" id="sales-chart">
            <div class="text-center">
                <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">مخطط المبيعات</p>
                <p class="text-sm text-gray-400">سيتم عرض البيانات قريباً</p>
            </div>
        </div>
    </div>

    <!-- Top Customers and Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Customers -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أفضل العملاء</h3>
                <a href="{{ route('reports.sales.customers') }}" class="text-sm text-blue-600 hover:text-blue-800">تقرير مفصل</a>
            </div>
            <div class="space-y-3" id="top-customers">
                <!-- Top customers will be loaded here -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">جاري التحميل...</p>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أكثر المنتجات مبيعاً</h3>
                <a href="{{ route('reports.sales.products') }}" class="text-sm text-blue-600 hover:text-blue-800">تقرير مفصل</a>
            </div>
            <div class="space-y-3" id="top-products">
                <!-- Top products will be loaded here -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">جاري التحميل...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

function loadDashboardData() {
    // Load dashboard statistics
    fetch('/api/sales/dashboard')
        .then(response => response.json())
        .then(data => {
            updateStats(data.stats);
            updateRecentOrders(data.recent_orders);
            updateRecentInvoices(data.recent_invoices);
            updateTopCustomers(data.top_customers);
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
        });
}

function updateStats(stats) {
    document.getElementById('today-sales').textContent = formatNumber(stats.total_sales || 0);
    document.getElementById('new-orders').textContent = stats.total_orders || 0;
    document.getElementById('pending-invoices').textContent = stats.pending_invoices || 0;
    document.getElementById('avg-order-value').textContent = formatNumber(stats.average_order_value || 0);
}

function updateRecentOrders(orders) {
    const container = document.getElementById('recent-orders');
    if (orders && orders.length > 0) {
        container.innerHTML = orders.map(order => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">${order.order_number}</p>
                    <p class="text-sm text-gray-600">${order.customer.name}</p>
                </div>
                <div class="text-left">
                    <p class="font-medium text-gray-900">${formatNumber(order.total_amount)} د.ع</p>
                    <p class="text-xs text-gray-500">${formatDate(order.created_at)}</p>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">لا توجد طلبات حديثة</p>
            </div>
        `;
    }
}

function updateRecentInvoices(invoices) {
    const container = document.getElementById('recent-invoices');
    if (invoices && invoices.length > 0) {
        container.innerHTML = invoices.map(invoice => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">${invoice.invoice_number}</p>
                    <p class="text-sm text-gray-600">${invoice.customer.name}</p>
                </div>
                <div class="text-left">
                    <p class="font-medium text-gray-900">${formatNumber(invoice.total_amount)} د.ع</p>
                    <span class="text-xs px-2 py-1 rounded-full ${getStatusClass(invoice.status)}">${getStatusText(invoice.status)}</span>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-file-invoice text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">لا توجد فواتير حديثة</p>
            </div>
        `;
    }
}

function updateTopCustomers(customers) {
    const container = document.getElementById('top-customers');
    if (customers && customers.length > 0) {
        container.innerHTML = customers.map((customer, index) => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold ml-3">${index + 1}</span>
                    <span class="font-medium text-gray-900">${customer.name}</span>
                </div>
                <span class="font-medium text-gray-900">${formatNumber(customer.total_sales)} د.ع</span>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">لا توجد بيانات عملاء</p>
            </div>
        `;
    }
}

function formatNumber(number) {
    return new Intl.NumberFormat('ar-IQ').format(number);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('ar-IQ');
}

function getStatusClass(status) {
    switch(status) {
        case 'paid': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'overdue': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'paid': return 'مدفوعة';
        case 'pending': return 'معلقة';
        case 'overdue': return 'متأخرة';
        default: return status;
    }
}

function loadSalesChart(period) {
    // This will be implemented when we add chart library
    console.log('Loading sales chart for period:', period);
}
</script>
@endpush
