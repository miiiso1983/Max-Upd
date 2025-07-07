@extends('layouts.app')

@section('page-title', 'تعديل الفاتورة')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تعديل الفاتورة: {{ $invoice->invoice_number }}</h1>
            <p class="text-gray-600 mt-1">تعديل بيانات الفاتورة</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.invoices.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
            <a href="{{ route('sales.invoices.show', $invoice) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-eye ml-2"></i>
                عرض الفاتورة
            </a>
        </div>
    </div>

    <!-- Invoice Status -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-900">معلومات الفاتورة</h3>
                <p class="text-blue-700">رقم الفاتورة: {{ $invoice->invoice_number }}</p>
            </div>
            <div class="text-left">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @switch($invoice->status)
                        @case('draft') bg-gray-100 text-gray-800 @break
                        @case('pending') bg-yellow-100 text-yellow-800 @break
                        @case('paid') bg-green-100 text-green-800 @break
                        @case('overdue') bg-red-100 text-red-800 @break
                        @case('cancelled') bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch">
                    @switch($invoice->status)
                        @case('draft') مسودة @break
                        @case('pending') معلقة @break
                        @case('paid') مدفوعة @break
                        @case('overdue') متأخرة @break
                        @case('cancelled') ملغية @break
                        @default {{ $invoice->status }}
                    @endswitch
                </span>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('sales.invoices.update', $invoice) }}" method="POST" id="invoiceForm">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <!-- Invoice Header -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رقم الفاتورة *</label>
                        <input type="text" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الفاتورة *</label>
                        <input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('invoice_date') border-red-500 @enderror">
                        @error('invoice_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('due_date') border-red-500 @enderror">
                        @error('due_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">العميل *</label>
                        <select name="customer_id" id="customer_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('customer_id') border-red-500 @enderror">
                            <option value="">اختر العميل</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                        {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name_ar ?: $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المستودع *</label>
                        <select name="warehouse_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('warehouse_id') border-red-500 @enderror">
                            <option value="">اختر المستودع</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" 
                                        {{ old('warehouse_id', $invoice->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name_ar ?: $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">عناصر الفاتورة</h3>
                    
                    <!-- Current Items -->
                    <div class="overflow-x-auto mb-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">سعر الوحدة</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">الخصم</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">المجموع</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        @if($item->product->name_ar)
                                            <div class="text-sm text-gray-500">{{ $item->product->name_ar }}</div>
                                        @endif
                                        <div class="text-xs text-gray-400">{{ $item->product->sku }}</div>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button type="button" class="text-red-600 hover:text-red-900 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Invoice Totals -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المجموع الفرعي</label>
                        <input type="number" name="subtotal" value="{{ old('subtotal', $invoice->subtotal) }}" 
                               step="0.01" readonly 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الضريبة (%)</label>
                        <input type="number" name="tax_percentage" value="{{ old('tax_percentage', $invoice->tax_percentage ?? 0) }}" 
                               min="0" max="100" step="0.01" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('tax_percentage') border-red-500 @enderror">
                        @error('tax_percentage')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">مبلغ الضريبة</label>
                        <input type="number" name="tax_amount" value="{{ old('tax_amount', $invoice->tax_amount) }}" 
                               step="0.01" readonly 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الخصم</label>
                        <input type="number" name="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount) }}" 
                               min="0" step="0.01" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('discount_amount') border-red-500 @enderror">
                        @error('discount_amount')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">المجموع الإجمالي</label>
                        <input type="number" name="total_amount" value="{{ old('total_amount', $invoice->total_amount) }}" 
                               step="0.01" readonly 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-lg font-bold">
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                    <textarea name="notes" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                              placeholder="أي ملاحظات إضافية...">{{ old('notes', $invoice->notes) }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <a href="{{ route('sales.invoices.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save ml-2"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate totals when tax percentage or discount changes
    const taxPercentageInput = document.querySelector('input[name="tax_percentage"]');
    const discountAmountInput = document.querySelector('input[name="discount_amount"]');
    const subtotalInput = document.querySelector('input[name="subtotal"]');
    const taxAmountInput = document.querySelector('input[name="tax_amount"]');
    const totalAmountInput = document.querySelector('input[name="total_amount"]');

    function calculateTotals() {
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const taxPercentage = parseFloat(taxPercentageInput.value) || 0;
        const discountAmount = parseFloat(discountAmountInput.value) || 0;

        const taxAmount = (subtotal * taxPercentage) / 100;
        const totalAmount = subtotal + taxAmount - discountAmount;

        taxAmountInput.value = taxAmount.toFixed(2);
        totalAmountInput.value = totalAmount.toFixed(2);
    }

    taxPercentageInput.addEventListener('input', calculateTotals);
    discountAmountInput.addEventListener('input', calculateTotals);
});
</script>
@endsection
