@extends('layouts.app')

@section('title', 'تفاصيل الدفعة')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تفاصيل الدفعة</h1>
            <p class="text-gray-600 mt-1">{{ $payment->payment_number }}</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.payments.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
            @if($payment->status === 'pending')
                <a href="{{ route('sales.payments.edit', $payment) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
            @endif
            <button onclick="window.print()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Payment Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات الدفعة</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الدفعة</label>
                            <p class="text-gray-900 font-mono">{{ $payment->payment_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $payment->type === 'receipt' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $payment->type === 'receipt' ? 'إيصال استلام' : 'دفعة صادرة' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ</label>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($payment->amount) }} {{ $payment->currency }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @switch($payment->status)
                                    @case('completed')
                                        bg-green-100 text-green-800
                                        @break
                                    @case('pending')
                                        bg-yellow-100 text-yellow-800
                                        @break
                                    @case('cancelled')
                                        bg-red-100 text-red-800
                                        @break
                                    @default
                                        bg-gray-100 text-gray-800
                                @endswitch">
                                @switch($payment->status)
                                    @case('completed')
                                        مكتملة
                                        @break
                                    @case('pending')
                                        معلقة
                                        @break
                                    @case('cancelled')
                                        ملغية
                                        @break
                                    @default
                                        {{ $payment->status }}
                                @endswitch
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
                            <p class="text-gray-900">
                                @switch($payment->payment_method)
                                    @case('cash')
                                        نقدي
                                        @break
                                    @case('bank_transfer')
                                        تحويل مصرفي
                                        @break
                                    @case('check')
                                        شيك
                                        @break
                                    @case('credit_card')
                                        بطاقة ائتمان
                                        @break
                                    @case('mobile_payment')
                                        دفع عبر الهاتف
                                        @break
                                    @default
                                        {{ $payment->payment_method }}
                                @endswitch
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الدفع</label>
                            <p class="text-gray-900">{{ $payment->payment_date->format('Y-m-d') }}</p>
                        </div>
                        @if($payment->reference_number)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم المرجع</label>
                            <p class="text-gray-900 font-mono">{{ $payment->reference_number }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإنشاء</label>
                            <p class="text-gray-900">{{ $payment->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    @if($payment->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $payment->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات العميل</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم العميل</label>
                            <p class="text-gray-900">{{ $payment->customer->name_ar ?: $payment->customer->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رمز العميل</label>
                            <p class="text-gray-900 font-mono">{{ $payment->customer->code }}</p>
                        </div>
                        @if($payment->customer->phone)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
                            <p class="text-gray-900">{{ $payment->customer->phone }}</p>
                        </div>
                        @endif
                        @if($payment->customer->email)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                            <p class="text-gray-900">{{ $payment->customer->email }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Info -->
            @if($payment->invoice)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات الفاتورة</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الفاتورة</label>
                            <a href="{{ route('sales.invoices.show', $payment->invoice) }}" 
                               class="text-blue-600 hover:text-blue-800 font-mono">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">إجمالي الفاتورة</label>
                            <p class="text-gray-900">{{ number_format($payment->invoice->total_amount) }} د.ع</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ المدفوع</label>
                            <p class="text-gray-900">{{ number_format($payment->invoice->paid_amount) }} د.ع</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ المتبقي</label>
                            <p class="text-gray-900 font-semibold">
                                {{ number_format($payment->invoice->total_amount - $payment->invoice->paid_amount) }} د.ع
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">حالة الفاتورة</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @switch($payment->invoice->status)
                                    @case('paid')
                                        bg-green-100 text-green-800
                                        @break
                                    @case('sent')
                                        bg-blue-100 text-blue-800
                                        @break
                                    @case('draft')
                                        bg-gray-100 text-gray-800
                                        @break
                                    @case('overdue')
                                        bg-red-100 text-red-800
                                        @break
                                    @default
                                        bg-gray-100 text-gray-800
                                @endswitch">
                                @switch($payment->invoice->status)
                                    @case('paid')
                                        مدفوعة
                                        @break
                                    @case('sent')
                                        مرسلة
                                        @break
                                    @case('draft')
                                        مسودة
                                        @break
                                    @case('overdue')
                                        متأخرة
                                        @break
                                    @default
                                        {{ $payment->invoice->status }}
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Creator Info -->
            @if($payment->creator)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات المنشئ</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم المستخدم</label>
                            <p class="text-gray-900">{{ $payment->creator->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                            <p class="text-gray-900">{{ $payment->creator->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإنشاء</label>
                            <p class="text-gray-900">{{ $payment->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .container {
        max-width: none;
        margin: 0;
        padding: 0;
    }
}
</style>
@endsection
