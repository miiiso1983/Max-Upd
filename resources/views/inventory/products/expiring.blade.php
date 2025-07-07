@extends('layouts.app')

@section('title', 'منتجات منتهية الصلاحية - MaxCon ERP')
@section('page-title', 'منتجات منتهية الصلاحية')

@section('content')
<div class="space-y-6" id="expiring-products-container" data-products="{{ base64_encode(json_encode($products->items())) }}">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">منتجات منتهية الصلاحية</h1>
            <p class="text-gray-600">المنتجات التي تنتهي صلاحيتها خلال {{ $days ?? 30 }} يوم</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('inventory.products.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                منتج جديد
            </a>
            <button onclick="exportExpiring()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download ml-2"></i>
                تصدير القائمة
            </button>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            </div>
            <div class="mr-3">
                <h3 class="text-sm font-medium text-red-800">تنبيه: منتجات منتهية الصلاحية</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>يوجد <span class="font-bold">{{ $products->total() }}</span> منتج ينتهي صلاحيته خلال {{ $days ?? 30 }} يوم. يرجى اتخاذ الإجراءات اللازمة.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">فلاتر البحث</h3>
        <form method="GET" action="{{ route('inventory.products.expiring') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Days Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">عدد الأيام</label>
                <select name="days" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="7" {{ request('days') == '7' ? 'selected' : '' }}>7 أيام</option>
                    <option value="15" {{ request('days') == '15' ? 'selected' : '' }}>15 يوم</option>
                    <option value="30" {{ request('days', '30') == '30' ? 'selected' : '' }}>30 يوم</option>
                    <option value="60" {{ request('days') == '60' ? 'selected' : '' }}>60 يوم</option>
                    <option value="90" {{ request('days') == '90' ? 'selected' : '' }}>90 يوم</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الفئة</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الفئات</option>
                    <option value="medicine" {{ request('category') == 'medicine' ? 'selected' : '' }}>أدوية</option>
                    <option value="cosmetic" {{ request('category') == 'cosmetic' ? 'selected' : '' }}>مستحضرات تجميل</option>
                    <option value="supplement" {{ request('category') == 'supplement' ? 'selected' : '' }}>مكملات غذائية</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="اسم المنتج أو الباركود..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Actions -->
            <div class="flex items-end space-x-2 space-x-reverse">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-search ml-1"></i>
                    بحث
                </button>
                <a href="{{ route('inventory.products.expiring') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-undo ml-1"></i>
                    إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-red-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">منتهية خلال 7 أيام</p>
                    <p class="text-2xl font-bold text-red-600" id="expiring-7-days">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-orange-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">منتهية خلال 30 يوم</p>
                    <p class="text-2xl font-bold text-orange-600" id="expiring-30-days">{{ $products->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">قيمة المنتجات المنتهية</p>
                    <p class="text-2xl font-bold text-purple-600" id="expiring-value">0 د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-boxes text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي الكمية</p>
                    <p class="text-2xl font-bold text-blue-600" id="expiring-quantity">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Products Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">قائمة المنتجات منتهية الصلاحية</h3>
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="selectAll()" class="text-sm text-blue-600 hover:text-blue-800">تحديد الكل</button>
                    <button onclick="createDiscountSale()" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-percentage ml-1"></i>
                        تخفيض سعر
                    </button>
                    <button onclick="createDisposalOrder()" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-trash ml-1"></i>
                        إتلاف
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفئة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية الحالية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الأيام المتبقية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">قيمة المخزون</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" class="product-checkbox">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($product->image)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-box text-gray-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="mr-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $product->sku ?? 'لا يوجد' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->category->name ?? 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->current_stock ?? 0 }} {{ $product->unit ?? 'قطعة' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $expiryDate = $product->expiry_date ?? $product->stockEntries->where('expiry_date', '>', now())->sortBy('expiry_date')->first()?->expiry_date;
                            @endphp
                            @if($expiryDate)
                                {{ \Carbon\Carbon::parse($expiryDate)->format('Y-m-d') }}
                            @else
                                غير محدد
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($expiryDate)
                                @php
                                    $daysRemaining = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($expiryDate), false);
                                @endphp
                                @if($daysRemaining < 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        منتهي الصلاحية
                                    </span>
                                @elseif($daysRemaining <= 7)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $daysRemaining }} يوم
                                    </span>
                                @elseif($daysRemaining <= 30)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ $daysRemaining }} يوم
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $daysRemaining }} يوم
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-500">غير محدد</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $stockValue = ($product->current_stock ?? 0) * ($product->cost_price ?? 0);
                            @endphp
                            {{ number_format($stockValue, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    نشط
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    غير نشط
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="{{ route('inventory.products.show', $product) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button data-product-id="{{ $product->id }}"
                                        onclick="createDiscountForProduct(this.dataset.productId)"
                                        class="text-orange-600 hover:text-orange-900" title="إنشاء خصم">
                                    <i class="fas fa-percentage"></i>
                                </button>
                                <button data-product-id="{{ $product->id }}"
                                        onclick="disposeProduct(this.dataset.productId)"
                                        class="text-red-600 hover:text-red-900" title="إتلاف المنتج">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد منتجات منتهية الصلاحية</h3>
                                <p class="text-gray-500">جميع المنتجات في حالة جيدة ولا تحتاج إجراءات فورية</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="createDiscountSale()" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors hover-scale">
                <i class="fas fa-percentage text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">تخفيض الأسعار</span>
            </button>
            <button onclick="createDisposalOrder()" class="flex flex-col items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors hover-scale">
                <i class="fas fa-trash text-2xl text-red-600 mb-2"></i>
                <span class="text-sm font-medium text-red-800">إتلاف المنتجات</span>
            </button>
            <button onclick="exportExpiring()" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors hover-scale">
                <i class="fas fa-download text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">تصدير القائمة</span>
            </button>
            <a href="{{ route('reports.inventory.expiring') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors hover-scale">
                <i class="fas fa-chart-line text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">تقرير مفصل</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updateStatistics();
});

// Toggle select all functionality
function toggleSelectAll(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Select all products
function selectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    selectAllCheckbox.checked = true;
    selectAllCheckbox.dispatchEvent(new Event('change'));
}

// Update statistics
function updateStatistics() {
    const container = document.getElementById('expiring-products-container');
    const productsData = container.getAttribute('data-products');
    const products = JSON.parse(atob(productsData));

    let expiring7Days = 0;
    let expiring30Days = 0;
    let totalValue = 0;
    let totalQuantity = 0;

    products.forEach(product => {
        const expiryDate = product.expiry_date || (product.stock_entries && product.stock_entries[0] ? product.stock_entries[0].expiry_date : null);
        if (expiryDate) {
            const daysRemaining = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24));
            const currentStock = product.current_stock || 0;
            const costPrice = product.cost_price || 0;

            if (daysRemaining <= 7) {
                expiring7Days++;
            }
            if (daysRemaining <= 30) {
                expiring30Days++;
            }

            totalValue += currentStock * costPrice;
            totalQuantity += currentStock;
        }
    });

    document.getElementById('expiring-7-days').textContent = expiring7Days;
    document.getElementById('expiring-30-days').textContent = expiring30Days;
    document.getElementById('expiring-value').textContent = MaxCon.formatNumber(totalValue) + ' د.ع';
    document.getElementById('expiring-quantity').textContent = MaxCon.formatNumber(totalQuantity);
}

// Create discount sale for selected products
function createDiscountSale() {
    const selectedProducts = getSelectedProducts();

    if (selectedProducts.length === 0) {
        MaxCon.showNotification('يرجى تحديد منتج واحد على الأقل', 'warning');
        return;
    }

    // Show discount modal
    showDiscountModal(selectedProducts);
}

// Create discount for specific product
function createDiscountForProduct(productId) {
    showDiscountModal([productId]);
}

// Show discount modal
function showDiscountModal(productIds) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.id = 'discountModal';

    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">إنشاء تخفيض للمنتجات</h3>
                    <button onclick="closeDiscountModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نسبة التخفيض (%)</label>
                        <input type="number" id="discountPercentage" min="1" max="90" value="20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ انتهاء التخفيض</label>
                        <input type="date" id="discountEndDate"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                        <textarea id="discountNotes" rows="3" placeholder="سبب التخفيض..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 space-x-reverse mt-6">
                    <button onclick="closeDiscountModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button onclick="confirmDiscount(${JSON.stringify(productIds)})" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">
                        إنشاء التخفيض
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Set default end date (7 days from now)
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + 7);
    document.getElementById('discountEndDate').value = endDate.toISOString().split('T')[0];
}

// Close discount modal
function closeDiscountModal() {
    const modal = document.getElementById('discountModal');
    if (modal) {
        modal.remove();
    }
}

// Confirm discount creation
function confirmDiscount(productIds) {
    const percentage = document.getElementById('discountPercentage').value;
    const endDate = document.getElementById('discountEndDate').value;
    const notes = document.getElementById('discountNotes').value;

    if (!percentage || !endDate) {
        MaxCon.showNotification('يرجى ملء جميع الحقول المطلوبة', 'warning');
        return;
    }

    // This would create the actual discount
    MaxCon.showNotification(`تم إنشاء تخفيض ${percentage}% لـ ${productIds.length} منتج`, 'success');
    closeDiscountModal();

    // Refresh page after a delay
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

// Create disposal order for selected products
function createDisposalOrder() {
    const selectedProducts = getSelectedProducts();

    if (selectedProducts.length === 0) {
        MaxCon.showNotification('يرجى تحديد منتج واحد على الأقل', 'warning');
        return;
    }

    showDisposalModal(selectedProducts);
}

// Dispose specific product
function disposeProduct(productId) {
    showDisposalModal([productId]);
}

// Show disposal modal
function showDisposalModal(productIds) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.id = 'disposalModal';

    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">إتلاف المنتجات</h3>
                    <button onclick="closeDisposalModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="bg-red-50 border border-red-200 rounded-md p-3">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-red-400 mt-0.5"></i>
                            <div class="mr-3">
                                <h3 class="text-sm font-medium text-red-800">تحذير</h3>
                                <p class="text-sm text-red-700 mt-1">
                                    سيتم إتلاف ${productIds.length} منتج نهائياً ولا يمكن التراجع عن هذا الإجراء.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">سبب الإتلاف</label>
                        <select id="disposalReason" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">اختر السبب</option>
                            <option value="expired">منتهي الصلاحية</option>
                            <option value="damaged">تالف</option>
                            <option value="contaminated">ملوث</option>
                            <option value="recalled">مسحوب من السوق</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات إضافية</label>
                        <textarea id="disposalNotes" rows="3" placeholder="تفاصيل إضافية..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 space-x-reverse mt-6">
                    <button onclick="closeDisposalModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button onclick="confirmDisposal(${JSON.stringify(productIds)})" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        تأكيد الإتلاف
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}

// Close disposal modal
function closeDisposalModal() {
    const modal = document.getElementById('disposalModal');
    if (modal) {
        modal.remove();
    }
}

// Confirm disposal
function confirmDisposal(productIds) {
    const reason = document.getElementById('disposalReason').value;
    const notes = document.getElementById('disposalNotes').value;

    if (!reason) {
        MaxCon.showNotification('يرجى تحديد سبب الإتلاف', 'warning');
        return;
    }

    // This would create the actual disposal record
    MaxCon.showNotification(`تم إتلاف ${productIds.length} منتج بنجاح`, 'success');
    closeDisposalModal();

    // Refresh page after a delay
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

// Get selected products
function getSelectedProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    return Array.from(checkboxes).map(checkbox => parseInt(checkbox.value));
}

// Export expiring products
function exportExpiring() {
    window.location.href = '/api/inventory/products/expiring/export?days={{ $days ?? 30 }}';
}

// Initialize MaxCon object if not exists
if (typeof MaxCon === 'undefined') {
    window.MaxCon = {
        showNotification: function(message, type = 'info') {
            // Simple notification fallback
            alert(message);
        },
        formatNumber: function(number) {
            return new Intl.NumberFormat('ar-IQ').format(number);
        }
    };
}
</script>
@endpush
