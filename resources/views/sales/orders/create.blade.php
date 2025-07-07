@extends('layouts.app')

@section('page-title', 'إضافة طلب مبيعات جديد')



@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إضافة طلب مبيعات جديد</h1>
            <p class="text-gray-600 mt-1">إنشاء طلب مبيعات جديد للعملاء</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.orders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('sales.orders.store') }}" method="POST" id="orderForm">
            @csrf
            
            <div class="p-6">
                <!-- Customer Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">العميل *</label>
                        <x-searchable-dropdown
                            name="customer_id"
                            placeholder="اختر العميل"
                            search-placeholder="ابحث عن عميل..."
                            :options="$customers->map(function($customer) {
                                return [
                                    'value' => $customer->id,
                                    'text' => $customer->name . ' - ' . $customer->phone
                                ];
                            })->toArray()"
                            value="{{ old('customer_id') }}"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ التسليم</label>
                        <input type="date" name="delivery_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع *</label>
                        <x-searchable-dropdown
                            name="payment_method"
                            placeholder="اختر طريقة الدفع"
                            search-placeholder="ابحث في طرق الدفع..."
                            :options="[
                                ['value' => 'cash', 'text' => 'نقداً'],
                                ['value' => 'credit', 'text' => 'آجل'],
                                ['value' => 'bank_transfer', 'text' => 'تحويل بنكي'],
                                ['value' => 'check', 'text' => 'شيك']
                            ]"
                            value="{{ old('payment_method') }}"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">شروط الدفع (أيام)</label>
                        <input type="number" name="payment_terms" min="0" max="365" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المستودع *</label>
                        <select name="warehouse_id" required class="searchable-select w-full">
                            <option value="">اختر المستودع</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">مندوب المبيعات</label>
                        <select name="sales_rep_id" class="searchable-select w-full">
                            <option value="">اختر المندوب</option>
                            @foreach($salesReps as $rep)
                            <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان الشحن</label>
                        <textarea name="shipping_address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان الفوترة</label>
                        <textarea name="billing_address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">أصناف الطلب</h3>
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

                <!-- Order Summary -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">المجموع الفرعي</p>
                            <p class="text-lg font-bold text-gray-900" id="subtotal">0 د.ع</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">إجمالي الخصم</p>
                            <p class="text-lg font-bold text-red-600" id="totalDiscount">0 د.ع</p>
                        </div>
                        <div class="text-center">
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
                <a href="{{ route('sales.orders.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                    إلغاء
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ الطلب
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Item Row Template -->
<template id="itemRowTemplate">
    <tr class="item-row">
        <td class="px-6 py-4">
            <select name="items[INDEX][product_id]" required class="searchable-select product-select w-full">
                <option value="">اختر المنتج</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">{{ $product->name }}</option>
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
            <input type="number" name="items[INDEX][discount_percentage]" min="0" max="100" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg discount-input">
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

    // Form submission
    document.getElementById('orderForm').addEventListener('submit', function(e) {
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
    // Initialize Select2 for the new row using global function
    SearchableSelect.reinitialize(row);

    // Product selection
    $(row).find('.product-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const price = selectedOption.data('price') || 0;
        $(row).find('.price-input').val(price);
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
            alert('يجب أن يحتوي الطلب على صنف واحد على الأقل');
        }
    });
}

function calculateRowTotal(row) {
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
    
    const subtotal = quantity * price;
    const discountAmount = subtotal * (discount / 100);
    const total = subtotal - discountAmount;
    
    row.querySelector('.item-total').textContent = total.toLocaleString() + ' د.ع';
    
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let subtotal = 0;
    let totalDiscount = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        
        const itemSubtotal = quantity * price;
        const itemDiscount = itemSubtotal * (discount / 100);
        
        subtotal += itemSubtotal;
        totalDiscount += itemDiscount;
    });
    
    const grandTotal = subtotal - totalDiscount;
    
    document.getElementById('subtotal').textContent = subtotal.toLocaleString() + ' د.ع';
    document.getElementById('totalDiscount').textContent = totalDiscount.toLocaleString() + ' د.ع';
    document.getElementById('grandTotal').textContent = grandTotal.toLocaleString() + ' د.ع';
}
</script>
@endpush
