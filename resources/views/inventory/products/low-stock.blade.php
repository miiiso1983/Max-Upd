@extends('layouts.app')

@section('title', 'منتجات بمخزون منخفض - MaxCon ERP')
@section('page-title', 'منتجات بمخزون منخفض')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">منتجات بمخزون منخفض</h1>
            <p class="text-gray-600">المنتجات التي تحتاج إعادة تموين</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('inventory.products.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus ml-2"></i>
                منتج جديد
            </a>
            <button onclick="exportLowStock()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download ml-2"></i>
                تصدير القائمة
            </button>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="bg-orange-100 border-l-4 border-orange-500 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-orange-500 text-xl"></i>
            </div>
            <div class="mr-3">
                <p class="text-sm text-orange-700">
                    <strong>تنبيه:</strong> يوجد {{ $products->total() }} منتج بمخزون منخفض يحتاج إعادة تموين فوري.
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">منتجات بمخزون منخفض</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $products->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">منتجات نفد مخزونها</p>
                    <p class="text-2xl font-bold text-red-600" id="out-of-stock-count">0</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">قيمة المخزون المطلوب</p>
                    <p class="text-2xl font-bold text-blue-600" id="restock-value">0</p>
                    <p class="text-xs text-gray-500">د.ع</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">قائمة المنتجات بمخزون منخفض</h3>
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="selectAll()" class="text-sm text-blue-600 hover:text-blue-800">تحديد الكل</button>
                    <button onclick="createPurchaseOrder()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-shopping-cart ml-1"></i>
                        إنشاء طلب شراء
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفئة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المخزون الحالي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحد الأدنى</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المطلوبة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">القيمة المقدرة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50" data-product-id="{{ $product->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected_products[]" value="{{ $product->id }}" 
                                   class="product-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center ml-3">
                                    <i class="fas fa-pills text-orange-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                    @if($product->barcode)
                                        <div class="text-sm text-gray-500">{{ $product->barcode }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->category ? $product->category->name : 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium {{ $product->current_stock <= 0 ? 'text-red-600' : 'text-orange-600' }}">
                                {{ $product->current_stock ?? 0 }}
                            </span>
                            <div class="text-xs text-gray-500">{{ $product->unit_of_measure }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->minimum_stock ?? $product->min_stock_level ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $currentStock = $product->current_stock ?? 0;
                                $minimumStock = $product->minimum_stock ?? $product->min_stock_level ?? 0;
                                $maxStock = $product->max_stock_level ?? ($minimumStock * 3);
                                $requiredQty = max(0, $maxStock - $currentStock);
                            @endphp
                            <span class="text-sm font-medium text-blue-600">{{ $requiredQty }}</span>
                            <div class="text-xs text-gray-500">للوصول للحد الأقصى</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $estimatedValue = $requiredQty * ($product->purchase_price ?? $product->cost_price ?? 0);
                            @endphp
                            {{ number_format($estimatedValue, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="{{ route('inventory.products.show', $product) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="adjustStock({{ $product->id }})" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-warehouse"></i>
                                </button>
                                <button onclick="addToPurchaseOrder({{ $product->id }}, {{ $requiredQty }})" class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                                <p class="text-lg">ممتاز! جميع المنتجات بمخزون كافي</p>
                                <p class="text-sm">لا توجد منتجات تحتاج إعادة تموين</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="createPurchaseOrder()" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors hover-scale">
                <i class="fas fa-shopping-cart text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-green-800">إنشاء طلب شراء</span>
            </button>
            <button onclick="exportLowStock()" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors hover-scale">
                <i class="fas fa-download text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-blue-800">تصدير القائمة</span>
            </button>
            <button onclick="sendAlert()" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors hover-scale">
                <i class="fas fa-bell text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-orange-800">إرسال تنبيه</span>
            </button>
            <a href="{{ route('reports.inventory.stock-levels') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors hover-scale">
                <i class="fas fa-chart-bar text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-purple-800">تقرير مفصل</span>
            </a>
        </div>
    </div>
</div>

<!-- Purchase Order Modal -->
<div id="purchaseOrderModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">إنشاء طلب شراء</h3>
                <button onclick="closePurchaseOrderModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="purchaseOrderContent">
                <!-- Purchase order form will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    calculateStats();
    
    // Handle select all checkbox
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function calculateStats() {
    const rows = document.querySelectorAll('tbody tr[data-product-id]');
    let outOfStockCount = 0;
    let totalRestockValue = 0;
    
    rows.forEach(row => {
        const currentStockElement = row.querySelector('td:nth-child(4) span');
        const valueElement = row.querySelector('td:nth-child(7)');
        
        if (currentStockElement) {
            const currentStock = parseInt(currentStockElement.textContent.trim());
            if (currentStock <= 0) {
                outOfStockCount++;
            }
        }
        
        if (valueElement) {
            const value = parseFloat(valueElement.textContent.replace(/[^\d.]/g, ''));
            if (!isNaN(value)) {
                totalRestockValue += value;
            }
        }
    });
    
    document.getElementById('out-of-stock-count').textContent = outOfStockCount;
    document.getElementById('restock-value').textContent = MaxCon.formatNumber(totalRestockValue);
}

function selectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    selectAllCheckbox.checked = true;
    selectAllCheckbox.dispatchEvent(new Event('change'));
}

function adjustStock(productId) {
    window.location.href = `/inventory/products/${productId}/adjust-stock`;
}

function addToPurchaseOrder(productId, quantity) {
    // Add product to purchase order
    console.log(`Adding product ${productId} with quantity ${quantity} to purchase order`);
    MaxCon.showNotification(`تم إضافة المنتج إلى طلب الشراء بكمية ${quantity}`, 'success');
}

function createPurchaseOrder() {
    const selectedProducts = document.querySelectorAll('.product-checkbox:checked');
    
    if (selectedProducts.length === 0) {
        MaxCon.showNotification('يرجى تحديد منتج واحد على الأقل', 'warning');
        return;
    }
    
    // Collect selected product data
    const products = Array.from(selectedProducts).map(checkbox => {
        const row = checkbox.closest('tr');
        const productId = checkbox.value;
        const productName = row.querySelector('td:nth-child(2) .text-gray-900').textContent;
        const currentStock = parseInt(row.querySelector('td:nth-child(4) span').textContent);
        const requiredQty = parseInt(row.querySelector('td:nth-child(6) span').textContent);
        
        return {
            id: productId,
            name: productName,
            current_stock: currentStock,
            required_quantity: requiredQty
        };
    });
    
    // Show purchase order modal
    document.getElementById('purchaseOrderContent').innerHTML = `
        <div class="space-y-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-700">
                    <strong>المنتجات المحددة:</strong> ${products.length} منتج
                </p>
            </div>
            <div class="space-y-2">
                ${products.map(product => `
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-sm font-medium">${product.name}</span>
                        <span class="text-sm text-gray-600">الكمية: ${product.required_quantity}</span>
                    </div>
                `).join('')}
            </div>
            <div class="flex justify-end space-x-2 space-x-reverse">
                <button onclick="closePurchaseOrderModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    إلغاء
                </button>
                <button onclick="confirmPurchaseOrder()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    إنشاء طلب الشراء
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('purchaseOrderModal').classList.remove('hidden');
}

function closePurchaseOrderModal() {
    document.getElementById('purchaseOrderModal').classList.add('hidden');
}

function confirmPurchaseOrder() {
    // This would create the actual purchase order
    MaxCon.showNotification('تم إنشاء طلب الشراء بنجاح', 'success');
    closePurchaseOrderModal();
    
    // Redirect to purchase orders page
    setTimeout(() => {
        window.location.href = '/suppliers/purchase-orders/create';
    }, 1500);
}

function exportLowStock() {
    window.location.href = '/api/inventory/products/low-stock/export';
}

function sendAlert() {
    fetch('/api/inventory/products/low-stock/alert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            MaxCon.showNotification('تم إرسال التنبيه بنجاح', 'success');
        } else {
            MaxCon.showNotification('حدث خطأ في إرسال التنبيه', 'error');
        }
    })
    .catch(error => {
        console.error('Error sending alert:', error);
        MaxCon.showNotification('حدث خطأ في إرسال التنبيه', 'error');
    });
}
</script>
@endpush
