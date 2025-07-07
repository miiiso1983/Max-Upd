@extends('layouts.app')

@section('title', 'حركات المخزون - MaxCon ERP')
@section('page-title', 'حركات المخزون')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-red-600 to-orange-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">حركات المخزون</h1>
                <p class="text-red-100">تتبع جميع حركات الدخول والخروج والنقل</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-history"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('inventory.movements') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
            </div>
            
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
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع الحركة</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                    <option value="">جميع الأنواع</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>وارد</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>صادر</option>
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>نقل</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>تسوية</option>
                    <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>مرتجع</option>
                    <option value="damage" {{ request('type') === 'damage' ? 'selected' : '' }}>تالف</option>
                    <option value="loss" {{ request('type') === 'loss' ? 'selected' : '' }}>فقدان</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">مصدر الحركة</label>
                <select name="source_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                    <option value="">جميع المصادر</option>
                    <option value="purchase" {{ request('source_type') === 'purchase' ? 'selected' : '' }}>مشتريات</option>
                    <option value="sale" {{ request('source_type') === 'sale' ? 'selected' : '' }}>مبيعات</option>
                    <option value="transfer" {{ request('source_type') === 'transfer' ? 'selected' : '' }}>نقل</option>
                    <option value="adjustment" {{ request('source_type') === 'adjustment' ? 'selected' : '' }}>تسوية</option>
                    <option value="return" {{ request('source_type') === 'return' ? 'selected' : '' }}>مرتجع</option>
                    <option value="production" {{ request('source_type') === 'production' ? 'selected' : '' }}>إنتاج</option>
                    <option value="manual" {{ request('source_type') === 'manual' ? 'selected' : '' }}>يدوي</option>
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

    <!-- Movements Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة حركات المخزون</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم المرجع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المخزن</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع الحركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تكلفة الوحدة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي التكلفة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الحركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السبب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $movement->reference_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-box text-gray-600 text-xs"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $movement->product->name_ar ?: $movement->product->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $movement->product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $movement->warehouse->name_ar ?: $movement->warehouse->name }}
                            @if($movement->type === 'transfer' && $movement->fromWarehouse)
                                <div class="text-xs text-gray-500">
                                    من: {{ $movement->fromWarehouse->name_ar ?: $movement->fromWarehouse->name }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($movement->type === 'in') bg-green-100 text-green-800
                                @elseif($movement->type === 'out') bg-red-100 text-red-800
                                @elseif($movement->type === 'transfer') bg-blue-100 text-blue-800
                                @elseif($movement->type === 'adjustment') bg-yellow-100 text-yellow-800
                                @elseif($movement->type === 'return') bg-purple-100 text-purple-800
                                @elseif($movement->type === 'damage') bg-orange-100 text-orange-800
                                @else bg-gray-100 text-gray-800 @endif">
                                <i class="fas 
                                    @if($movement->type === 'in') fa-arrow-down
                                    @elseif($movement->type === 'out') fa-arrow-up
                                    @elseif($movement->type === 'transfer') fa-exchange-alt
                                    @elseif($movement->type === 'adjustment') fa-edit
                                    @elseif($movement->type === 'return') fa-undo
                                    @elseif($movement->type === 'damage') fa-exclamation-triangle
                                    @else fa-question @endif ml-1"></i>
                                {{ $movement->type_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                            @if($movement->type === 'in') text-green-600
                            @elseif($movement->type === 'out') text-red-600
                            @else text-blue-600 @endif">
                            {{ $movement->type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($movement->unit_cost, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($movement->total_cost, 0) }} د.ع
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movement->movement_date->format('Y-m-d') }}
                            @if($movement->movement_time)
                                <div class="text-xs">{{ $movement->movement_time->format('H:i') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ Str::limit($movement->reason, 30) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($movement->status === 'completed') bg-green-100 text-green-800
                                @elseif($movement->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $movement->status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <button data-movement-id="{{ $movement->id }}"
                                        onclick="viewMovementDetails(this.dataset.movementId)"
                                        class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($movement->status === 'pending')
                                <button data-movement-id="{{ $movement->id }}"
                                        onclick="approveMovement(this.dataset.movementId)"
                                        class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button data-movement-id="{{ $movement->id }}"
                                        onclick="cancelMovement(this.dataset.movementId)"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-history text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد حركات مخزون</p>
                                <p class="text-sm">لا توجد حركات تطابق المعايير المحددة</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($movements->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Movement Details Modal -->
<div id="movementDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">تفاصيل حركة المخزون</h3>
                <button onclick="closeMovementDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="movementDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewMovementDetails(movementId) {
    document.getElementById('movementDetailsModal').classList.remove('hidden');
    document.getElementById('movementDetailsContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
    
    // Here you would fetch movement details via AJAX
    // For now, we'll show a placeholder
    setTimeout(() => {
        document.getElementById('movementDetailsContent').innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">رقم المرجع</label>
                        <p class="text-sm text-gray-900">REF-${movementId}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">تاريخ الحركة</label>
                        <p class="text-sm text-gray-900">${new Date().toLocaleDateString('ar-EG')}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">الملاحظات</label>
                    <p class="text-sm text-gray-900">تفاصيل إضافية حول الحركة...</p>
                </div>
            </div>
        `;
    }, 1000);
}

function closeMovementDetailsModal() {
    document.getElementById('movementDetailsModal').classList.add('hidden');
}

function approveMovement(movementId) {
    if (confirm('هل أنت متأكد من اعتماد هذه الحركة؟')) {
        // Here you would send AJAX request to approve movement
        alert('تم اعتماد الحركة بنجاح');
        location.reload();
    }
}

function cancelMovement(movementId) {
    if (confirm('هل أنت متأكد من إلغاء هذه الحركة؟')) {
        // Here you would send AJAX request to cancel movement
        alert('تم إلغاء الحركة بنجاح');
        location.reload();
    }
}
</script>
@endpush
@endsection
