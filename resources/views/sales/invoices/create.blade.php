@extends('layouts.app')

@section('page-title', 'إضافة فاتورة جديدة')



@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إضافة فاتورة جديدة</h1>
            <p class="text-gray-600 mt-1">إنشاء فاتورة مبيعات جديدة</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.invoices.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('sales.invoices.store') }}" method="POST" id="invoiceForm">
            @csrf
            
            <div class="p-6">
                <!-- Invoice Header -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رقم الفاتورة *</label>
                        <input type="text" name="invoice_number" value="{{ $nextInvoiceNumber }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الفاتورة *</label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user ml-2 text-blue-500"></i>
                            العميل *
                        </label>
                        <x-searchable-dropdown
                            name="customer_id"
                            placeholder="اختر العميل..."
                            search-placeholder="ابحث عن العميل بالاسم أو الهاتف..."
                            :options="$customers->map(function($customer) {
                                return [
                                    'value' => $customer->id,
                                    'text' => $customer->name . ' - ' . $customer->phone
                                ];
                            })->toArray()"
                            value="{{ old('customer_id') }}"
                            required
                            class="customer-select"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-credit-card ml-2 text-green-500"></i>
                            طريقة الدفع *
                        </label>
                        <x-searchable-dropdown
                            name="payment_method"
                            placeholder="اختر طريقة الدفع..."
                            search-placeholder="ابحث في طرق الدفع..."
                            :options="[
                                ['value' => 'cash', 'text' => '💵 نقداً'],
                                ['value' => 'credit', 'text' => '📅 آجل'],
                                ['value' => 'bank_transfer', 'text' => '🏦 تحويل بنكي'],
                                ['value' => 'check', 'text' => '📝 شيك'],
                                ['value' => 'credit_card', 'text' => '💳 بطاقة ائتمان']
                            ]"
                            value="{{ old('payment_method') }}"
                            required
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان العميل</label>
                        <input type="text" name="customer_address" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">أصناف الفاتورة</h3>
                        <button type="button" id="addItem" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus ml-2"></i>
                            إضافة صنف
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المنتج</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الكمية</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">السعر</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الخصم %</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجمالي</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTable" class="bg-white divide-y divide-gray-200">
                                <!-- Items will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tax and Totals -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نسبة الضريبة (%)</label>
                        <input type="number" name="tax_rate" min="0" max="100" step="0.01" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" id="taxRate">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">خصم إضافي (د.ع)</label>
                        <input type="number" name="additional_discount" min="0" step="0.01" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" id="additionalDiscount">
                    </div>
                </div>

                <!-- Invoice Summary -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-sm text-gray-600">المجموع الفرعي</p>
                            <p class="text-lg font-bold text-gray-900" id="subtotal">0 د.ع</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">إجمالي الخصم</p>
                            <p class="text-lg font-bold text-red-600" id="totalDiscount">0 د.ع</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">الضريبة</p>
                            <p class="text-lg font-bold text-orange-600" id="taxAmount">0 د.ع</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">المجموع الكلي</p>
                            <p class="text-xl font-bold text-green-600" id="grandTotal">0 د.ع</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="أي ملاحظات إضافية..."></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <a href="{{ route('sales.invoices.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                    إلغاء
                </a>
                <button type="submit" name="action" value="draft" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ كمسودة
                </button>
                <button type="submit" name="action" value="finalize" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-check ml-2"></i>
                    إنهاء الفاتورة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Item Row Template -->
<template id="itemRowTemplate">
    <tr class="item-row">
        <td class="px-6 py-4">
            <select name="items[INDEX][product_id]" required class="advanced-searchable-select product-select w-full"
                    data-placeholder="اختر المنتج..."
                    data-search-placeholder="ابحث في المنتجات...">
                <option value="">اختر المنتج</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}"
                        data-price="{{ $product->selling_price }}"
                        data-stock="{{ $product->current_stock ?? 0 }}">
                    📦 {{ $product->name }} (متوفر: {{ $product->current_stock ?? 0 }})
                </option>
                @endforeach
            </select>
        </td>
        <td class="px-6 py-4">
            <input type="number" name="items[INDEX][quantity]" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg quantity-input">
        </td>
        <td class="px-6 py-4">
            <input type="number" name="items[INDEX][unit_price]" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg price-input">
        </td>
        <td class="px-6 py-4">
            <input type="number" name="items[INDEX][discount_percentage]" min="0" max="100" step="0.01" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg discount-input">
        </td>
        <td class="px-6 py-4">
            <span class="font-bold text-gray-900 item-total">0 د.ع</span>
        </td>
        <td class="px-6 py-4">
            <button type="button" class="text-red-600 hover:text-red-800 remove-item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let itemIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Add first item row
    addItemRow();

    // Add item button
    document.getElementById('addItem').addEventListener('click', addItemRow);

    // Customer selection
    $('.customer-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const address = selectedOption.data('address') || '';
        const paymentTerms = selectedOption.data('payment-terms') || 0;
        
        $('input[name="customer_address"]').val(address);
        
        // Set due date based on payment terms
        if (paymentTerms > 0) {
            const invoiceDate = new Date($('input[name="invoice_date"]').val());
            const dueDate = new Date(invoiceDate.getTime() + (paymentTerms * 24 * 60 * 60 * 1000));
            $('input[name="due_date"]').val(dueDate.toISOString().split('T')[0]);
        }
    });

    // Tax and discount changes
    $('#taxRate, #additionalDiscount').on('input', calculateGrandTotal);

    // Form submission
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        if (document.querySelectorAll('.item-row').length === 0) {
            e.preventDefault();
            alert('يجب إضافة صنف واحد على الأقل');
        }
    });
});

function addItemRow() {
    const template = document.getElementById('itemRowTemplate');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX with actual index
    clone.innerHTML = clone.innerHTML.replace(/INDEX/g, itemIndex);
    
    document.getElementById('itemsTable').appendChild(clone);
    
    // Add event listeners to the new row
    const newRow = document.getElementById('itemsTable').lastElementChild;
    addRowEventListeners(newRow);
    
    itemIndex++;
}

function addRowEventListeners(row) {
    // Initialize Advanced Searchable Select for the new row
    if (window.AdvancedSearchableSelect) {
        AdvancedSearchableSelect.reinitialize(row);
    }
    
    // Product selection
    $(row).find('.product-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const price = selectedOption.data('price') || 0;
        const stock = selectedOption.data('stock') || 0;
        
        $(row).find('.price-input').val(price);
        $(row).find('.quantity-input').attr('max', stock);
        
        calculateRowTotal(row);
    });

    // Quantity, price, discount changes
    $(row).find('.quantity-input, .price-input, .discount-input').on('input', function() {
        calculateRowTotal(row);
    });

    // Remove item
    $(row).find('.remove-item').on('click', function() {
        if (document.querySelectorAll('.item-row').length > 1) {
            $(row).remove();
            calculateGrandTotal();
        } else {
            alert('يجب أن تحتوي الفاتورة على صنف واحد على الأقل');
        }
    });
}

function calculateRowTotal(row) {
    const quantity = parseFloat($(row).find('.quantity-input').val()) || 0;
    const price = parseFloat($(row).find('.price-input').val()) || 0;
    const discount = parseFloat($(row).find('.discount-input').val()) || 0;
    
    const subtotal = quantity * price;
    const discountAmount = subtotal * (discount / 100);
    const total = subtotal - discountAmount;
    
    $(row).find('.item-total').text(total.toLocaleString() + ' د.ع');
    
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let subtotal = 0;
    let totalDiscount = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat($(row).find('.quantity-input').val()) || 0;
        const price = parseFloat($(row).find('.price-input').val()) || 0;
        const discount = parseFloat($(row).find('.discount-input').val()) || 0;
        
        const itemSubtotal = quantity * price;
        const itemDiscount = itemSubtotal * (discount / 100);
        
        subtotal += itemSubtotal;
        totalDiscount += itemDiscount;
    });
    
    const additionalDiscount = parseFloat($('#additionalDiscount').val()) || 0;
    const taxRate = parseFloat($('#taxRate').val()) || 0;
    
    const afterDiscount = subtotal - totalDiscount - additionalDiscount;
    const taxAmount = afterDiscount * (taxRate / 100);
    const grandTotal = afterDiscount + taxAmount;
    
    document.getElementById('subtotal').textContent = subtotal.toLocaleString() + ' د.ع';
    document.getElementById('totalDiscount').textContent = (totalDiscount + additionalDiscount).toLocaleString() + ' د.ع';
    document.getElementById('taxAmount').textContent = taxAmount.toLocaleString() + ' د.ع';
    document.getElementById('grandTotal').textContent = grandTotal.toLocaleString() + ' د.ع';
}
</script>
@endpush
