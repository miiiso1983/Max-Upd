@extends('layouts.app')

@section('title', 'لوحة التحكم - MaxCon ERP')
@section('page-title', 'لوحة التحكم')

@push('styles')
<style>
/* Dashboard Hover Effects */
.bg-white:hover h1,
.bg-white:hover h2,
.bg-white:hover h3,
.bg-white:hover p,
.bg-white:hover span {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.bg-white:hover .text-gray-600,
.bg-white:hover .text-gray-900 {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.bg-white:hover i {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

/* Welcome section hover */
.bg-gradient-to-r:hover h1 {
    color: #6f42c1 !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.bg-gradient-to-r:hover p {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

/* Icon background hover */
.bg-green-100:hover,
.bg-blue-100:hover,
.bg-purple-100:hover,
.bg-orange-100:hover {
    background: #6f42c1 !important;
    transition: background 0.3s ease;
}

.bg-green-100:hover i,
.bg-blue-100:hover i,
.bg-purple-100:hover i,
.bg-orange-100:hover i {
    color: white !important;
    transition: color 0.3s ease;
}

/* Chart hover effects */
.chart-container:hover {
    transform: scale(1.02);
    transition: transform 0.3s ease;
}

/* Button hover effects */
.btn:hover,
button:hover {
    background: #6f42c1 !important;
    color: white !important;
    transition: all 0.3s ease;
}

/* Table hover effects */
table tr:hover td {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

/* Link hover effects */
a:hover {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}
</style>
@endpush

@section('content')
<div class="container-responsive">
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg p-4 md:p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold mb-2">مرحباً بك في MaxCon</h1>
                    <p class="text-purple-100 text-sm md:text-base">نظام إدارة الموارد المتكامل للشركات الدوائية</p>
                </div>
                <div class="text-4xl md:text-6xl opacity-20 hidden sm:block">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <!-- Revenue Card -->
            <div class="card-responsive">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">الإيرادات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_revenue'], 0) }}</p>
                    <p class="text-xs text-green-600 flex items-center mt-1">
                        <i class="fas fa-arrow-up ml-1"></i>
                        {{ $stats['revenue_growth'] }}% من الشهر الماضي
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">العملاء</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_customers']) }}</p>
                    <p class="text-xs text-blue-600 flex items-center mt-1">
                        <i class="fas fa-arrow-up ml-1"></i>
                        {{ $stats['customers_growth'] }}% نمو
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Products Card -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">المنتجات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_products']) }}</p>
                    <p class="text-xs text-purple-600 flex items-center mt-1">
                        <i class="fas fa-box ml-1"></i>
                        منتج نشط
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-boxes text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Orders Card -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">الطلبات المعلقة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_orders']) }}</p>
                    <p class="text-xs text-orange-600 flex items-center mt-1">
                        <i class="fas fa-clock ml-1"></i>
                        تحتاج متابعة
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">اتجاه المبيعات</h3>
                <div class="flex space-x-2 space-x-reverse">
                    <button class="px-3 py-1 text-xs bg-purple-100 text-purple-600 rounded-full">30 يوم</button>
                    <button class="px-3 py-1 text-xs text-gray-500 hover:bg-gray-100 rounded-full">7 أيام</button>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">مخطط المبيعات</p>
                    <p class="text-sm text-gray-400">سيتم عرض البيانات هنا</p>
                </div>
            </div>
        </div>

        <!-- Inventory Status -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">حالة المخزون</h3>
                <a href="{{ route('inventory.index') }}" class="text-sm text-purple-600 hover:text-purple-800">عرض الكل</a>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full ml-3"></div>
                        <span class="text-sm text-gray-600">مخزون طبيعي</span>
                    </div>
                    <span class="text-sm font-medium">{{ $inventoryChart['normal_stock'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full ml-3"></div>
                        <span class="text-sm text-gray-600">مخزون منخفض</span>
                    </div>
                    <span class="text-sm font-medium">{{ $inventoryChart['low_stock'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full ml-3"></div>
                        <span class="text-sm text-gray-600">نفد المخزون</span>
                    </div>
                    <span class="text-sm font-medium">{{ $inventoryChart['out_of_stock'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full ml-3"></div>
                        <span class="text-sm text-gray-600">مخزون عالي</span>
                    </div>
                    <span class="text-sm font-medium">{{ $inventoryChart['high_stock'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أحدث الطلبات</h3>
                <a href="{{ route('sales.orders.index') }}" class="text-sm text-purple-600 hover:text-purple-800">عرض الكل</a>
            </div>
            <div class="space-y-3">
                @forelse($recentOrders as $order)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                        <p class="text-sm text-gray-600">{{ $order->customer->name }}</p>
                    </div>
                    <div class="text-left">
                        <p class="font-medium text-gray-900">{{ number_format($order->total_amount, 0) }} د.ع</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">لا توجد طلبات حديثة</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Alerts -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">التنبيهات</h3>
                <span class="text-sm text-gray-500">{{ count($alerts) }} تنبيه</span>
            </div>
            <div class="space-y-3">
                @forelse($alerts as $alert)
                <div class="flex items-start space-x-3 space-x-reverse p-3 rounded-lg {{ $alert['type'] === 'danger' ? 'bg-red-50' : ($alert['type'] === 'warning' ? 'bg-yellow-50' : 'bg-blue-50') }}">
                    <div class="flex-shrink-0">
                        <i class="fas {{ $alert['type'] === 'danger' ? 'fa-exclamation-triangle text-red-500' : ($alert['type'] === 'warning' ? 'fa-exclamation-circle text-yellow-500' : 'fa-info-circle text-blue-500') }}"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium {{ $alert['type'] === 'danger' ? 'text-red-800' : ($alert['type'] === 'warning' ? 'text-yellow-800' : 'text-blue-800') }}">{{ $alert['title'] }}</p>
                        <p class="text-xs {{ $alert['type'] === 'danger' ? 'text-red-600' : ($alert['type'] === 'warning' ? 'text-yellow-600' : 'text-blue-600') }}">{{ $alert['message'] }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-2"></i>
                    <p class="text-gray-500">لا توجد تنبيهات</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('sales.orders.create') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-plus-circle text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">طلب جديد</span>
            </a>
            <a href="{{ route('sales.customers.create') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-user-plus text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">عميل جديد</span>
            </a>
            <a href="{{ route('inventory.products.create') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-box text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-800">منتج جديد</span>
            </a>
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                <i class="fas fa-chart-bar text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">التقارير</span>
            </a>
        </div>
    </div>
</div>
@endsection
