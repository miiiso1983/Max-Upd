@extends('layouts.app')

@section('title', 'طلبات المبيعات - MaxCon ERP')
@section('page-title', 'طلبات المبيعات')

@push('styles')
<style>
/* Orders Page Hover Effects */
.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
    transition: all 0.3s ease;
}

.order-card:hover .order-number {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.order-card:hover .customer-name {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.status-badge:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.filter-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: #6f42c1 !important;
    color: white !important;
    transform: scale(1.1);
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
                <h1 class="text-2xl font-bold text-gray-900">طلبات المبيعات</h1>
                <p class="text-gray-600">إدارة ومتابعة طلبات المبيعات</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('sales.orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus ml-2"></i>
                    طلب جديد
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" id="search" placeholder="رقم الطلب أو اسم العميل..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع الحالات</option>
                    @foreach($filters['statuses_ar'] as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">العميل</label>
                <select id="customer" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">جميع العملاء</option>
                    @foreach($filters['customers'] as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name_ar ?? $customer->name }}</option>
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
        </div>
    </div>

    <!-- Orders List -->
    <div class="bg-white rounded-lg card-shadow">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4">
                @forelse($orders as $order)
                <div class="order-card bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 space-x-reverse mb-2">
                                <h3 class="order-number text-lg font-semibold text-gray-900">{{ $order->order_number }}</h3>
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-medium
                                    @switch($order->status)
                                        @case('draft') bg-gray-100 text-gray-800 @break
                                        @case('pending') bg-yellow-100 text-yellow-800 @break
                                        @case('confirmed') bg-blue-100 text-blue-800 @break
                                        @case('processing') bg-purple-100 text-purple-800 @break
                                        @case('shipped') bg-indigo-100 text-indigo-800 @break
                                        @case('delivered') bg-green-100 text-green-800 @break
                                        @case('cancelled') bg-red-100 text-red-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ $filters['statuses_ar'][$order->status] ?? $order->status }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">العميل:</span>
                                    <span class="customer-name">{{ $order->customer->name_ar ?? $order->customer->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">تاريخ الطلب:</span>
                                    <span>{{ $order->order_date->format('Y/m/d') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">المبلغ الإجمالي:</span>
                                    <span class="font-semibold text-green-600">{{ number_format($order->total_amount, 0) }} د.ع</span>
                                </div>
                            </div>

                            @if($order->delivery_date)
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="font-medium">تاريخ التسليم:</span>
                                <span>{{ $order->delivery_date->format('Y/m/d') }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 space-x-reverse">
                            <a href="{{ route('sales.orders.show', $order) }}" 
                               class="action-btn bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600">
                                <i class="fas fa-eye"></i>
                                عرض
                            </a>
                            
                            @if($order->status === 'draft' || $order->status === 'pending')
                            <a href="{{ route('sales.orders.edit', $order) }}" 
                               class="action-btn bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </a>
                            @endif

                            @if($order->status === 'confirmed' && !$order->invoices()->exists())
                            <a href="{{ route('sales.orders.invoice', $order) }}" 
                               class="action-btn bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600">
                                <i class="fas fa-file-invoice"></i>
                                فاتورة
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
                                        <form method="POST" action="{{ route('sales.orders.confirm', $order) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="block w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <i class="fas fa-check ml-2"></i>
                                                تأكيد الطلب
                                            </button>
                                        </form>
                                        @endif

                                        @if(in_array($order->status, ['draft', 'pending']))
                                        <form method="POST" action="{{ route('sales.orders.cancel', $order) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="block w-full text-right px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                                    onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟')">
                                                <i class="fas fa-times ml-2"></i>
                                                إلغاء الطلب
                                            </button>
                                        </form>
                                        @endif

                                        <a href="{{ route('sales.orders.print', $order) }}" target="_blank"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-print ml-2"></i>
                                            طباعة
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد طلبات</h3>
                    <p class="text-gray-500 mb-4">لم يتم العثور على أي طلبات مبيعات</p>
                    <a href="{{ route('sales.orders.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus ml-2"></i>
                        إنشاء طلب جديد
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

document.getElementById('customer').addEventListener('change', function() {
    filterOrders();
});

document.getElementById('warehouse').addEventListener('change', function() {
    filterOrders();
});

function filterOrders() {
    const search = document.getElementById('search').value;
    const status = document.getElementById('status').value;
    const customer = document.getElementById('customer').value;
    const warehouse = document.getElementById('warehouse').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (customer) params.append('customer_id', customer);
    if (warehouse) params.append('warehouse_id', warehouse);
    
    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
@endsection
