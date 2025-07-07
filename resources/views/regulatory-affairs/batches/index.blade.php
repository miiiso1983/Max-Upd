@extends('layouts.app')

@section('title', 'الدفعات الدوائية - MaxCon ERP')
@section('page-title', 'الدفعات الدوائية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">الدفعات الدوائية</h1>
                <p class="text-purple-100">إدارة ومتابعة دفعات الإنتاج والفحوصات</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.batches.create') }}" 
                   class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-purple-50 transition-colors font-medium">
                    <i class="fas fa-plus ml-2"></i>
                    دفعة جديدة
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-vials"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-vials text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي الدفعات</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">دفعات مطروحة</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['released'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تحت الفحص</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_testing'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">دفعات مرفوضة</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تنتهي قريباً</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['expiring'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('regulatory-affairs.batches.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="البحث في الدفعات..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">المنتج</label>
                <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">جميع المنتجات</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->display_trade_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة الدفعة</label>
                <select name="batch_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">جميع الحالات</option>
                    <option value="in_production" {{ request('batch_status') === 'in_production' ? 'selected' : '' }}>قيد الإنتاج</option>
                    <option value="testing" {{ request('batch_status') === 'testing' ? 'selected' : '' }}>تحت الفحص</option>
                    <option value="released" {{ request('batch_status') === 'released' ? 'selected' : '' }}>مطروح</option>
                    <option value="rejected" {{ request('batch_status') === 'rejected' ? 'selected' : '' }}>مرفوض</option>
                    <option value="recalled" {{ request('batch_status') === 'recalled' ? 'selected' : '' }}>مسحوب</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة الفحص</label>
                <select name="testing_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">جميع حالات الفحص</option>
                    <option value="pending" {{ request('testing_status') === 'pending' ? 'selected' : '' }}>في الانتظار</option>
                    <option value="in_progress" {{ request('testing_status') === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                    <option value="passed" {{ request('testing_status') === 'passed' ? 'selected' : '' }}>نجح</option>
                    <option value="failed" {{ request('testing_status') === 'failed' ? 'selected' : '' }}>فشل</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الانتهاء</label>
                <select name="expiry_filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="">جميع التواريخ</option>
                    <option value="expired" {{ request('expiry_filter') === 'expired' ? 'selected' : '' }}>منتهية الصلاحية</option>
                    <option value="expiring_30" {{ request('expiry_filter') === 'expiring_30' ? 'selected' : '' }}>تنتهي خلال 30 يوم</option>
                    <option value="expiring_90" {{ request('expiry_filter') === 'expiring_90' ? 'selected' : '' }}>تنتهي خلال 90 يوم</option>
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

    <!-- Batches Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة الدفعات الدوائية</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الإنتاج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <i class="fas fa-vials text-purple-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900 font-mono">
                                        {{ $batch->batch_number }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $batch->lot_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $batch->product->display_trade_name }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $batch->product->company->display_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $batch->manufacturing_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="{{ $batch->isExpiring(30) ? 'text-orange-600 font-medium' : '' }}">
                                {{ $batch->expiry_date->format('Y-m-d') }}
                            </span>
                            @if($batch->isExpiring(30))
                                <div class="text-xs text-orange-600">
                                    ينتهي خلال {{ $batch->getDaysUntilExpiry() }} يوم
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>المنتج: {{ number_format($batch->quantity_manufactured) }}</div>
                            <div class="text-xs text-gray-500">المطروح: {{ number_format($batch->quantity_released) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($batch->batch_status === 'released') bg-green-100 text-green-800
                                @elseif($batch->batch_status === 'testing') bg-yellow-100 text-yellow-800
                                @elseif($batch->batch_status === 'rejected') bg-red-100 text-red-800
                                @elseif($batch->batch_status === 'recalled') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $batch->batch_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($batch->testing_status === 'passed') bg-green-100 text-green-800
                                @elseif($batch->testing_status === 'failed') bg-red-100 text-red-800
                                @elseif($batch->testing_status === 'in_progress') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $batch->testing_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <a href="{{ route('regulatory-affairs.batches.show', $batch) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('regulatory-affairs.batches.edit', $batch) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-batch-id="{{ $batch->id }}"
                                        onclick="updateStatus(this.dataset.batchId)"
                                        class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <a href="{{ route('regulatory-affairs.tests.index', ['batch_id' => $batch->id]) }}" 
                                   class="text-orange-600 hover:text-orange-900">
                                    <i class="fas fa-flask"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-vials text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد دفعات دوائية</p>
                                <p class="text-sm">ابدأ بإضافة دفعة دوائية جديدة</p>
                                <a href="{{ route('regulatory-affairs.batches.create') }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    <i class="fas fa-plus ml-2"></i>
                                    إضافة دفعة جديدة
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($batches->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $batches->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function updateStatus(batchId) {
    // This would open a modal to update batch status
    alert('سيتم فتح نافذة تحديث حالة الدفعة');
}
</script>
@endpush
@endsection
