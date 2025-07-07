@extends('layouts.app')

@section('title', 'لوحة تحكم المبيعات - MaxCon ERP')
@section('page-title', 'لوحة تحكم المبيعات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">لوحة تحكم المبيعات</h1>
                <p class="text-blue-100">تتبع الأداء والإحصائيات المالية</p>
            </div>
            <div class="flex items-center space-x-4 space-x-reverse">
                <select id="period-selector" class="bg-white/20 border border-white/30 text-white rounded-lg px-3 py-2 text-sm">
                    <option value="day" {{ $period === 'day' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>هذا الأسبوع</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>هذا الشهر</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>هذا العام</option>
                </select>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المبيعات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_sales'], 0) }}</p>
                    <p class="text-xs text-green-600 flex items-center mt-1">
                        <i class="fas fa-dollar-sign ml-1"></i>
                        دينار عراقي
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي الطلبات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                    <p class="text-xs text-blue-600 flex items-center mt-1">
                        <i class="fas fa-shopping-cart ml-1"></i>
                        طلب
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Invoices -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">الفواتير المعلقة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_invoices'] }}</p>
                    <p class="text-xs text-orange-600 flex items-center mt-1">
                        <i class="fas fa-clock ml-1"></i>
                        في الانتظار
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="bg-white rounded-lg p-6 card-shadow hover-scale">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">متوسط قيمة الطلب</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_order_value'], 0) }}</p>
                    <p class="text-xs text-purple-600 flex items-center mt-1">
                        <i class="fas fa-chart-bar ml-1"></i>
                        دينار عراقي
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Data -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أحدث الطلبات</h3>
                <a href="{{ route('sales.orders.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    عرض الكل
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentOrders as $order)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                            <i class="fas fa-shopping-cart text-blue-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">{{ $order->customer->name ?? 'عميل غير محدد' }}</p>
                            <p class="text-xs text-gray-500">{{ $order->order_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-medium text-gray-900">{{ number_format($order->total_amount, 0) }} د.ع</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $order->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $order->status === 'confirmed' ? 'مؤكد' : 'معلق' }}
                        </span>
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

        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أحدث الفواتير</h3>
                <a href="{{ route('sales.invoices.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    عرض الكل
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentInvoices as $invoice)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center ml-3">
                            <i class="fas fa-file-invoice text-green-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">#{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-gray-500">{{ $invoice->customer->name ?? 'عميل غير محدد' }}</p>
                            <p class="text-xs text-gray-500">{{ $invoice->invoice_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-medium text-gray-900">{{ number_format($invoice->total_amount, 0) }} د.ع</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            @switch($invoice->status)
                                @case('paid')
                                    مدفوعة
                                    @break
                                @case('pending')
                                    معلقة
                                    @break
                                @case('overdue')
                                    متأخرة
                                    @break
                                @default
                                    {{ $invoice->status }}
                            @endswitch
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-file-invoice text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">لا توجد فواتير حديثة</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">أفضل العملاء</h3>
            <a href="{{ route('sales.customers.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                عرض الكل
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($topCustomers as $customer)
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                    <p class="text-xs text-gray-500">{{ $customer->type }}</p>
                    <p class="text-sm font-bold text-green-600">{{ number_format($customer->total_sales, 0) }} د.ع</p>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-8">
                <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">لا توجد بيانات عملاء</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('sales.orders.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-plus text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">طلب جديد</p>
                    <p class="text-xs text-gray-600">إنشاء طلب مبيعات</p>
                </div>
            </a>
            
            <a href="{{ route('sales.invoices.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-file-invoice text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">فاتورة جديدة</p>
                    <p class="text-xs text-gray-600">إنشاء فاتورة مبيعات</p>
                </div>
            </a>
            
            <a href="{{ route('sales.customers.create') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-user-plus text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">عميل جديد</p>
                    <p class="text-xs text-gray-600">إضافة عميل جديد</p>
                </div>
            </a>
            
            <a href="{{ route('sales.payments.create') }}" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-credit-card text-orange-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">دفعة جديدة</p>
                    <p class="text-xs text-gray-600">تسجيل دفعة</p>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('period-selector').addEventListener('change', function() {
    const period = this.value;
    window.location.href = `{{ route('sales.dashboard') }}?period=${period}`;
});

// Auto-refresh dashboard every 5 minutes
setInterval(() => {
    // You can implement auto-refresh logic here
}, 300000);
</script>
@endsection
