@extends('layouts.app')

@section('title', 'إدارة المخازن - MaxCon ERP')
@section('page-title', 'إدارة المخازن')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">إدارة المخازن المتعددة</h1>
                <p class="text-green-100">إدارة وتتبع جميع المخازن والمواقع</p>
            </div>
            <div class="flex items-center space-x-4 space-x-reverse">
                <a href="{{ route('inventory.warehouses.create') }}" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 transition-colors">
                    <i class="fas fa-plus ml-2"></i>
                    مخزن جديد
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المخازن</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_warehouses'] ?? 0 }}</p>
                    <p class="text-xs text-blue-600 flex items-center mt-1">
                        <i class="fas fa-warehouse ml-1"></i>
                        مخزن
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-warehouse text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">المخازن النشطة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_warehouses'] ?? 0 }}</p>
                    <p class="text-xs text-green-600 flex items-center mt-1">
                        <i class="fas fa-check-circle ml-1"></i>
                        نشط
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">المخازن الرئيسية</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['main_warehouses'] ?? 0 }}</p>
                    <p class="text-xs text-purple-600 flex items-center mt-1">
                        <i class="fas fa-star ml-1"></i>
                        رئيسي
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي السعة</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_capacity'] ?? 0, 0) }}</p>
                    <p class="text-xs text-orange-600 flex items-center mt-1">
                        <i class="fas fa-cube ml-1"></i>
                        متر مكعب
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cube text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">الاستخدام الحالي</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_utilization'] ?? 0, 0) }}</p>
                    <p class="text-xs text-red-600 flex items-center mt-1">
                        <i class="fas fa-chart-pie ml-1"></i>
                        متر مكعب
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-pie text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('inventory.warehouses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="البحث في المخازن..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">النوع</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">جميع الأنواع</option>
                    <option value="main" {{ request('type') === 'main' ? 'selected' : '' }}>رئيسي</option>
                    <option value="branch" {{ request('type') === 'branch' ? 'selected' : '' }}>فرعي</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Warehouses Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة المخازن</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المخزن</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكود</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المدير</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الموقع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاستخدام</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($warehouses as $warehouse)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $warehouse->is_main ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                                    <i class="fas {{ $warehouse->is_main ? 'fa-star' : 'fa-warehouse' }}"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $warehouse->name_ar ?: $warehouse->name }}
                                    </div>
                                    @if($warehouse->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($warehouse->description, 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ $warehouse->code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ optional($warehouse->manager)->name ?: 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $warehouse->city ? $warehouse->city . ', ' . $warehouse->governorate : 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $warehouse->capacity ? number_format($warehouse->capacity, 0) . ' م³' : 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($warehouse->capacity > 0)
                                @php
                                    $percentage = ($warehouse->current_utilization / $warehouse->capacity) * 100;
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full warehouse-utilization-bar" data-width="{{ min($percentage, 100) }}"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ number_format($percentage, 1) }}%</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-500">غير محدد</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $warehouse->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <a href="{{ route('inventory.warehouses.show', $warehouse) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('inventory.warehouses.edit', $warehouse) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-warehouse-id="{{ $warehouse->id }}"
                                        onclick="deleteWarehouse(this.dataset.warehouseId)"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-warehouse text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد مخازن</p>
                                <p class="text-sm">ابدأ بإضافة مخزن جديد</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($warehouses->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $warehouses->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function deleteWarehouse(id) {
    if (confirm('هل أنت متأكد من حذف هذا المخزن؟')) {
        fetch(`/inventory/warehouses/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'حدث خطأ أثناء الحذف');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء الحذف');
        });
    }
}

// Set warehouse utilization bar widths
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.warehouse-utilization-bar').forEach(function(element) {
        const width = element.dataset.width;
        element.style.width = width + '%';
    });
});
</script>
@endpush
@endsection
