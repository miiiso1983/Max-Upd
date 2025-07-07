@extends('layouts.app')

@section('title', 'تفاصيل أمر البيع')
@section('page-title', 'تفاصيل أمر البيع')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">أمر البيع: {{ $order->order_number }}</h1>
            <p class="text-gray-600 mt-1">تفاصيل أمر البيع وحالته</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.orders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
            <a href="{{ route('sales.orders.print', $order) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </a>
            @if($order->status === 'draft' || $order->status === 'pending')
                <a href="{{ route('sales.orders.edit', $order) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
            @endif
        </div>
    </div>

    <!-- Order Status -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">حالة الأمر</h3>
                <p class="text-gray-600">آخر تحديث: {{ $order->updated_at->format('Y/m/d H:i') }}</p>
            </div>
            <div class="text-left">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @switch($order->status)
                        @case('draft') bg-gray-100 text-gray-800 @break
                        @case('confirmed') bg-blue-100 text-blue-800 @break
                        @case('processing') bg-yellow-100 text-yellow-800 @break
                        @case('shipped') bg-purple-100 text-purple-800 @break
                        @case('delivered') bg-green-100 text-green-800 @break
                        @case('cancelled') bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                    @switch($order->status)
                        @case('draft') مسودة @break
                        @case('confirmed') مؤكد @break
                        @case('processing') قيد المعالجة @break
                        @case('shipped') تم الشحن @break
                        @case('delivered') تم التسليم @break
                        @case('cancelled') ملغي @break
                        @default {{ $order->status }}
                    @endswitch
                </span>
            </div>
        </div>
    </div>

    <!-- Order Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Order Details -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الأمر</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">رقم الأمر:</span>
                    <span class="font-medium">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">تاريخ الأمر:</span>
                    <span class="font-medium">{{ $order->order_date->format('Y/m/d') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">تاريخ التسليم:</span>
                    <span class="font-medium">{{ $order->delivery_date ? $order->delivery_date->format('Y/m/d') : 'غير محدد' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">طريقة الدفع:</span>
                    <span class="font-medium">
                        @switch($order->payment_method)
                            @case('cash') نقداً @break
                            @case('credit') آجل @break
                            @case('card') بطاقة @break
                            @case('transfer') تحويل @break
                            @default {{ $order->payment_method }}
                        @endswitch
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">شروط الدفع:</span>
                    <span class="font-medium">{{ $order->payment_terms }} يوم</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">المستودع:</span>
                    <span class="font-medium">{{ $order->warehouse->name_ar ?: $order->warehouse->name }}</span>
                </div>
                @if($order->salesRep)
                <div class="flex justify-between">
                    <span class="text-gray-600">مندوب المبيعات:</span>
                    <span class="font-medium">{{ $order->salesRep->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات العميل</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">اسم العميل:</span>
                    <span class="font-medium">{{ $order->customer->name_ar ?: $order->customer->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">رمز العميل:</span>
                    <span class="font-medium">{{ $order->customer->code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">نوع العميل:</span>
                    <span class="font-medium">
                        @switch($order->customer->type)
                            @case('individual') فرد @break
                            @case('pharmacy') صيدلية @break
                            @case('clinic') عيادة @break
                            @case('hospital') مستشفى @break
                            @case('distributor') موزع @break
                            @case('government') حكومي @break
                            @default {{ $order->customer->type }}
                        @endswitch
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الهاتف:</span>
                    <span class="font-medium">{{ $order->customer->phone ?: $order->customer->mobile }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">البريد الإلكتروني:</span>
                    <span class="font-medium">{{ $order->customer->email ?: 'غير محدد' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">العنوان:</span>
                    <span class="font-medium">{{ $order->customer->address }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">المدينة:</span>
                    <span class="font-medium">{{ $order->customer->city }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">عناصر الأمر</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">سعر الوحدة</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">الخصم</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">المجموع</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">المخزون</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($order->items as $index => $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                            @if($item->product->name_ar)
                                <div class="text-sm text-gray-500">{{ $item->product->name_ar }}</div>
                            @endif
                            <div class="text-xs text-gray-400">{{ $item->product->sku }}</div>
                            @if($item->batch_number)
                                <div class="text-xs text-blue-600">دفعة: {{ $item->batch_number }}</div>
                            @endif
                            @if($item->expiry_date)
                                <div class="text-xs text-orange-600">انتهاء: {{ $item->expiry_date->format('Y/m/d') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ number_format($item->quantity) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ number_format($item->unit_price, 2) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            @if($item->discount_percentage > 0)
                                {{ $item->discount_percentage }}%<br>
                                <span class="text-xs text-gray-500">({{ number_format($item->discount_amount, 2) }} د.ع)</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                            {{ number_format($item->total_amount, 2) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            <span class="text-green-600 font-medium">{{ number_format($item->available_stock) }}</span>
                            <div class="text-xs text-gray-500">متاح</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Totals -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">مجاميع الأمر</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">المجموع الفرعي:</span>
                    <span class="font-medium">{{ number_format($order->subtotal, 2) }} د.ع</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الخصم:</span>
                    <span class="font-medium">{{ number_format($order->discount_amount, 2) }} د.ع</span>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">الضريبة:</span>
                    <span class="font-medium">{{ number_format($order->tax_amount, 2) }} د.ع</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الشحن:</span>
                    <span class="font-medium">{{ number_format($order->shipping_amount, 2) }} د.ع</span>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-200 mt-4 pt-4">
            <div class="flex justify-between text-lg font-bold">
                <span>المجموع الإجمالي:</span>
                <span class="text-blue-600">{{ number_format($order->total_amount, 2) }} د.ع</span>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($order->notes || $order->internal_notes)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">ملاحظات</h3>
        @if($order->notes)
            <div class="mb-4">
                <h4 class="font-medium text-gray-700 mb-2">ملاحظات عامة:</h4>
                <p class="text-gray-600 bg-gray-50 p-3 rounded">{{ $order->notes }}</p>
            </div>
        @endif
        @if($order->internal_notes)
            <div>
                <h4 class="font-medium text-gray-700 mb-2">ملاحظات داخلية:</h4>
                <div class="text-gray-600 bg-yellow-50 p-3 rounded whitespace-pre-line">{{ $order->internal_notes }}</div>
            </div>
        @endif
    </div>
    @endif

    <!-- Action Buttons -->
    @if($order->status !== 'cancelled' && $order->status !== 'delivered')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات</h3>
        <div class="flex flex-wrap gap-3">
            @if($order->status === 'draft')
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-check ml-2"></i>
                    تأكيد الأمر
                </button>
            @endif
            
            @if($order->status === 'confirmed')
                <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-cogs ml-2"></i>
                    بدء المعالجة
                </button>
            @endif
            
            @if($order->status === 'processing')
                <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-shipping-fast ml-2"></i>
                    شحن الأمر
                </button>
            @endif
            
            @if($order->status === 'shipped')
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-check-circle ml-2"></i>
                    تأكيد التسليم
                </button>
            @endif
            
            @if(in_array($order->status, ['draft', 'confirmed', 'processing']))
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-times ml-2"></i>
                    إلغاء الأمر
                </button>
            @endif
            
            @if($order->status === 'confirmed' && !$order->invoice)
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-file-invoice ml-2"></i>
                    إنشاء فاتورة
                </button>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
