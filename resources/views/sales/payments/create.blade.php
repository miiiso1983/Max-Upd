@extends('layouts.app')

@section('title', 'إضافة دفعة جديدة')
@section('page-title', 'إضافة دفعة')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إضافة دفعة جديدة</h1>
            <p class="text-gray-600 mt-1">تسجيل دفعة جديدة من العميل</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.payments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('sales.payments.store') }}" method="POST" id="paymentForm" novalidate>
            @csrf
            
            <div class="p-6">
                <!-- Customer and Invoice Selection -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات العميل والفاتورة</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">العميل *</label>
                            <x-searchable-dropdown
                                name="customer_id"
                                placeholder="اختر العميل"
                                search-placeholder="ابحث عن عميل..."
                                :options="$customers->map(function($customer) {
                                    return [
                                        'value' => $customer->id,
                                        'text' => $customer->name_ar ?: $customer->name
                                    ];
                                })->prepend(['value' => '', 'text' => 'اختر العميل'])->toArray()"
                                value="{{ old('customer_id', $customer?->id ?? '') }}"
                                required
                                class="{{ $errors->has('customer_id') ? 'error' : '' }}"
                            />
                            @error('customer_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الفاتورة *</label>
                            <x-searchable-dropdown
                                name="invoice_id"
                                placeholder="اختر الفاتورة"
                                search-placeholder="ابحث في الفواتير..."
                                :options="$unpaidInvoices->map(function($invoice) {
                                    return [
                                        'value' => $invoice->id,
                                        'text' => $invoice->invoice_number . ' - ' .
                                                 number_format($invoice->total_amount - $invoice->paid_amount) . ' د.ع' .
                                                 ' (' . ($invoice->customer->name_ar ?: $invoice->customer->name) . ')'
                                    ];
                                })->toArray()"
                                value="{{ old('invoice_id', $invoice?->id ?? '') }}"
                                required
                                class="{{ $errors->has('invoice_id') ? 'error' : '' }}"
                            />
                            @error('invoice_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Invoice Details (shown when invoice is selected) -->
                    <div id="invoice-details" class="mt-4 p-4 bg-blue-50 rounded-lg hidden">
                        <h4 class="font-semibold text-blue-900 mb-2">تفاصيل الفاتورة</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-blue-700">إجمالي الفاتورة:</span>
                                <span id="invoice-total" class="font-semibold text-blue-900"></span>
                            </div>
                            <div>
                                <span class="text-blue-700">المدفوع:</span>
                                <span id="invoice-paid" class="font-semibold text-blue-900"></span>
                            </div>
                            <div>
                                <span class="text-blue-700">المتبقي:</span>
                                <span id="invoice-remaining" class="font-semibold text-red-600"></span>
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
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" 
                                   min="0.01" step="0.01" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('amount') border-red-500 @enderror">
                            @error('amount')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">المبلغ بالدينار العراقي</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع *</label>
                            <x-searchable-dropdown
                                name="payment_method"
                                placeholder="اختر طريقة الدفع"
                                search-placeholder="ابحث في طرق الدفع..."
                                :options="collect(['' => 'اختر طريقة الدفع'] + $paymentMethods)->map(function($method, $key) {
                                    return [
                                        'value' => $key,
                                        'text' => $method
                                    ];
                                })->values()->toArray()"
                                value="{{ old('payment_method') }}"
                                required
                                class="{{ $errors->has('payment_method') ? 'error' : '' }}"
                            />
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الدفع</label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('payment_date') border-red-500 @enderror">
                            @error('payment_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رقم المرجع</label>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" 
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
                                  placeholder="أي ملاحظات إضافية حول الدفعة...">{{ old('notes') }}</textarea>
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
                    حفظ الدفعة
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerDropdown = document.querySelector('[data-name="customer_id"]');
    const invoiceDropdown = document.querySelector('[data-name="invoice_id"]');
    const invoiceDetails = document.getElementById('invoice-details');
    const amountInput = document.getElementById('amount');

    // Listen for invoice selection changes
    if (invoiceDropdown) {
        invoiceDropdown.addEventListener('change', function(event) {
            const invoiceId = event.detail.value;

            if (invoiceId) {
                // Find invoice data from the dropdown options
                const selectedOption = invoiceDropdown.querySelector(`[data-value="${invoiceId}"]`);
                if (selectedOption) {
                    // Extract invoice details from the text
                    const text = selectedOption.textContent;
                    // You can add more logic here to extract and display invoice details
                    invoiceDetails.classList.remove('hidden');
                }
            } else {
                invoiceDetails.classList.add('hidden');
                amountInput.value = '';
            }
        });
    }

    // Filter invoices by customer (if needed)
    if (customerDropdown) {
        customerDropdown.addEventListener('change', function(event) {
            const customerId = event.detail.value;
            // Reset invoice selection
            if (invoiceDropdown) {
                setDropdownValue('invoice_id', '');
            }
            invoiceDetails.classList.add('hidden');
            amountInput.value = '';
        });
    }

    // Legacy code for old select elements (keeping for compatibility)
    const legacyCustomerSelect = document.getElementById('customer_id');
    const legacyInvoiceSelect = document.getElementById('invoice_id');

    if (legacyCustomerSelect && legacyInvoiceSelect) {
        legacyCustomerSelect.addEventListener('change', function() {
            const customerId = this.value;
            const invoiceOptions = legacyInvoiceSelect.querySelectorAll('option');

            // Reset invoice selection
            legacyInvoiceSelect.value = '';
            invoiceDetails.classList.add('hidden');
            amountInput.value = '';

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
        legacyInvoiceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (this.value && selectedOption.hasAttribute('data-total')) {
                const total = parseFloat(selectedOption.getAttribute('data-total'));
                const paid = parseFloat(selectedOption.getAttribute('data-paid'));
                const remaining = parseFloat(selectedOption.getAttribute('data-remaining'));

                document.getElementById('invoice-total').textContent = total.toLocaleString() + ' د.ع';
                document.getElementById('invoice-paid').textContent = paid.toLocaleString() + ' د.ع';
                document.getElementById('invoice-remaining').textContent = remaining.toLocaleString() + ' د.ع';

                // Set amount to remaining amount
                amountInput.value = remaining;
                amountInput.max = remaining;

                invoiceDetails.classList.remove('hidden');
            } else {
                invoiceDetails.classList.add('hidden');
                amountInput.value = '';
                amountInput.removeAttribute('max');
            }
        });

        // Trigger customer change if pre-selected
        if (legacyCustomerSelect.value) {
            legacyCustomerSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Trigger invoice change if pre-selected
    if (invoiceSelect.value) {
        invoiceSelect.dispatchEvent(new Event('change'));
    }

    // Form validation before submit
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        console.log('Form submit triggered');

        const customerId = getDropdownValue('customer_id');
        const invoiceId = getDropdownValue('invoice_id');
        const paymentMethod = getDropdownValue('payment_method');
        const amount = document.getElementById('amount').value;

        console.log('Form values:', {
            customerId,
            invoiceId,
            paymentMethod,
            amount
        });

        // Check required fields
        if (!customerId) {
            e.preventDefault();
            alert('يرجى اختيار العميل');
            return false;
        }

        if (!invoiceId) {
            e.preventDefault();
            alert('يرجى اختيار الفاتورة');
            return false;
        }

        if (!paymentMethod) {
            e.preventDefault();
            alert('يرجى اختيار طريقة الدفع');
            return false;
        }

        if (!amount || parseFloat(amount) <= 0) {
            e.preventDefault();
            alert('يرجى إدخال مبلغ صحيح');
            return false;
        }

        // Update hidden inputs to ensure values are sent
        const customerHidden = document.querySelector('input[name="customer_id"]');
        const invoiceHidden = document.querySelector('input[name="invoice_id"]');
        const paymentMethodHidden = document.querySelector('input[name="payment_method"]');

        if (customerHidden) customerHidden.value = customerId;
        if (invoiceHidden) invoiceHidden.value = invoiceId;
        if (paymentMethodHidden) paymentMethodHidden.value = paymentMethod;

        console.log('Hidden inputs updated');

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري الحفظ...';
        }

        console.log('Form will be submitted');
        return true;
    });
});
</script>
@endsection
