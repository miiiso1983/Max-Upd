@extends('layouts.app')

@section('title', 'المخزون حسب المخزن - MaxCon ERP')
@section('page-title', 'المخزون حسب المخزن')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">المخزون حسب المخزن</h1>
                <p class="text-blue-100">عرض وإدارة المخزون في كل مخزن</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-warehouse"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('inventory.by-warehouse') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المخزن</label>
                <select name="warehouse_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                <select name="brand_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع العلامات</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name_ar ?: $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة المخزون</label>
                <select name="stock_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>متوفر</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>منخفض</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>نفد المخزون</option>
                    <option value="expiring" {{ request('stock_status') === 'expiring' ? 'selected' : '' }}>قارب على الانتهاء</option>
                    <option value="expired" {{ request('stock_status') === 'expired' ? 'selected' : '' }}>منتهي الصلاحية</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة المخزون</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المخزن</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المتاحة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المحجوزة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">متوسط التكلفة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">قيمة المخزون</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموقع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventory as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-box text-blue-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $item->product->name_ar ?: $item->product->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $item->product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->warehouse->name_ar ?: $item->warehouse->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-medium {{ $item->available_quantity <= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($item->available_quantity, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->reserved_quantity, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-medium">{{ number_format($item->quantity, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->average_cost, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-medium text-green-600">
                                {{ number_format($item->quantity * $item->average_cost, 0) }} د.ع
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->location ?: 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($item->expiry_date)
                                <span class="{{ $item->isExpired() ? 'text-red-600' : ($item->isExpiringSoon() ? 'text-orange-600' : 'text-gray-600') }}">
                                    {{ $item->expiry_date->format('Y-m-d') }}
                                </span>
                            @else
                                غير محدد
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <button data-item-id="{{ $item->id }}"
                                        onclick="adjustInventory(this.dataset.itemId)"
                                        class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button data-item-id="{{ $item->id }}"
                                        onclick="viewHistory(this.dataset.itemId)"
                                        class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-box-open text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد عناصر مخزون</p>
                                <p class="text-sm">لا توجد منتجات تطابق المعايير المحددة</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($inventory->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $inventory->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Adjust Inventory Modal -->
<div id="adjustModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">تعديل كمية المخزون</h3>
            <form id="adjustForm">
                @csrf
                <input type="hidden" id="inventory_id" name="inventory_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الكمية الجديدة</label>
                        <input type="number" id="new_quantity" name="new_quantity" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">السبب</label>
                        <input type="text" id="reason" name="reason" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 space-x-reverse mt-6">
                    <button type="button" onclick="closeAdjustModal()" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                        إلغاء
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        حفظ التعديل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function adjustInventory(inventoryId) {
    document.getElementById('inventory_id').value = inventoryId;
    document.getElementById('adjustModal').classList.remove('hidden');
}

function closeAdjustModal() {
    document.getElementById('adjustModal').classList.add('hidden');
    document.getElementById('adjustForm').reset();
}

function viewHistory(inventoryId) {
    // Redirect to movements page with filter
    window.location.href = `{{ route('inventory.movements') }}?inventory_id=${inventoryId}`;
}

document.getElementById('adjustForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("inventory.adjust") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم تعديل المخزون بنجاح');
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء التعديل');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء التعديل');
    });
});
</script>
@endpush
@endsection
