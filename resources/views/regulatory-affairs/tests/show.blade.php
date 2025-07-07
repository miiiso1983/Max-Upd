@extends('layouts.app')

@section('title', 'تفاصيل الفحص - ' . $test->test_name)
@section('page-title', 'تفاصيل الفحص')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $test->test_name }}</h1>
                <p class="text-orange-100">{{ $test->test_type }} - {{ $test->test_method }}</p>
                <div class="flex items-center mt-2 space-x-4 space-x-reverse">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($test->test_status === 'completed') bg-green-100 text-green-800
                        @elseif($test->test_status === 'in_progress') bg-yellow-100 text-yellow-800
                        @elseif($test->test_status === 'cancelled') bg-red-100 text-red-800
                        @else bg-blue-100 text-blue-800 @endif">
                        {{ $test->test_status_arabic }}
                    </span>
                    @if($test->test_result && $test->test_result !== 'pending')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($test->test_result === 'pass') bg-green-100 text-green-800
                            @elseif($test->test_result === 'fail') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $test->test_result_arabic }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.tests.edit', $test) }}" 
                   class="bg-white text-orange-600 px-4 py-2 rounded-lg hover:bg-orange-50 transition-colors">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-flask"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Test Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الفحص</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">اسم الفحص</label>
                        <p class="text-sm text-gray-900">{{ $test->test_name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">نوع الفحص</label>
                        <p class="text-sm text-gray-900">{{ $test->test_type }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">طريقة الفحص</label>
                        <p class="text-sm text-gray-900">{{ $test->test_method }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">المعامل المختبر</label>
                        <p class="text-sm text-gray-900">{{ $test->test_parameter }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الفحص</label>
                        <p class="text-sm text-gray-900">{{ $test->test_date->format('Y-m-d') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">المختبر</label>
                        <p class="text-sm text-gray-900">{{ $test->laboratory }}</p>
                    </div>
                </div>
            </div>

            <!-- Batch Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الدفعة</h3>
                
                <div class="flex items-center p-4 bg-purple-50 rounded-lg">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-vials text-purple-600 text-xl"></i>
                    </div>
                    <div class="mr-4 flex-1">
                        <h4 class="font-medium text-gray-900">{{ $test->batch->batch_number }}</h4>
                        <p class="text-sm text-gray-600">{{ $test->batch->product->display_trade_name }}</p>
                        <p class="text-xs text-gray-500">{{ $test->batch->product->company->display_name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $test->batch->lot_number }}</p>
                        <p class="text-xs text-gray-500">{{ $test->batch->manufacturing_date->format('Y-m-d') }}</p>
                    </div>
                </div>
            </div>

            <!-- Test Specifications -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">مواصفات الفحص</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">معايير القبول</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded font-mono">{{ $test->acceptance_criteria }}</p>
                    </div>

                    @if($test->actual_result)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">النتيجة الفعلية</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded font-mono">{{ $test->actual_result }}</p>
                    </div>
                    @endif

                    @if($test->test_notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-2">ملاحظات</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $test->test_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Test Personnel -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">فريق الفحص</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">المختبر</label>
                        <p class="text-sm text-gray-900">{{ $test->tested_by }}</p>
                    </div>
                    
                    @if($test->reviewed_by)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">المراجع</label>
                        <p class="text-sm text-gray-900">{{ $test->reviewed_by }}</p>
                    </div>
                    @endif
                    
                    @if($test->approved_by)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">المعتمد</label>
                        <p class="text-sm text-gray-900">{{ $test->approved_by }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
                
                <div class="space-y-3">
                    @if($test->test_status !== 'completed')
                    <button onclick="updateStatus()" 
                            class="block w-full bg-orange-600 text-white text-center py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-cog ml-2"></i>
                        تحديث الحالة
                    </button>
                    @endif
                    
                    @if($test->test_status === 'completed' && $test->test_result)
                    <button onclick="generateReport()" 
                            class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-file-pdf ml-2"></i>
                        إنشاء تقرير
                    </button>
                    @endif
                    
                    <a href="{{ route('regulatory-affairs.tests.edit', $test) }}" 
                       class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-edit ml-2"></i>
                        تعديل الفحص
                    </a>
                    
                    <a href="{{ route('regulatory-affairs.batches.show', $test->batch) }}" 
                       class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-vials ml-2"></i>
                        عرض الدفعة
                    </a>
                </div>
            </div>

            <!-- Test Status -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">حالة الفحص</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">الحالة الحالية</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($test->test_status === 'completed') bg-green-100 text-green-800
                            @elseif($test->test_status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($test->test_status === 'cancelled') bg-red-100 text-red-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ $test->test_status_arabic }}
                        </span>
                    </div>
                    
                    @if($test->test_result && $test->test_result !== 'pending')
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">النتيجة</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($test->test_result === 'pass') bg-green-100 text-green-800
                            @elseif($test->test_result === 'fail') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $test->test_result_arabic }}
                        </span>
                    </div>
                    @endif
                    
                    <div class="pt-4 border-t">
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-user ml-2"></i>
                            <span>أنشئ بواسطة: {{ $test->creator->name ?? 'غير محدد' }}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <i class="fas fa-calendar ml-2"></i>
                            <span>تاريخ الإنشاء: {{ $test->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Tests -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">فحوصات أخرى للدفعة</h3>
                    <a href="{{ route('regulatory-affairs.tests.index', ['batch_id' => $test->batch->id]) }}" 
                       class="text-orange-600 hover:text-orange-700 text-sm">عرض الكل</a>
                </div>
                
                <div class="space-y-3">
                    @forelse($test->batch->tests->where('id', '!=', $test->id)->take(3) as $relatedTest)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-flask text-orange-600 text-xs"></i>
                        </div>
                        <div class="mr-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $relatedTest->test_name }}</p>
                            <p class="text-xs text-gray-500">{{ $relatedTest->test_date->format('Y-m-d') }}</p>
                        </div>
                        @if($relatedTest->test_result && $relatedTest->test_result !== 'pending')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                @if($relatedTest->test_result === 'pass') bg-green-100 text-green-800
                                @elseif($relatedTest->test_result === 'fail') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $relatedTest->test_result_arabic }}
                            </span>
                        @endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">لا توجد فحوصات أخرى</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    @include('regulatory-affairs.components.document-upload', [
        'entityType' => 'test',
        'entityId' => $test->id,
        'entityName' => $test->test_name
    ])
</div>

@push('scripts')
<script>
function updateStatus() {
    // This would open a modal to update test status
    alert('سيتم فتح نافذة تحديث حالة الفحص');
}

function generateReport() {
    // This would generate a PDF report for the test
    alert('سيتم إنشاء تقرير PDF للفحص');
}
</script>
@endpush
@endsection
