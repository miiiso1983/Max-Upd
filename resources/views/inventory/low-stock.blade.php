@extends('layouts.app')

@section('title', 'تقرير المخزون المنخفض - MaxCon ERP')
@section('page-title', 'تقرير المخزون المنخفض')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">تقرير المخزون المنخفض</h1>
                <p class="text-orange-100">المنتجات التي وصلت إلى الحد الأدنى للمخزون</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('inventory.low-stock') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المخزن</label>
                <select name="warehouse_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="">جميع المخازن</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name_ar ?: $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الفئة</label>
                <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="">جميع الفئات</option>
                    @if(isset($categories))
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name_ar ?: $category->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Low Stock Items Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">المنتجات ذات المخزون المنخفض</h3>
            <div class="flex items-center space-x-2 space-x-reverse">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                    <i class="fas fa-exclamation-triangle ml-1"></i>
                    {{ $lowStockItems->total() }} منتج
                </span>
                <button onclick="exportLowStock()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                    <i class="fas fa-file-excel ml-2"></i>
                    تصدير Excel
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المخزن</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية الحالية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحد الأدنى</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النقص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">متوسط التكلفة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">قيمة النقص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">آخر حركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($lowStockItems as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-orange-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $item->product->name_ar ?: $item->product->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $item->product->sku }}</div>
                                    @if($item->product->category)
                                        <div class="text-xs text-gray-400">{{ $item->product->category->name_ar ?: $item->product->category->name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->warehouse->name_ar ?: $item->warehouse->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                            {{ number_format($item->quantity, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->product->min_stock_level, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                            {{ number_format($item->product->min_stock_level - $item->quantity, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->average_cost, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                            {{ number_format(($item->product->min_stock_level - $item->quantity) * $item->average_cost, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->last_movement_date ? $item->last_movement_date->format('Y-m-d') : 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <button data-product-id="{{ $item->product->id }}" data-warehouse-id="{{ $item->warehouse->id }}"
                                        onclick="createPurchaseOrder(this.dataset.productId, this.dataset.warehouseId)"
                                        class="text-blue-600 hover:text-blue-900" title="إنشاء أمر شراء">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button data-item-id="{{ $item->id }}"
                                        onclick="adjustInventory(this.dataset.itemId)"
                                        class="text-green-600 hover:text-green-900" title="تعديل المخزون">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button data-product-id="{{ $item->product->id }}" data-warehouse-id="{{ $item->warehouse->id }}"
                                        onclick="viewHistory(this.dataset.productId, this.dataset.warehouseId)"
                                        class="text-purple-600 hover:text-purple-900" title="عرض التاريخ">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                                <p class="text-lg font-medium">ممتاز! لا توجد منتجات بمخزون منخفض</p>
                                <p class="text-sm">جميع المنتجات فوق الحد الأدنى للمخزون</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($lowStockItems->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $lowStockItems->links() }}
        </div>
        @endif
    </div>

    <!-- Summary Statistics -->
    @if($lowStockItems->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي المنتجات</p>
                    <p class="text-2xl font-bold text-red-600">{{ $lowStockItems->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-warehouse text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">المخازن المتأثرة</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $lowStockItems->pluck('warehouse_id')->unique()->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">قيمة النقص الإجمالية</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ number_format($lowStockItems->sum(function($item) {
                            return ($item->product->min_stock_level - $item->quantity) * $item->average_cost;
                        }), 0) }} د.ع
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">يحتاج أوامر شراء</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $lowStockItems->count() }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function createPurchaseOrder(productId, warehouseId) {
    if (confirm('هل تريد إنشاء أمر شراء لهذا المنتج؟')) {
        // Redirect to purchase order creation with pre-filled data
        window.location.href = `/purchases/orders/create?product_id=${productId}&warehouse_id=${warehouseId}`;
    }
}

function adjustInventory(inventoryId) {
    // This would open a modal to adjust inventory
    alert('سيتم فتح نافذة تعديل المخزون');
}

function viewHistory(productId, warehouseId) {
    window.location.href = `{{ route('inventory.movements') }}?product_id=${productId}&warehouse_id=${warehouseId}`;
}

function exportLowStock() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.location.href = `{{ route('inventory.low-stock') }}?${params.toString()}`;
}
</script>
@endpush
@endsection
