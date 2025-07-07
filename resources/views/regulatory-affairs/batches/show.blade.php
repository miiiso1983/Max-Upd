@extends('layouts.app')

@section('title', 'تفاصيل الدفعة - ' . $batch->batch_number)
@section('page-title', 'تفاصيل الدفعة')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $batch->batch_number }}</h1>
                <p class="text-purple-100">{{ $batch->product->display_trade_name }} - {{ $batch->lot_number }}</p>
                <div class="flex items-center mt-2 space-x-4 space-x-reverse">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($batch->batch_status === 'released') bg-green-100 text-green-800
                        @elseif($batch->batch_status === 'testing') bg-yellow-100 text-yellow-800
                        @elseif($batch->batch_status === 'rejected') bg-red-100 text-red-800
                        @else bg-blue-100 text-blue-800 @endif">
                        {{ $batch->batch_status_arabic }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($batch->testing_status === 'passed') bg-green-100 text-green-800
                        @elseif($batch->testing_status === 'failed') bg-red-100 text-red-800
                        @elseif($batch->testing_status === 'in_progress') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $batch->testing_status_arabic }}
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.batches.edit', $batch) }}" 
                   class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-purple-50 transition-colors">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-vials"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @php
        $alerts = [];
        if($batch->isExpiring(30)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'تنتهي صلاحية هذه الدفعة خلال ' . $batch->getDaysUntilExpiry() . ' يوم'
            ];
        }
        if($batch->batch_status === 'testing' && $batch->testing_start_date && $batch->testing_start_date->addDays(7)->isPast()) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'الفحوصات قيد التنفيذ منذ أكثر من أسبوع'
            ];
        }
    @endphp

    @if(count($alerts) > 0)
    <div class="space-y-3">
        @foreach($alerts as $alert)
        <div class="p-4 rounded-lg border-r-4 
            @if($alert['type'] === 'warning') bg-orange-50 border-orange-500 text-orange-800
            @elseif($alert['type'] === 'danger') bg-red-50 border-red-500 text-red-800
            @else bg-blue-50 border-blue-500 text-blue-800 @endif">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle ml-3"></i>
                <span>{{ $alert['message'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Batch Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الدفعة</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">رقم الدفعة</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $batch->batch_number }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">رقم اللوط</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $batch->lot_number }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الإنتاج</label>
                        <p class="text-sm text-gray-900">{{ $batch->manufacturing_date->format('Y-m-d') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الانتهاء</label>
                        <p class="text-sm text-gray-900 {{ $batch->isExpiring(30) ? 'text-orange-600 font-medium' : '' }}">
                            {{ $batch->expiry_date->format('Y-m-d') }}
                            @if($batch->isExpiring(30))
                                <span class="text-xs">(ينتهي خلال {{ $batch->getDaysUntilExpiry() }} يوم)</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">موقع التصنيع</label>
                        <p class="text-sm text-gray-900">{{ $batch->manufacturing_site }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الكمية المنتجة</label>
                        <p class="text-sm text-gray-900">{{ number_format($batch->quantity_manufactured) }} وحدة</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الكمية المطروحة</label>
                        <p class="text-sm text-gray-900">{{ number_format($batch->quantity_released) }} وحدة</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الكمية المتبقية</label>
                        <p class="text-sm text-gray-900">{{ number_format($batch->quantity_manufactured - $batch->quantity_released) }} وحدة</p>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات المنتج</h3>
                
                <div class="flex items-center p-4 bg-green-50 rounded-lg">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-pills text-green-600 text-xl"></i>
                    </div>
                    <div class="mr-4 flex-1">
                        <h4 class="font-medium text-gray-900">{{ $batch->product->display_trade_name }}</h4>
                        <p class="text-sm text-gray-600">{{ $batch->product->display_generic_name }}</p>
                        <p class="text-xs text-gray-500">{{ $batch->product->display_active_ingredient }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $batch->product->company->display_name }}</p>
                        <p class="text-xs text-gray-500">{{ $batch->product->registration_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Release Information -->
            @if($batch->batch_status === 'released')
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الطرح</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($batch->testing_start_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ بدء الفحوصات</label>
                        <p class="text-sm text-gray-900">{{ $batch->testing_start_date->format('Y-m-d') }}</p>
                    </div>
                    @endif
                    
                    @if($batch->testing_completion_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ انتهاء الفحوصات</label>
                        <p class="text-sm text-gray-900">{{ $batch->testing_completion_date->format('Y-m-d') }}</p>
                    </div>
                    @endif
                    
                    @if($batch->release_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الطرح</label>
                        <p class="text-sm text-gray-900">{{ $batch->release_date->format('Y-m-d') }}</p>
                    </div>
                    @endif
                    
                    @if($batch->released_by)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">مطروح بواسطة</label>
                        <p class="text-sm text-gray-900">{{ $batch->released_by }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Tests -->
            @if($batch->tests && $batch->tests->count() > 0)
            <div class="bg-white rounded-lg p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">الفحوصات</h3>
                    <a href="{{ route('regulatory-affairs.tests.create', ['batch_id' => $batch->id]) }}" 
                       class="text-orange-600 hover:text-orange-700 text-sm">
                        <i class="fas fa-plus ml-1"></i>
                        إضافة فحص
                    </a>
                </div>
                
                <div class="space-y-3">
                    @foreach($batch->tests as $test)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-flask text-orange-600"></i>
                        </div>
                        <div class="mr-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $test->test_name }}</p>
                            <p class="text-xs text-gray-500">{{ $test->test_type }} - {{ $test->test_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="text-right">
                            @if($test->test_result && $test->test_result !== 'pending')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($test->test_result === 'pass') bg-green-100 text-green-800
                                    @elseif($test->test_result === 'fail') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $test->test_result_arabic }}
                                </span>
                            @else
                                <span class="text-xs text-gray-500">في الانتظار</span>
                            @endif
                        </div>
                        <div class="mr-3">
                            <a href="{{ route('regulatory-affairs.tests.show', $test) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('regulatory-affairs.tests.create', ['batch_id' => $batch->id]) }}" 
                       class="block w-full bg-orange-600 text-white text-center py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-flask ml-2"></i>
                        إضافة فحص
                    </a>
                    
                    @if($batch->batch_status !== 'released')
                    <button onclick="updateStatus()" 
                            class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-cog ml-2"></i>
                        تحديث الحالة
                    </button>
                    @endif
                    
                    <a href="{{ route('regulatory-affairs.batches.edit', $batch) }}" 
                       class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-edit ml-2"></i>
                        تعديل الدفعة
                    </a>
                    
                    <a href="{{ route('regulatory-affairs.products.show', $batch->product) }}" 
                       class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-pills ml-2"></i>
                        عرض المنتج
                    </a>
                </div>
            </div>

            <!-- Batch Statistics -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">إحصائيات الدفعة</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">نسبة الطرح</span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ $batch->quantity_manufactured > 0 ? round(($batch->quantity_released / $batch->quantity_manufactured) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">عدد الفحوصات</span>
                        <span class="text-sm font-medium text-gray-900">{{ $batch->tests->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">الفحوصات الناجحة</span>
                        <span class="text-sm font-medium text-green-600">{{ $batch->tests->where('test_result', 'pass')->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">الفحوصات الفاشلة</span>
                        <span class="text-sm font-medium text-red-600">{{ $batch->tests->where('test_result', 'fail')->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">أيام حتى الانتهاء</span>
                        <span class="text-sm font-medium {{ $batch->isExpiring(30) ? 'text-orange-600' : 'text-gray-900' }}">
                            {{ $batch->getDaysUntilExpiry() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">الجدول الزمني</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">تاريخ الإنتاج</p>
                            <p class="text-xs text-gray-500">{{ $batch->manufacturing_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    
                    @if($batch->testing_start_date)
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">بدء الفحوصات</p>
                            <p class="text-xs text-gray-500">{{ $batch->testing_start_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($batch->testing_completion_date)
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">انتهاء الفحوصات</p>
                            <p class="text-xs text-gray-500">{{ $batch->testing_completion_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($batch->release_date)
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">تاريخ الطرح</p>
                            <p class="text-xs text-gray-500">{{ $batch->release_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-gray-900">تاريخ الانتهاء</p>
                            <p class="text-xs text-gray-500">{{ $batch->expiry_date->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    @include('regulatory-affairs.components.document-upload', [
        'entityType' => 'batch',
        'entityId' => $batch->id,
        'entityName' => $batch->batch_number
    ])
</div>

@push('scripts')
<script>
function updateStatus() {
    // This would open a modal to update batch status
    alert('سيتم فتح نافذة تحديث حالة الدفعة');
}
</script>
@endpush
@endsection
