@extends('layouts.app')

@section('title', 'تفاصيل طلب الشراء - MaxCon ERP')
@section('page-title', 'تفاصيل طلب الشراء')

@push('styles')
<style>
.card-shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}
.card-shadow:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease-in-out;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">طلب الشراء {{ $purchaseOrder->order_number }}</h1>
            <p class="text-gray-600">تفاصيل طلب الشراء من {{ $purchaseOrder->supplier->name }}</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            @if($purchaseOrder->canBeEdited())
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
            @endif

            @if($purchaseOrder->status === 'approved' || $purchaseOrder->status === 'ordered')
                <button data-order-id="{{ $purchaseOrder->id }}"
                        onclick="receiveItems(this.dataset.orderId)"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-truck ml-2"></i>
                    استلام أصناف
                </button>
            @endif

            <button data-order-id="{{ $purchaseOrder->id }}"
                    onclick="printOrder(this.dataset.orderId)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </button>

            <a href="{{ route('purchase-orders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Status and Progress -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Status Card -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">حالة الطلب</p>
                    <p class="text-lg font-bold">
                        @switch($purchaseOrder->status)
                            @case('pending')
                                <span class="text-yellow-600">في الانتظار</span>
                                @break
                            @case('approved')
                                <span class="text-blue-600">معتمد</span>
                                @break
                            @case('ordered')
                                <span class="text-purple-600">مطلوب</span>
                                @break
                            @case('partially_received')
                                <span class="text-orange-600">مستلم جزئياً</span>
                                @break
                            @case('completed')
                                <span class="text-green-600">مكتمل</span>
                                @break
                            @case('cancelled')
                                <span class="text-red-600">ملغي</span>
                                @break
                            @default
                                <span class="text-gray-600">{{ $purchaseOrder->status }}</span>
                        @endswitch
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Amount Card -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المبلغ</p>
                    <p class="text-lg font-bold text-green-600">{{ number_format($purchaseOrder->total_amount, 2) }} د.ع</p>
                </div>
            </div>
        </div>

        <!-- Progress Card -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-pie text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">نسبة الاستلام</p>
                    <p class="text-lg font-bold text-purple-600">{{ number_format($purchaseOrder->receiving_progress, 1) }}%</p>
                </div>
            </div>
        </div>

        <!-- Items Count Card -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-boxes text-orange-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">عدد الأصناف</p>
                    <p class="text-lg font-bold text-orange-600">{{ $purchaseOrder->items->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات الطلب</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الطلب</label>
                            <p class="text-sm text-gray-900 font-mono">{{ $purchaseOrder->order_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الطلب</label>
                            <p class="text-sm text-gray-900">{{ $purchaseOrder->order_date->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التسليم المتوقع</label>
                            <p class="text-sm text-gray-900">
                                {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('Y-m-d') : 'غير محدد' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التسليم الفعلي</label>
                            <p class="text-sm text-gray-900">
                                {{ $purchaseOrder->delivered_date ? $purchaseOrder->delivered_date->format('Y-m-d') : 'لم يتم التسليم بعد' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
                            <p class="text-sm text-gray-900">
                                @switch($purchaseOrder->payment_method)
                                    @case('cash')
                                        نقداً
                                        @break
                                    @case('credit')
                                        آجل
                                        @break
                                    @case('bank_transfer')
                                        تحويل بنكي
                                        @break
                                    @case('check')
                                        شيك
                                        @break
                                    @default
                                        {{ $purchaseOrder->payment_method }}
                                @endswitch
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">شروط الدفع</label>
                            <p class="text-sm text-gray-900">{{ $purchaseOrder->payment_terms ?? 0 }} يوم</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">أصناف الطلب</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المطلوبة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المستلمة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">سعر الوحدة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجمالي</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchaseOrder->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->product->name_ar }}</div>
                                            <div class="text-xs text-gray-400">SKU: {{ $item->product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($item->quantity) }} {{ $item->product->unit_of_measure }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($item->total_received) }} {{ $item->product->unit_of_measure }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($item->unit_cost, 2) }} د.ع
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($item->total_amount, 2) }} د.ع
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->is_fully_received)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            مستلم بالكامل
                                        </span>
                                    @elseif($item->total_received > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            مستلم جزئياً ({{ number_format($item->receiving_progress, 1) }}%)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            لم يستلم بعد
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Received Items (if any) -->
            @if($purchaseOrder->receivedItems && $purchaseOrder->receivedItems->count() > 0)
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">الأصناف المستلمة</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المستلمة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الدفعة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الاستلام</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الجودة</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchaseOrder->receivedItems as $receivedItem)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $receivedItem->product->name ?? 'منتج محذوف' }}</div>
                                    @if($receivedItem->product)
                                        <div class="text-sm text-gray-500">{{ $receivedItem->product->name_ar }}</div>
                                        <div class="text-xs text-gray-400">SKU: {{ $receivedItem->product->sku }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($receivedItem->received_quantity) }}
                                    @if($receivedItem->product)
                                        {{ $receivedItem->product->unit_of_measure }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                    {{ $receivedItem->batch_number ?? 'غير محدد' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $receivedItem->expiry_date ? $receivedItem->expiry_date->format('Y-m-d') : 'غير محدد' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $receivedItem->received_date->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($receivedItem->quality_check_status)
                                        @case('passed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                مقبول
                                            </span>
                                            @break
                                        @case('failed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                مرفوض
                                            </span>
                                            @break
                                        @case('pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                في الانتظار
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $receivedItem->quality_check_status }}
                                            </span>
                                    @endswitch
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Financial Summary -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">الملخص المالي</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">المجموع الفرعي:</span>
                            <span class="text-sm font-medium">{{ number_format($purchaseOrder->subtotal, 2) }} د.ع</span>
                        </div>
                        @if($purchaseOrder->discount_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">الخصم:</span>
                            <span class="text-sm font-medium text-red-600">-{{ number_format($purchaseOrder->discount_amount, 2) }} د.ع</span>
                        </div>
                        @endif
                        @if($purchaseOrder->tax_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">الضريبة:</span>
                            <span class="text-sm font-medium">{{ number_format($purchaseOrder->tax_amount, 2) }} د.ع</span>
                        </div>
                        @endif
                        @if($purchaseOrder->shipping_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">الشحن:</span>
                            <span class="text-sm font-medium">{{ number_format($purchaseOrder->shipping_amount, 2) }} د.ع</span>
                        </div>
                        @endif
                        <div class="border-t pt-3">
                            <div class="flex justify-between">
                                <span class="text-base font-semibold text-gray-900">الإجمالي:</span>
                                <span class="text-base font-bold text-green-600">{{ number_format($purchaseOrder->total_amount, 2) }} د.ع</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Supplier Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات المورد</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اسم المورد</label>
                        <p class="text-sm text-gray-900">{{ $purchaseOrder->supplier->name }}</p>
                        @if($purchaseOrder->supplier->name_ar)
                            <p class="text-sm text-gray-600">{{ $purchaseOrder->supplier->name_ar }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع المورد</label>
                        <p class="text-sm text-gray-900">
                            @switch($purchaseOrder->supplier->type)
                                @case('manufacturer')
                                    مصنع
                                    @break
                                @case('distributor')
                                    موزع
                                    @break
                                @case('wholesaler')
                                    تاجر جملة
                                    @break
                                @default
                                    {{ $purchaseOrder->supplier->type }}
                            @endswitch
                        </p>
                    </div>
                    @if($purchaseOrder->supplier->email)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                        <p class="text-sm text-gray-900">
                            <a href="mailto:{{ $purchaseOrder->supplier->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $purchaseOrder->supplier->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    @if($purchaseOrder->supplier->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
                        <p class="text-sm text-gray-900">
                            <a href="tel:{{ $purchaseOrder->supplier->phone }}" class="text-blue-600 hover:text-blue-800">
                                {{ $purchaseOrder->supplier->phone }}
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Warehouse Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات المستودع</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اسم المستودع</label>
                        <p class="text-sm text-gray-900">{{ $purchaseOrder->warehouse->name }}</p>
                        @if($purchaseOrder->warehouse->name_ar)
                            <p class="text-sm text-gray-600">{{ $purchaseOrder->warehouse->name_ar }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">كود المستودع</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $purchaseOrder->warehouse->code }}</p>
                    </div>
                    @if($purchaseOrder->warehouse->address)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                        <p class="text-sm text-gray-900">{{ $purchaseOrder->warehouse->address }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($purchaseOrder->notes)
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">ملاحظات</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-900">{{ $purchaseOrder->notes }}</p>
                </div>
            </div>
            @endif

            <!-- System Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات النظام</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تم الإنشاء بواسطة</label>
                        <p class="text-sm text-gray-900">{{ $purchaseOrder->creator->name ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإنشاء</label>
                        <p class="text-sm text-gray-900">{{ $purchaseOrder->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">آخر تحديث</label>
                        <p class="text-sm text-gray-900">{{ $purchaseOrder->updated_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function receiveItems(orderId) {
    // Redirect to receiving page or open modal
    window.location.href = `/purchase-orders/${orderId}/receive`;
}

function printOrder(orderId) {
    // Open print view in new window
    const printUrl = '{{ route("purchase-orders.print", ":id") }}'.replace(':id', orderId);
    window.open(printUrl, '_blank');
}

// Add any additional JavaScript functionality here
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any tooltips or interactive elements
    console.log('Purchase Order Details page loaded');
});
</script>
@endpush
