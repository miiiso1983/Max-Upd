@extends('layouts.app')

@section('title', 'عرض الفاتورة - ' . $invoice->invoice_number)
@section('page-title', 'عرض الفاتورة')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1>
                <p class="text-gray-600">تفاصيل الفاتورة</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('sales.invoices.edit', $invoice) }}" class="btn btn-secondary">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
                <a href="{{ route('sales.invoices.pdf', $invoice) }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-eye ml-2"></i>
                    عرض PDF
                </a>
                <a href="{{ route('sales.invoices.pdf', $invoice) }}" download class="btn btn-success">
                    <i class="fas fa-download ml-2"></i>
                    تحميل PDF
                </a>
                <a href="{{ route('sales.invoices.print', $invoice) }}" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-print ml-2"></i>
                    طباعة
                </a>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-4">
            <span class="px-3 py-1 text-sm font-medium rounded-full 
                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                   ($invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : 
                   ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 
                   ($invoice->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))) }}">
                @switch($invoice->status)
                    @case('paid')
                        مدفوعة
                        @break
                    @case('sent')
                        مرسلة
                        @break
                    @case('overdue')
                        متأخرة
                        @break
                    @case('partial')
                        مدفوعة جزئياً
                        @break
                    @case('draft')
                        مسودة
                        @break
                    @default
                        {{ $invoice->status }}
                @endswitch
            </span>
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Customer Information -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات العميل</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">اسم العميل</label>
                    <p class="text-gray-900">{{ $invoice->customer->name_ar ?? $invoice->customer->name ?? 'غير محدد' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">نوع العميل</label>
                    <p class="text-gray-900">{{ $invoice->customer->type ?? 'غير محدد' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">العنوان</label>
                    <p class="text-gray-900">{{ $invoice->customer->address ?? 'غير محدد' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">الهاتف</label>
                    <p class="text-gray-900">{{ $invoice->customer->phone ?? 'غير محدد' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">البريد الإلكتروني</label>
                    <p class="text-gray-900">{{ $invoice->customer->email ?? 'غير محدد' }}</p>
                </div>
            </div>
        </div>

        <!-- Invoice Information -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الفاتورة</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-600">رقم الفاتورة</label>
                    <p class="text-gray-900">{{ $invoice->invoice_number }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">تاريخ الفاتورة</label>
                    <p class="text-gray-900">{{ $invoice->invoice_date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">تاريخ الاستحقاق</label>
                    <p class="text-gray-900">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'غير محدد' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">شروط الدفع</label>
                    <p class="text-gray-900">{{ $invoice->payment_terms }} يوم</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-600">العملة</label>
                    <p class="text-gray-900">{{ $invoice->currency }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">عناصر الفاتورة</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المنتج
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الوصف
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الكمية
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            سعر الوحدة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الخصم
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الضريبة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المجموع
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoice->items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $item->product->name ?? 'منتج محذوف' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $item->product->code ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                {{ $item->description_ar ?? $item->description ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->unit_price, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->discount_amount ?? 0, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->tax_amount ?? 0, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($item->total_amount, 0) }} د.ع
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            لا توجد عناصر في هذه الفاتورة
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invoice Totals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Summary -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">ملخص المدفوعات</h3>
            <div class="space-y-3">
                @forelse($invoice->payments as $payment)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $payment->payment_method }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <p class="text-sm font-medium text-green-600">{{ number_format($payment->amount, 0) }} د.ع</p>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">لا توجد مدفوعات مسجلة</p>
                @endforelse
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">الملخص المالي</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">المجموع الفرعي:</span>
                    <span class="font-medium">{{ number_format($invoice->subtotal, 0) }} د.ع</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الضريبة:</span>
                    <span class="font-medium">{{ number_format($invoice->tax_amount, 0) }} د.ع</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">الخصم:</span>
                    <span class="font-medium">{{ number_format($invoice->discount_amount, 0) }} د.ع</span>
                </div>
                <hr>
                <div class="flex justify-between text-lg font-bold">
                    <span>المجموع الإجمالي:</span>
                    <span class="text-blue-600">{{ number_format($invoice->total_amount, 0) }} د.ع</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">المبلغ المدفوع:</span>
                    <span class="font-medium text-green-600">{{ number_format($invoice->paid_amount, 0) }} د.ع</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                    <span>الرصيد المستحق:</span>
                    <span class="text-red-600">{{ number_format($invoice->balance_due, 0) }} د.ع</span>
                </div>
            </div>

            @if($invoice->balance_due > 0)
            <div class="mt-4">
                <a href="{{ route('sales.payments.create', ['invoice_id' => $invoice->id]) }}" 
                   class="btn btn-primary w-full">
                    <i class="fas fa-credit-card ml-2"></i>
                    تسجيل دفعة
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Notes -->
    @if($invoice->notes || $invoice->terms_conditions)
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">ملاحظات وشروط</h3>
        @if($invoice->notes)
        <div class="mb-4">
            <label class="text-sm font-medium text-gray-600">ملاحظات:</label>
            <p class="text-gray-900 mt-1">{{ $invoice->notes }}</p>
        </div>
        @endif
        @if($invoice->terms_conditions)
        <div>
            <label class="text-sm font-medium text-gray-600">الشروط والأحكام:</label>
            <p class="text-gray-900 mt-1">{{ $invoice->terms_conditions }}</p>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
function printInvoice() {
    // Open PDF in new window for printing
    const printUrl = '{{ route("sales.invoices.print", $invoice) }}';
    window.open(printUrl, '_blank');
}

function downloadPDF() {
    // Download PDF directly
    const downloadUrl = '{{ route("sales.invoices.pdf", $invoice) }}';
    window.location.href = downloadUrl;
}

function viewPDF() {
    // View PDF in new window
    const viewUrl = '{{ route("sales.invoices.pdf", $invoice) }}';
    window.open(viewUrl, '_blank');
}
</script>

<style>
/* Hover Effects */
.btn:hover {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.btn:hover i {
    color: #6f42c1 !important;
}

h1:hover, h2:hover, h3:hover {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
    cursor: pointer;
}

.text-gray-900:hover {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.font-semibold:hover {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

.text-lg:hover {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
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

/* Status badge hover */
.bg-green-100:hover, .bg-yellow-100:hover, .bg-red-100:hover {
    background-color: #6f42c1 !important;
    color: white !important;
    transition: all 0.3s ease;
}

/* Card hover effects */
.bg-white:hover {
    box-shadow: 0 10px 25px rgba(111, 66, 193, 0.1) !important;
    transition: box-shadow 0.3s ease;
}

.bg-white:hover h3,
.bg-white:hover .text-gray-900,
.bg-white:hover .font-semibold {
    color: #6f42c1 !important;
    transition: color 0.3s ease;
}

@media print {
    .btn, .no-print {
        display: none !important;
    }

    .card-shadow {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
    }

    /* Remove hover effects in print */
    *:hover {
        color: inherit !important;
        background-color: inherit !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
