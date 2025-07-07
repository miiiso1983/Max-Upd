@extends('layouts.app')

@section('title', 'المخزون - MaxCon ERP')
@section('page-title', 'إدارة المخزون')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">إدارة المخزون</h1>
                <p class="text-green-100">تتبع وإدارة المنتجات والمخزون والحركات</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">المخازن النشطة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_warehouses'] ?? 0 }}</p>
                    <p class="text-xs text-green-600 flex items-center mt-1">
                        <i class="fas fa-warehouse ml-1"></i>
                        مخزن نشط
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-warehouse text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المنتجات</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_products'] ?? 0 }}</p>
                    <p class="text-xs text-blue-600 flex items-center mt-1">
                        <i class="fas fa-box ml-1"></i>
                        منتج نشط
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-boxes text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">قيمة المخزون</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_inventory_value'] ?? 0, 0) }}</p>
                    <p class="text-xs text-purple-600 flex items-center mt-1">
                        <i class="fas fa-dollar-sign ml-1"></i>
                        دينار عراقي
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">مخزون منخفض</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['low_stock_items'] ?? 0 }}</p>
                    <p class="text-xs text-orange-600 flex items-center mt-1">
                        <i class="fas fa-exclamation-triangle ml-1"></i>
                        يحتاج تموين
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">منتهي الصلاحية</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['expiring_items'] ?? 0 }}</p>
                    <p class="text-xs text-red-600 flex items-center mt-1">
                        <i class="fas fa-clock ml-1"></i>
                        خلال 30 يوم
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Multi-Warehouse Management Section -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">إدارة المخازن المتعددة</h3>
            <a href="{{ route('inventory.warehouses.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-warehouse ml-2"></i>
                مخزن جديد
            </a>
        </div>

        <!-- Warehouse Utilization Chart -->
        @if(isset($warehouseUtilization) && $warehouseUtilization->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($warehouseUtilization as $warehouse)
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">{{ $warehouse->name_ar ?: $warehouse->name }}</h4>
                    <span class="text-sm text-gray-500">{{ number_format($warehouse->utilization_percentage, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    @php
                        $utilizationWidth = min($warehouse->utilization_percentage ?? 0, 100);
                    @endphp
                    <div class="bg-green-600 h-2 rounded-full warehouse-utilization" data-width="{{ $utilizationWidth }}"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>{{ number_format($warehouse->current_utilization, 2) }}</span>
                    <span>{{ number_format($warehouse->capacity, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Warehouse Quick Actions -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="{{ route('inventory.warehouses.index') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors hover-scale">
                <i class="fas fa-warehouse text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-800">إدارة المخازن</span>
            </a>
            <a href="{{ route('inventory.by-warehouse') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors hover-scale">
                <i class="fas fa-exchange-alt text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">نقل بين المخازن</span>
            </a>
            <a href="{{ route('inventory.by-product') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors hover-scale">
                <i class="fas fa-clipboard-list text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">جرد المخزون</span>
            </a>
            <a href="{{ route('inventory.movements') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors hover-scale">
                <i class="fas fa-chart-bar text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">تقارير المخازن</span>
            </a>
            <a href="{{ route('inventory.movements') }}" class="flex flex-col items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors hover-scale">
                <i class="fas fa-history text-2xl text-red-600 mb-2"></i>
                <span class="text-sm font-medium text-red-800">حركات المخزون</span>
            </a>
            <a href="{{ route('inventory.warehouses.index') }}" class="flex flex-col items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors hover-scale">
                <i class="fas fa-cogs text-2xl text-indigo-600 mb-2"></i>
                <span class="text-sm font-medium text-indigo-800">إعدادات المخازن</span>
            </a>
        </div>
    </div>

    <!-- Alerts and Quick Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">تنبيهات المخزون المنخفض</h3>
                <a href="{{ route('inventory.low-stock') }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium">عرض الكل</a>
            </div>
            @if(isset($lowStockItems) && $lowStockItems->count() > 0)
                <div class="space-y-3">
                    @foreach($lowStockItems->take(5) as $item)
                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->product->name_ar ?: $item->product->name }}</p>
                            <p class="text-sm text-gray-500">{{ $item->warehouse->name_ar ?: $item->warehouse->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-orange-600">{{ $item->quantity }}</p>
                            <p class="text-xs text-gray-500">الحد الأدنى: {{ $item->product->min_stock_level }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                    <p class="text-gray-500">لا توجد تنبيهات مخزون منخفض</p>
                </div>
            @endif
        </div>

        <!-- Expiring Items Alerts -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">منتجات قاربت على الانتهاء</h3>
                <a href="{{ route('inventory.expiring') }}" class="text-red-600 hover:text-red-700 text-sm font-medium">عرض الكل</a>
            </div>
            @if(isset($expiringItems) && $expiringItems->count() > 0)
                <div class="space-y-3">
                    @foreach($expiringItems->take(5) as $item)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->product->name_ar ?: $item->product->name }}</p>
                            <p class="text-sm text-gray-500">{{ $item->warehouse->name_ar ?: $item->warehouse->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-red-600">{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : 'غير محدد' }}</p>
                            <p class="text-xs text-gray-500">الكمية: {{ $item->quantity }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                    <p class="text-gray-500">لا توجد منتجات قاربت على الانتهاء</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('inventory.products.create') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors hover-scale">
                <i class="fas fa-plus-circle text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">منتج جديد</span>
            </a>
            <a href="{{ route('inventory.products.low-stock') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors hover-scale">
                <i class="fas fa-exclamation-triangle text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">مخزون منخفض</span>
            </a>
            <a href="{{ route('inventory.products.expiring') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors hover-scale">
                <i class="fas fa-calendar-times text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">منتهية الصلاحية</span>
            </a>
            <a href="{{ route('reports.inventory.stock-levels') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors hover-scale">
                <i class="fas fa-chart-bar text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-800">تقارير المخزون</span>
            </a>
        </div>
    </div>

    <!-- Inventory Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Stock Movements -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أحدث حركات المخزون</h3>
                <a href="{{ route('inventory.movements') }}" class="text-sm text-blue-600 hover:text-blue-800">عرض الكل</a>
            </div>
            @if(isset($recentMovements) && $recentMovements->count() > 0)
                <div class="space-y-3">
                    @foreach($recentMovements as $movement)
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3
                                @if($movement->type === 'in') bg-green-100 text-green-600
                                @elseif($movement->type === 'out') bg-red-100 text-red-600
                                @elseif($movement->type === 'transfer') bg-blue-100 text-blue-600
                                @else bg-gray-100 text-gray-600 @endif">
                                <i class="fas
                                    @if($movement->type === 'in') fa-arrow-down
                                    @elseif($movement->type === 'out') fa-arrow-up
                                    @elseif($movement->type === 'transfer') fa-exchange-alt
                                    @else fa-edit @endif text-xs"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $movement->product->name_ar ?: $movement->product->name }}</p>
                                <p class="text-sm text-gray-500">{{ $movement->warehouse->name_ar ?: $movement->warehouse->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium
                                @if($movement->type === 'in') text-green-600
                                @elseif($movement->type === 'out') text-red-600
                                @else text-blue-600 @endif">
                                {{ $movement->type === 'out' ? '-' : '+' }}{{ $movement->quantity }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $movement->movement_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-history text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">لا توجد حركات مخزون حديثة</p>
                </div>
            @endif
        </div>

        <!-- Top Products by Value -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">أعلى المنتجات قيمة</h3>
                <a href="{{ route('inventory.by-product') }}" class="text-sm text-blue-600 hover:text-blue-800">عرض الكل</a>
            </div>
            @if(isset($topProductsByValue) && $topProductsByValue->count() > 0)
                <div class="space-y-3">
                    @foreach($topProductsByValue as $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->product->name_ar ?: $item->product->name }}</p>
                            <p class="text-sm text-gray-500">{{ $item->product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-green-600">{{ number_format($item->total_value, 0) }} د.ع</p>
                            <p class="text-xs text-gray-500">إجمالي القيمة</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-chart-bar text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">لا توجد بيانات متاحة</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Inventory Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock Status Chart -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">حالة المخزون</h3>
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-pie text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">مخطط حالة المخزون</p>
                    <p class="text-sm text-gray-400">سيتم عرض البيانات قريباً</p>
                </div>
            </div>
        </div>

        <!-- Stock Value Chart -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">قيمة المخزون</h3>
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-bar text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">مخطط قيمة المخزون</p>
                    <p class="text-sm text-gray-400">سيتم عرض البيانات قريباً</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Products Alert -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">منتجات قاربت على انتهاء الصلاحية</h3>
            <a href="{{ route('inventory.products.expiring') }}" class="text-sm text-blue-600 hover:text-blue-800">عرض الكل</a>
        </div>
        <div class="space-y-3" id="expiring-products-list">
            <!-- Expiring products will be loaded here -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>
    </div>

    <!-- Warehouse Summary -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">ملخص المستودعات</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="warehouse-summary">
            <!-- Warehouse data will be loaded here -->
            <div class="text-center py-8 md:col-span-3">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">جاري التحميل...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadInventoryData();
});

function loadInventoryData() {
    // Load inventory dashboard data
    fetch('/api/inventory/dashboard')
        .then(response => response.json())
        .then(data => {
            updateStats(data.stats);
            updateRecentMovements(data.recent_movements);
            updateLowStockProducts(data.low_stock_products);
            updateExpiringProducts(data.expiring_products);
        })
        .catch(error => {
            console.error('Error loading inventory data:', error);
        });
}

function updateStats(stats) {
    document.getElementById('total-products').textContent = stats.total_products || 0;
    document.getElementById('low-stock').textContent = stats.low_stock_products || 0;
    document.getElementById('out-of-stock').textContent = stats.out_of_stock_products || 0;
    document.getElementById('expiring-products').textContent = stats.expiring_products || 0;
}

function updateRecentMovements(movements) {
    const container = document.getElementById('recent-movements');
    if (movements && movements.length > 0) {
        container.innerHTML = movements.map(movement => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 ${getMovementTypeClass(movement.movement_type)} rounded-lg flex items-center justify-center ml-3">
                        <i class="fas ${getMovementTypeIcon(movement.movement_type)} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">${movement.product.name}</p>
                        <p class="text-sm text-gray-600">${getMovementTypeText(movement.movement_type)}</p>
                    </div>
                </div>
                <div class="text-left">
                    <p class="font-medium text-gray-900">${movement.quantity}</p>
                    <p class="text-xs text-gray-500">${formatDate(movement.movement_date)}</p>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exchange-alt text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">لا توجد حركات حديثة</p>
            </div>
        `;
    }
}

function updateLowStockProducts(products) {
    const container = document.getElementById('low-stock-products');
    if (products && products.length > 0) {
        container.innerHTML = products.map(product => `
            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">${product.name}</p>
                    <p class="text-sm text-gray-600">${product.sku}</p>
                </div>
                <div class="text-left">
                    <p class="font-medium text-orange-600">${product.current_stock}</p>
                    <p class="text-xs text-gray-500">الحد الأدنى: ${product.minimum_stock}</p>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-300 mb-2"></i>
                <p class="text-gray-500">جميع المنتجات بمخزون كافي</p>
            </div>
        `;
    }
}

function updateExpiringProducts(products) {
    const container = document.getElementById('expiring-products-list');
    if (products && products.length > 0) {
        container.innerHTML = products.map(product => `
            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">${product.name}</p>
                    <p class="text-sm text-gray-600">${product.sku}</p>
                </div>
                <div class="text-left">
                    <p class="font-medium text-purple-600">${formatDate(product.expiry_date)}</p>
                    <p class="text-xs text-gray-500">${getDaysUntilExpiry(product.expiry_date)} يوم متبقي</p>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-300 mb-2"></i>
                <p class="text-gray-500">لا توجد منتجات قاربت على انتهاء الصلاحية</p>
            </div>
        `;
    }
}

function getMovementTypeClass(type) {
    switch(type) {
        case 'in': return 'bg-green-100 text-green-600';
        case 'out': return 'bg-red-100 text-red-600';
        case 'adjustment': return 'bg-blue-100 text-blue-600';
        default: return 'bg-gray-100 text-gray-600';
    }
}

function getMovementTypeIcon(type) {
    switch(type) {
        case 'in': return 'fa-arrow-down';
        case 'out': return 'fa-arrow-up';
        case 'adjustment': return 'fa-edit';
        default: return 'fa-exchange-alt';
    }
}

function getMovementTypeText(type) {
    switch(type) {
        case 'in': return 'إدخال';
        case 'out': return 'إخراج';
        case 'adjustment': return 'تعديل';
        default: return type;
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('ar-IQ');
}

function getDaysUntilExpiry(expiryDate) {
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diffTime = expiry - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

// Set warehouse utilization widths
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.warehouse-utilization').forEach(function(element) {
        const width = element.dataset.width;
        element.style.width = width + '%';
    });
});
</script>
@endpush
