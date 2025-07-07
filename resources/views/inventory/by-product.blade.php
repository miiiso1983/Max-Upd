@extends('layouts.app')

@section('title', 'المخزون حسب المنتج - MaxCon ERP')
@section('page-title', 'المخزون حسب المنتج')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">المخزون حسب المنتج</h1>
                <p class="text-purple-100">عرض إجمالي المخزون لكل منتج في جميع المخازن</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('inventory.by-product') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="البحث في المنتجات..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الفئة</label>
                <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">جميع الفئات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name_ar ?: $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">العلامة التجارية</label>
                <select name="brand_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">جميع العلامات</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name_ar ?: $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة المنتجات والمخزون</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفئة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">العلامة التجارية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المتاحة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المحجوزة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي القيمة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عدد المخازن</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <i class="fas fa-box text-purple-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $product->name_ar ?: $product->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ optional($product->category)->name_ar ?: optional($product->category)->name ?: 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ optional($product->brand)->name_ar ?: optional($product->brand)->name ?: 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-medium {{ $product->total_quantity <= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($product->total_quantity ?? 0, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-medium text-green-600">
                                {{ number_format($product->available_quantity ?? 0, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($product->reserved_quantity ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-medium text-green-600">
                                {{ number_format($product->total_value ?? 0, 0) }} د.ع
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $product->inventory->count() }} مخزن
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <button data-product-id="{{ $product->id }}"
                                        onclick="viewProductDetails(this.dataset.productId)"
                                        class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button data-product-id="{{ $product->id }}"
                                        onclick="viewProductMovements(this.dataset.productId)"
                                        class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-history"></i>
                                </button>
                                <a href="{{ route('inventory.products.edit', $product) }}" 
                                   class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-boxes text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد منتجات</p>
                                <p class="text-sm">لا توجد منتجات تطابق المعايير المحددة</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Product Details Modal -->
<div id="productDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">تفاصيل المنتج في المخازن</h3>
                <button onclick="closeProductDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="productDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewProductDetails(productId) {
    document.getElementById('productDetailsModal').classList.remove('hidden');
    document.getElementById('productDetailsContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
    
    fetch(`/inventory/products/${productId}/warehouses`)
        .then(response => response.json())
        .then(data => {
            let html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
            html += '<thead class="bg-gray-50"><tr>';
            html += '<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المخزن</th>';
            html += '<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الكمية المتاحة</th>';
            html += '<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الكمية المحجوزة</th>';
            html += '<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجمالي الكمية</th>';
            html += '<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموقع</th>';
            html += '<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الانتهاء</th>';
            html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
            
            if (data.inventory && data.inventory.length > 0) {
                data.inventory.forEach(item => {
                    html += '<tr>';
                    html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.warehouse.name_ar || item.warehouse.name}</td>`;
                    html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">${parseFloat(item.available_quantity).toFixed(2)}</td>`;
                    html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">${parseFloat(item.reserved_quantity).toFixed(2)}</td>`;
                    html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${parseFloat(item.quantity).toFixed(2)}</td>`;
                    html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.location || 'غير محدد'}</td>`;
                    html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.expiry_date || 'غير محدد'}</td>`;
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">لا توجد بيانات مخزون</td></tr>';
            }
            
            html += '</tbody></table></div>';
            document.getElementById('productDetailsContent').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('productDetailsContent').innerHTML = '<div class="text-center py-8 text-red-600">حدث خطأ في تحميل البيانات</div>';
        });
}

function closeProductDetailsModal() {
    document.getElementById('productDetailsModal').classList.add('hidden');
}

function viewProductMovements(productId) {
    window.location.href = `{{ route('inventory.movements') }}?product_id=${productId}`;
}
</script>
@endpush
@endsection
