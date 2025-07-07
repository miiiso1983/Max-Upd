@extends('layouts.app')

@section('title', 'تقرير الفحوصات - MaxCon ERP')
@section('page-title', 'تقرير الفحوصات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">تقرير الفحوصات الدوائية</h1>
                <p class="text-orange-100">مراقبة وتحليل نتائج فحوصات الجودة والسلامة</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-flask"></i>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flask text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي الفحوصات</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_tests'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الفحوصات الناجحة</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['passed_tests'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الفحوصات الفاشلة</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['failed_tests'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">فحوصات في الانتظار</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_tests'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Rate Chart -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">معدل نجاح الفحوصات</h3>
        
        <div class="flex items-center justify-center">
            @php
                $totalTests = $stats['total_tests'];
                $passedTests = $stats['passed_tests'];
                $failedTests = $stats['failed_tests'];
                $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
            @endphp
            
            <div class="relative w-32 h-32">
                <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-gray-300" stroke="currentColor" stroke-width="3" fill="none"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path class="text-green-600" stroke="currentColor" stroke-width="3" fill="none"
                          stroke-dasharray="{{ $successRate }}, 100"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-green-600">{{ $successRate }}%</span>
                </div>
            </div>
            
            <div class="mr-8">
                <div class="flex items-center mb-2">
                    <div class="w-4 h-4 bg-green-600 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">ناجح: {{ $passedTests }}</span>
                </div>
                <div class="flex items-center mb-2">
                    <div class="w-4 h-4 bg-red-600 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">فاشل: {{ $failedTests }}</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-yellow-600 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">في الانتظار: {{ $stats['pending_tests'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tests by Type -->
    @if($testsByType->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">الفحوصات حسب النوع</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ناجح</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">فاشل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">معدل النجاح</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($testsByType as $testType)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $testType->test_type }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $testType->count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            {{ $testType->passed }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                            {{ $testType->failed }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $rate = $testType->count > 0 ? round(($testType->passed / $testType->count) * 100, 1) : 0;
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($rate >= 90) bg-green-100 text-green-800
                                @elseif($rate >= 70) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $rate }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Recent Tests -->
    @if($recentTests->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">الفحوصات الأخيرة</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النتيجة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentTests as $test)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-flask text-orange-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $test->test_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $test->test_type }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->batch->batch_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $test->batch->product->display_trade_name }}</div>
                            <div class="text-sm text-gray-500">{{ $test->batch->product->company->display_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->test_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($test->test_result && $test->test_result !== 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($test->test_result === 'pass') bg-green-100 text-green-800
                                    @elseif($test->test_result === 'fail') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $test->test_result_arabic }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">في الانتظار</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.tests.show', $test) }}" 
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

    <!-- Failed Tests -->
    @if($failedTests->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h3 class="text-lg font-semibold text-red-900">الفحوصات الفاشلة ({{ $failedTests->count() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السبب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($failedTests as $test)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-flask text-red-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $test->test_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $test->test_type }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->batch->batch_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $test->batch->product->display_trade_name }}</div>
                            <div class="text-sm text-gray-500">{{ $test->batch->product->company->display_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->test_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->actual_result ?? 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.tests.show', $test) }}" 
                               class="text-blue-600 hover:text-blue-900 ml-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button data-test-id="{{ $test->id }}"
                                    onclick="investigateFailure(this.dataset.testId)"
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-search"></i>
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
    @if($stats['total_tests'] == 0)
    <div class="bg-white rounded-lg p-12 card-shadow text-center">
        <div class="text-orange-500 mb-4">
            <i class="fas fa-flask text-6xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">لا توجد فحوصات</h3>
        <p class="text-gray-600">لم يتم إجراء أي فحوصات دوائية بعد</p>
        <a href="{{ route('regulatory-affairs.tests.create') }}" 
           class="mt-4 inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
            <i class="fas fa-plus ml-2"></i>
            إضافة فحص جديد
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function investigateFailure(testId) {
    // This would open a modal to investigate test failure
    alert('سيتم فتح نافذة تحقيق في فشل الفحص');
}
</script>
@endpush
@endsection
