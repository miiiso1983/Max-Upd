@extends('layouts.app')

@section('title', 'طلبات الشراء - MaxCon ERP')
@section('page-title', 'طلبات الشراء')

@push('styles')
<style>
/* Purchase Orders Page Hover Effects */
.purchase-order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.purchase-order-card:hover .order-number {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.purchase-order-card:hover .supplier-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.purchase-order-card:hover .total-amount {
    color: #6f42c1 !important;
    font-weight: bold;
    transition: all 0.3s ease;
}

.status-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.progress-bar:hover {
    background: #6f42c1 !important;
    transition: background 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(111, 66, 193, 0.2);
    transition: all 0.3s ease;
}

.stats-card:hover .stats-number {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.action-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.1);
    transition: all 0.3s ease;
}

.filter-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transition: all 0.3s ease;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">طلبات الشراء</h1>
                <p class="text-gray-600">إدارة ومتابعة طلبات الشراء من الموردين</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    طلب شراء جديد
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="stats-card bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">إجمالي الطلبات</p>
                        <p class="stats-number text-2xl font-bold text-blue-900">{{ $orders->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600">قيد الانتظار</p>
                        <p class="stats-number text-2xl font-bold text-yellow-900">
                            {{ $orders->where('status', 'pending')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">مؤكدة</p>
                        <p class="stats-number text-2xl font-bold text-green-900">
                            {{ $orders->where('status', 'confirmed')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">مستلمة جزئياً</p>
                        <p class="stats-number text-2xl font-bold text-purple-900">
                            {{ $orders->where('status', 'partially_received')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-orange-600">إجمالي القيمة</p>
                        <p class="stats-number text-2xl font-bold text-orange-900">
                            {{ number_format($orders->sum('total_amount'), 0) }} د.ع
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="رقم الطلب أو اسم المورد..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <x-searchable-dropdown
                    name="status"
                    placeholder="جميع الحالات"
                    search-placeholder="ابحث في الحالات..."
                    :options="collect($filters['statuses_ar'])->map(function($value, $key) {
                        return ['value' => $key, 'text' => $value];
                    })->prepend(['value' => '', 'text' => 'جميع الحالات'])->values()->toArray()"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المورد</label>
                <select id="supplier" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الموردين</option>
                    @foreach($filters['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name_ar ?? $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المستودع</label>
                <select id="warehouse" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع المستودعات</option>
                    @foreach($filters['warehouses'] as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name_ar ?? $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المتأخرة</label>
                <select id="overdue" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">الكل</option>
                    <option value="true">المتأخرة فقط</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Purchase Orders List -->
    <div class="bg-white rounded-lg card-shadow">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                @forelse($orders as $order)
                <div class="purchase-order-card bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 space-x-reverse mb-2">
                                <h3 class="order-number text-lg font-semibold text-gray-900">{{ $order->order_number }}</h3>
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($order->status)
                                        @case('draft') bg-gray-100 text-gray-800 @break
                                        @case('pending') bg-yellow-100 text-yellow-800 @break
                                        @case('confirmed') bg-blue-100 text-blue-800 @break
                                        @case('partially_received') bg-purple-100 text-purple-800 @break
                                        @case('received') bg-green-100 text-green-800 @break
                                        @case('cancelled') bg-red-100 text-red-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $filters['statuses_ar'][$order->status] ?? $order->status }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-3">
                                <div>
                                    <span class="font-medium">المورد:</span>
                                    <span class="supplier-name">{{ $order->supplier->name_ar ?? $order->supplier->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">تاريخ الطلب:</span>
                                    <span>{{ $order->order_date ? $order->order_date->format('Y/m/d') : $order->created_at->format('Y/m/d') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">التسليم المتوقع:</span>
                                    <span>{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('Y/m/d') : 'غير محدد' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">المبلغ الإجمالي:</span>
                                    <span class="total-amount font-semibold text-green-600">{{ number_format($order->total_amount, 0) }} د.ع</span>
                                </div>
                            </div>

                            <!-- Progress Bar for Receiving -->
                            @if($order->status === 'confirmed' || $order->status === 'partially_received')
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                                    <span>تقدم الاستلام:</span>
                                    <span>{{ $order->total_received ?? 0 }} / {{ $order->total_ordered ?? 0 }} قطعة</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="progress-bar bg-blue-600 h-2 rounded-full"
                                         style="width: {{ $order->receiving_progress ?? 0 }}%"></div>
                                </div>
                            </div>
                            @endif

                            @if($order->notes)
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">ملاحظات:</span>
                                <span>{{ Str::limit($order->notes, 100) }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 space-x-reverse">
                            <a href="{{ route('purchase-orders.show', $order) }}"
                               class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                               title="عرض التفاصيل">
                                <i class="fas fa-eye text-sm"></i>
                            </a>

                            <a href="{{ route('purchase-orders.print', $order) }}"
                               target="_blank"
                               class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-all duration-200"
                               title="طباعة">
                                <i class="fas fa-print text-sm"></i>
                            </a>

                            @if(in_array($order->status, ['draft', 'pending']))
                            <a href="{{ route('purchase-orders.edit', $order) }}" 
                               class="action-btn bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>
                            @endif

                            <div class="relative">
                                <button class="action-btn bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600" 
                                        onclick="toggleDropdown('dropdown-{{ $order->id }}')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-{{ $order->id }}" class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                    <div class="py-1">
                                        @if($order->status === 'pending')
                                        <a href="#" class="block px-4 py-2 text-sm text-green-600 hover:bg-gray-100">
                                            <i class="fas fa-check ml-2"></i>
                                            تأكيد الطلب
                                        </a>
                                        @endif

                                        @if(in_array($order->status, ['confirmed', 'partially_received']))
                                        <a href="#" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
                                            <i class="fas fa-truck ml-2"></i>
                                            استلام البضاعة
                                        </a>
                                        @endif

                                        <a href="{{ route('purchase-orders.print', $order) }}"
                                           target="_blank"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-print ml-2"></i>
                                            طباعة الطلب
                                        </a>

                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-download ml-2"></i>
                                            تحميل PDF
                                        </a>

                                        @if(in_array($order->status, ['draft', 'pending']))
                                        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-times ml-2"></i>
                                            إلغاء الطلب
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد طلبات شراء</h3>
                    <p class="text-gray-500 mb-4">لم يتم العثور على أي طلبات شراء</p>
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        إنشاء طلب شراء جديد
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('hidden');
    
    // Close other dropdowns
    document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
        if (el.id !== id) {
            el.classList.add('hidden');
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick*="toggleDropdown"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
            el.classList.add('hidden');
        });
    }
});

// Filter functionality
document.getElementById('search').addEventListener('input', function() {
    filterOrders();
});

document.getElementById('status').addEventListener('change', function() {
    filterOrders();
});

document.getElementById('supplier').addEventListener('change', function() {
    filterOrders();
});

document.getElementById('warehouse').addEventListener('change', function() {
    filterOrders();
});

document.getElementById('overdue').addEventListener('change', function() {
    filterOrders();
});

function filterOrders() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('status').value;
    const supplier = document.getElementById('supplier').value;
    const warehouse = document.getElementById('warehouse').value;
    const overdue = document.getElementById('overdue').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (supplier) params.append('supplier_id', supplier);
    if (warehouse) params.append('warehouse_id', warehouse);
    if (overdue) params.append('overdue', overdue);
    
    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
@endsection
