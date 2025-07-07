@extends('layouts.app')

@section('title', 'تقرير المنتجات منتهية الصلاحية - MaxCon ERP')
@section('page-title', 'تقرير المنتجات منتهية الصلاحية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-red-600 to-pink-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">تقرير المنتجات منتهية الصلاحية</h1>
                <p class="text-red-100">المنتجات التي انتهت أو ستنتهي صلاحيتها قريباً</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('inventory.expiring') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المخزن</label>
                <select name="warehouse_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
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
                <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
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
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">فترة الانتهاء (أيام)</label>
                <select name="days" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                    <option value="7" {{ ($days ?? 30) == 7 ? 'selected' : '' }}>خلال 7 أيام</option>
                    <option value="15" {{ ($days ?? 30) == 15 ? 'selected' : '' }}>خلال 15 يوم</option>
                    <option value="30" {{ ($days ?? 30) == 30 ? 'selected' : '' }}>خلال 30 يوم</option>
                    <option value="60" {{ ($days ?? 30) == 60 ? 'selected' : '' }}>خلال 60 يوم</option>
                    <option value="90" {{ ($days ?? 30) == 90 ? 'selected' : '' }}>خلال 90 يوم</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Expiring Items Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">المنتجات منتهية الصلاحية</h3>
            <div class="flex items-center space-x-2 space-x-reverse">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <i class="fas fa-clock ml-1"></i>
                    {{ $expiringItems->total() }} منتج
                </span>
                <button onclick="exportExpiring()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الأيام المتبقية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">متوسط التكلفة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">قيمة المخزون</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expiringItems as $item)
                    @php
                        $daysToExpiry = $item->expiry_date ? now()->diffInDays($item->expiry_date, false) : null;
                        $isExpired = $daysToExpiry !== null && $daysToExpiry < 0;
                        $isExpiringSoon = $daysToExpiry !== null && $daysToExpiry >= 0 && $daysToExpiry <= 7;
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isExpired ? 'bg-red-50' : ($isExpiringSoon ? 'bg-orange-50' : '') }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg {{ $isExpired ? 'bg-red-100' : ($isExpiringSoon ? 'bg-orange-100' : 'bg-yellow-100') }} flex items-center justify-center">
                                    <i class="fas fa-clock {{ $isExpired ? 'text-red-600' : ($isExpiringSoon ? 'text-orange-600' : 'text-yellow-600') }}"></i>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($item->quantity, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($daysToExpiry !== null)
                                <span class="{{ $isExpired ? 'text-red-600' : ($isExpiringSoon ? 'text-orange-600' : 'text-yellow-600') }}">
                                    @if($isExpired)
                                        منتهي منذ {{ abs($daysToExpiry) }} يوم
                                    @else
                                        {{ $daysToExpiry }} يوم
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-500">غير محدد</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->batch_number ?: 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->average_cost, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                            {{ number_format($item->quantity * $item->average_cost, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($isExpired)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle ml-1"></i>
                                    منتهي الصلاحية
                                </span>
                            @elseif($isExpiringSoon)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-exclamation-triangle ml-1"></i>
                                    ينتهي قريباً
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock ml-1"></i>
                                    تحت المراقبة
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                @if($isExpired)
                                    <button data-item-id="{{ $item->id }}"
                                            onclick="markAsDamaged(this.dataset.itemId)"
                                            class="text-red-600 hover:text-red-900" title="تحويل إلى تالف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                                <button data-product-id="{{ $item->product->id }}" data-warehouse-id="{{ $item->warehouse->id }}"
                                        onclick="createDiscount(this.dataset.productId, this.dataset.warehouseId)"
                                        class="text-blue-600 hover:text-blue-900" title="إنشاء عرض خصم">
                                    <i class="fas fa-percentage"></i>
                                </button>
                                <button data-item-id="{{ $item->id }}"
                                        onclick="transferToOutlet(this.dataset.itemId)"
                                        class="text-green-600 hover:text-green-900" title="نقل لمنفذ بيع">
                                    <i class="fas fa-exchange-alt"></i>
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
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                                <p class="text-lg font-medium">ممتاز! لا توجد منتجات منتهية الصلاحية</p>
                                <p class="text-sm">جميع المنتجات ضمن فترة الصلاحية المحددة</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($expiringItems->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $expiringItems->links() }}
        </div>
        @endif
    </div>

    <!-- Summary Statistics -->
    @if($expiringItems->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">منتهي الصلاحية</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $expiringItems->filter(function($item) {
                            return $item->expiry_date && now()->diffInDays($item->expiry_date, false) < 0;
                        })->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">ينتهي خلال 7 أيام</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ $expiringItems->filter(function($item) {
                            $days = $item->expiry_date ? now()->diffInDays($item->expiry_date, false) : null;
                            return $days !== null && $days >= 0 && $days <= 7;
                        })->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">قيمة المخزون المتأثر</p>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ number_format($expiringItems->sum(function($item) {
                            return $item->quantity * $item->average_cost;
                        }), 0) }} د.ع
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-warehouse text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">المخازن المتأثرة</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $expiringItems->pluck('warehouse_id')->unique()->count() }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function markAsDamaged(inventoryId) {
    if (confirm('هل تريد تحويل هذا المنتج إلى تالف؟')) {
        // Here you would send AJAX request to mark as damaged
        alert('تم تحويل المنتج إلى تالف');
        location.reload();
    }
}

function createDiscount(productId, warehouseId) {
    if (confirm('هل تريد إنشاء عرض خصم لهذا المنتج؟')) {
        // Redirect to discount creation
        window.location.href = `/sales/discounts/create?product_id=${productId}&warehouse_id=${warehouseId}`;
    }
}

function transferToOutlet(inventoryId) {
    if (confirm('هل تريد نقل هذا المنتج إلى منفذ بيع؟')) {
        // Here you would open transfer modal or redirect
        alert('سيتم فتح نافذة النقل');
    }
}

function viewHistory(productId, warehouseId) {
    window.location.href = `{{ route('inventory.movements') }}?product_id=${productId}&warehouse_id=${warehouseId}`;
}

function exportExpiring() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.location.href = `{{ route('inventory.expiring') }}?${params.toString()}`;
}
</script>
@endpush
@endsection
