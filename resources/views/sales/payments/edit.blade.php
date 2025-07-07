@extends('layouts.app')

@section('title', 'تعديل الدفعة')
@section('page-title', 'تعديل الدفعة')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تعديل الدفعة: {{ $payment->payment_number }}</h1>
            <p class="text-gray-600 mt-1">تعديل بيانات الدفعة</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
            <a href="{{ route('sales.payments.show', $payment) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-eye ml-2"></i>
                عرض الدفعة
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('sales.payments.update', $payment) }}" method="POST" id="paymentForm">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <!-- Payment Info -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">معلومات الدفعة</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-blue-700">رقم الدفعة:</span>
                            <span class="font-semibold text-blue-900">{{ $payment->payment_number }}</span>
                        </div>
                        <div>
                            <span class="text-blue-700">الحالة:</span>
                            <span class="font-semibold 
                                @if($payment->status === 'completed') text-green-600
                                @elseif($payment->status === 'pending') text-yellow-600
                                @elseif($payment->status === 'cancelled') text-red-600
                                @else text-gray-600 @endif">
                                {{ $payment->status === 'completed' ? 'مكتملة' : 
                                   ($payment->status === 'pending' ? 'معلقة' : 
                                   ($payment->status === 'cancelled' ? 'ملغية' : $payment->status)) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-blue-700">تاريخ الإنشاء:</span>
                            <span class="font-semibold text-blue-900">{{ $payment->created_at->format('Y/m/d H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Customer and Invoice Selection -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات العميل والفاتورة</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">العميل *</label>
                            <select name="customer_id" id="customer_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('customer_id') border-red-500 @enderror">
                                <option value="">اختر العميل</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ old('customer_id', $payment->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name_ar ?: $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الفاتورة *</label>
                            <select name="invoice_id" id="invoice_id" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('invoice_id') border-red-500 @enderror">
                                <option value="">اختر الفاتورة</option>
                                @foreach($unpaidInvoices as $invoice)
                                    <option value="{{ $invoice->id }}" 
                                            data-customer="{{ $invoice->customer_id }}"
                                            data-total="{{ $invoice->total_amount }}"
                                            data-paid="{{ $invoice->paid_amount }}"
                                            data-remaining="{{ $invoice->total_amount - $invoice->paid_amount }}"
                                            {{ old('invoice_id', $payment->invoice_id) == $invoice->id ? 'selected' : '' }}>
                                        {{ $invoice->invoice_number }} - 
                                        {{ number_format($invoice->total_amount - $invoice->paid_amount) }} د.ع
                                        ({{ $invoice->customer->name_ar ?: $invoice->customer->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div id="invoice-details" class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">تفاصيل الفاتورة</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-blue-700">إجمالي الفاتورة:</span>
                                <span id="invoice-total" class="font-semibold text-blue-900">
                                    {{ number_format($payment->invoice->total_amount) }} د.ع
                                </span>
                            </div>
                            <div>
                                <span class="text-blue-700">المدفوع:</span>
                                <span id="invoice-paid" class="font-semibold text-blue-900">
                                    {{ number_format($payment->invoice->paid_amount) }} د.ع
                                </span>
                            </div>
                            <div>
                                <span class="text-blue-700">المتبقي:</span>
                                <span id="invoice-remaining" class="font-semibold text-red-600">
                                    {{ number_format($payment->invoice->total_amount - $payment->invoice->paid_amount) }} د.ع
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">تفاصيل الدفعة</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ *</label>
                            <input type="number" name="amount" id="amount" value="{{ old('amount', $payment->amount) }}" 
                                   min="0.01" step="0.01" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('amount') border-red-500 @enderror">
                            @error('amount')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">المبلغ بالدينار العراقي</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع *</label>
                            <select name="payment_method" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('payment_method') border-red-500 @enderror">
                                <option value="">اختر طريقة الدفع</option>
                                @foreach($paymentMethods as $key => $method)
                                    <option value="{{ $key }}" {{ old('payment_method', $payment->payment_method) == $key ? 'selected' : '' }}>
                                        {{ $method }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الدفع</label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('payment_date') border-red-500 @enderror">
                            @error('payment_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رقم المرجع</label>
                            <input type="text" name="reference_number" value="{{ old('reference_number', $payment->reference_number) }}" 
                                   placeholder="رقم الشيك، رقم التحويل، إلخ..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('reference_number') border-red-500 @enderror">
                            @error('reference_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ملاحظات</h3>
                    <div>
                        <textarea name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="أي ملاحظات إضافية حول الدفعة...">{{ old('notes', $payment->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <a href="{{ route('sales.payments.index') }}" 
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
    const customerSelect = document.getElementById('customer_id');
    const invoiceSelect = document.getElementById('invoice_id');
    const invoiceDetails = document.getElementById('invoice-details');
    const amountInput = document.getElementById('amount');

    // Filter invoices by customer
    customerSelect.addEventListener('change', function() {
        const customerId = this.value;
        const invoiceOptions = invoiceSelect.querySelectorAll('option');
        
        // Show/hide invoice options based on customer
        invoiceOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const optionCustomerId = option.getAttribute('data-customer');
            if (customerId === '' || optionCustomerId === customerId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Show invoice details when invoice is selected
    invoiceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value && selectedOption.hasAttribute('data-total')) {
            const total = parseFloat(selectedOption.getAttribute('data-total'));
            const paid = parseFloat(selectedOption.getAttribute('data-paid'));
            const remaining = parseFloat(selectedOption.getAttribute('data-remaining'));
            
            document.getElementById('invoice-total').textContent = total.toLocaleString() + ' د.ع';
            document.getElementById('invoice-paid').textContent = paid.toLocaleString() + ' د.ع';
            document.getElementById('invoice-remaining').textContent = remaining.toLocaleString() + ' د.ع';
            
            amountInput.max = remaining + {{ $payment->amount }};
        }
    });

    // Trigger customer change if pre-selected
    if (customerSelect.value) {
        customerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
