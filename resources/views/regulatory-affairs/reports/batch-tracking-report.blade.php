@extends('layouts.app')

@section('title', 'تقرير تتبع الدفعات - MaxCon ERP')
@section('page-title', 'تقرير تتبع الدفعات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">تقرير تتبع الدفعات الدوائية</h1>
                <p class="text-purple-100">مراقبة دورة حياة الدفعات من الإنتاج إلى الطرح</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-vials"></i>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-vials text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي الدفعات</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['total_batches'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الدفعات المطروحة</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['released_batches'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flask text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">قيد الفحص</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['testing_batches'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الدفعات المرفوضة</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['rejected_batches'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Status Distribution -->
    @if($batchesByStatus->count() > 0)
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">توزيع الدفعات حسب الحالة</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($batchesByStatus as $status)
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold 
                    @if($status->batch_status === 'released') text-green-600
                    @elseif($status->batch_status === 'testing') text-yellow-600
                    @elseif($status->batch_status === 'rejected') text-red-600
                    @else text-blue-600 @endif">
                    {{ $status->count }}
                </div>
                <div class="text-sm text-gray-600 mt-1">
                    @if($status->batch_status === 'released') مطروحة
                    @elseif($status->batch_status === 'testing') قيد الفحص
                    @elseif($status->batch_status === 'rejected') مرفوضة
                    @elseif($status->batch_status === 'quarantine') حجر صحي
                    @else {{ $status->batch_status }} @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Batches -->
    @if($recentBatches->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">الدفعات الأخيرة</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الإنتاج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentBatches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <i class="fas fa-vials text-purple-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $batch->lot_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $batch->product->display_trade_name }}</div>
                            <div class="text-sm text-gray-500">{{ $batch->product->display_generic_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $batch->product->company->display_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $batch->manufacturing_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="{{ $batch->isExpiring(30) ? 'text-orange-600 font-medium' : '' }}">
                                {{ $batch->expiry_date->format('Y-m-d') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($batch->batch_status === 'released') bg-green-100 text-green-800
                                @elseif($batch->batch_status === 'testing') bg-yellow-100 text-yellow-800
                                @elseif($batch->batch_status === 'rejected') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $batch->batch_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($batch->quantity_manufactured) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.batches.show', $batch) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Expiring Batches -->
    @if($expiringBatches->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-orange-50">
            <h3 class="text-lg font-semibold text-orange-900">الدفعات التي تنتهي خلال 90 يوم ({{ $expiringBatches->count() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">أيام متبقية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية المتبقية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($expiringBatches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-vials text-orange-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $batch->lot_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $batch->product->display_trade_name }}</div>
                            <div class="text-sm text-gray-500">{{ $batch->product->company->display_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 font-medium">
                            {{ $batch->expiry_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $daysLeft = $batch->expiry_date->diffInDays(now());
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($daysLeft <= 30) bg-red-100 text-red-800
                                @elseif($daysLeft <= 60) bg-orange-100 text-orange-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $daysLeft }} يوم
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($batch->quantity_manufactured - $batch->quantity_released) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.batches.show', $batch) }}" 
                               class="text-blue-600 hover:text-blue-900 ml-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button data-batch-id="{{ $batch->id }}"
                                    onclick="planDisposal(this.dataset.batchId)"
                                    class="text-orange-600 hover:text-orange-900">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- No Data -->
    @if($stats['total_batches'] == 0)
    <div class="bg-white rounded-lg p-12 card-shadow text-center">
        <div class="text-purple-500 mb-4">
            <i class="fas fa-vials text-6xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">لا توجد دفعات</h3>
        <p class="text-gray-600">لم يتم إنتاج أي دفعات دوائية بعد</p>
        <a href="{{ route('regulatory-affairs.batches.create') }}" 
           class="mt-4 inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <i class="fas fa-plus ml-2"></i>
            إضافة دفعة جديدة
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function planDisposal(batchId) {
    // This would open a modal to plan batch disposal
    alert('سيتم فتح نافذة تخطيط التخلص من الدفعة');
}
</script>
@endpush
@endsection
