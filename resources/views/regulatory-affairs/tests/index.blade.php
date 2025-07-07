@extends('layouts.app')

@section('title', 'الفحوصات الدوائية - MaxCon ERP')
@section('page-title', 'الفحوصات الدوائية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">الفحوصات الدوائية</h1>
                <p class="text-orange-100">إدارة ومتابعة فحوصات الجودة والسلامة</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.tests.create') }}" 
                   class="bg-white text-orange-600 px-4 py-2 rounded-lg hover:bg-orange-50 transition-colors font-medium">
                    <i class="fas fa-plus ml-2"></i>
                    فحص جديد
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-flask"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flask text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي الفحوصات</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">فحوصات ناجحة</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['passed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">فحوصات فاشلة</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">قيد التنفيذ</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">في الانتظار</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('regulatory-affairs.tests.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="البحث في الفحوصات..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الدفعة</label>
                <select name="batch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="">جميع الدفعات</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                            {{ $batch->batch_number }} - {{ $batch->product->display_trade_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع الفحص</label>
                <select name="test_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="">جميع الأنواع</option>
                    @foreach($testTypes as $type)
                        <option value="{{ $type }}" {{ request('test_type') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة السجل</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                    <option value="superseded" {{ request('status') === 'superseded' ? 'selected' : '' }}>محدث</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نتيجة الفحص</label>
                <select name="test_result" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="">جميع النتائج</option>
                    <option value="passed" {{ request('test_result') === 'passed' ? 'selected' : '' }}>نجح</option>
                    <option value="failed" {{ request('test_result') === 'failed' ? 'selected' : '' }}>فشل</option>
                    <option value="inconclusive" {{ request('test_result') === 'inconclusive' ? 'selected' : '' }}>غير حاسم</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Tests Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة الفحوصات الدوائية</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ البدء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النتيجة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tests as $test)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-flask text-orange-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900 font-mono">
                                        {{ $test->test_number }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $test->test_method }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $test->batch->batch_number }}
                            </div>
                            <div class="text-sm text-gray-500">{{ $test->batch->product->display_trade_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->display_test_type }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->test_date ? $test->test_date->format('Y-m-d') : 'غير محدد' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($test->test_completion_date)
                                {{ $test->test_completion_date->format('Y-m-d') }}
                            @else
                                <span class="text-gray-400">لم يكتمل</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($test->status === 'active') bg-green-100 text-green-800
                                @elseif($test->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($test->status === 'superseded') bg-yellow-100 text-yellow-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $test->status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($test->test_result)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($test->test_result === 'passed') bg-green-100 text-green-800
                                    @elseif($test->test_result === 'failed') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $test->test_result_arabic }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">في الانتظار</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <a href="{{ route('regulatory-affairs.tests.show', $test) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('regulatory-affairs.tests.edit', $test) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-test-id="{{ $test->id }}"
                                        onclick="updateStatus(this.dataset.testId)"
                                        class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-cog"></i>
                                </button>
                                @if($test->status === 'active' && $test->test_result && $test->test_result !== 'pending')
                                    <button data-test-id="{{ $test->id }}"
                                            onclick="generateReport(this.dataset.testId)"
                                            class="text-orange-600 hover:text-orange-900">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-flask text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد فحوصات دوائية</p>
                                <p class="text-sm">ابدأ بإضافة فحص دوائي جديد</p>
                                <a href="{{ route('regulatory-affairs.tests.create') }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                                    <i class="fas fa-plus ml-2"></i>
                                    إضافة فحص جديد
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tests->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $tests->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function updateStatus(testId) {
    // This would open a modal to update test status
    alert('سيتم فتح نافذة تحديث حالة الفحص');
}

function generateReport(testId) {
    // This would generate a PDF report for the test
    alert('سيتم إنشاء تقرير PDF للفحص');
}
</script>
@endpush
@endsection
