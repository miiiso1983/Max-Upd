@extends('layouts.app')

@section('title', 'التقارير - MaxCon ERP')
@section('page-title', 'التقارير والتحليلات')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">التقارير والتحليلات</h1>
                <p class="text-green-100">تقارير شاملة لمتابعة الأداء واتخاذ القرارات</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-chart-bar"></i>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($reportCategories as $categoryKey => $category)
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-{{ $categoryKey === 'sales' ? 'blue' : 'green' }}-100 rounded-lg flex items-center justify-center ml-4">
                    <i class="fas fa-{{ $category['icon'] }} text-{{ $categoryKey === 'sales' ? 'blue' : 'green' }}-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $category['title'] }}</h3>
                    <p class="text-sm text-gray-600">{{ $category['description'] }}</p>
                </div>
            </div>
            
            <div class="space-y-2">
                @foreach($category['reports'] as $report)
                <a href="{{ $report['url'] }}" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">{{ $report['name'] }}</span>
                        <i class="fas fa-arrow-left text-gray-400"></i>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Quick Reports -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">تقارير سريعة</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-blue-50 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-blue-900">مبيعات اليوم</h4>
                    <i class="fas fa-calendar-day text-blue-600"></i>
                </div>
                <p class="text-2xl font-bold text-blue-900" id="today-sales">0 د.ع</p>
                <p class="text-sm text-blue-600">{{ now()->format('Y-m-d') }}</p>
            </div>
            
            <div class="p-4 bg-green-50 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-green-900">مبيعات هذا الشهر</h4>
                    <i class="fas fa-calendar-alt text-green-600"></i>
                </div>
                <p class="text-2xl font-bold text-green-900" id="month-sales">0 د.ع</p>
                <p class="text-sm text-green-600">{{ now()->format('F Y') }}</p>
            </div>
            
            <div class="p-4 bg-purple-50 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-purple-900">مبيعات هذا العام</h4>
                    <i class="fas fa-calendar text-purple-600"></i>
                </div>
                <p class="text-2xl font-bold text-purple-900" id="year-sales">0 د.ع</p>
                <p class="text-sm text-purple-600">{{ now()->format('Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">التقارير المحفوظة</h3>
            <button class="text-sm text-blue-600 hover:text-blue-800">عرض الكل</button>
        </div>
        
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center ml-3">
                        <i class="fas fa-chart-line text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">تقرير مبيعات الشهر الماضي</p>
                        <p class="text-sm text-gray-500">تم إنشاؤه في {{ now()->subDays(5)->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="flex space-x-2 space-x-reverse">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-800">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center ml-3">
                        <i class="fas fa-boxes text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">تقرير حالة المخزون</p>
                        <p class="text-sm text-gray-500">تم إنشاؤه في {{ now()->subDays(2)->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="flex space-x-2 space-x-reverse">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-800">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center ml-3">
                        <i class="fas fa-users text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">تقرير أداء العملاء</p>
                        <p class="text-sm text-gray-500">تم إنشاؤه في {{ now()->subDays(7)->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="flex space-x-2 space-x-reverse">
                    <button class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-800">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Report Builder -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">منشئ التقارير المخصصة</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التقرير</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>تقرير المبيعات</option>
                    <option>تقرير المخزون</option>
                    <option>تقرير العملاء</option>
                    <option>تقرير المنتجات</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الفترة الزمنية</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>اليوم</option>
                    <option>هذا الأسبوع</option>
                    <option>هذا الشهر</option>
                    <option>هذا العام</option>
                    <option>فترة مخصصة</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تنسيق التصدير</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>PDF</option>
                    <option>Excel</option>
                    <option>CSV</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex justify-end">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                <i class="fas fa-chart-bar ml-2"></i>
                إنشاء التقرير
            </button>
        </div>
    </div>

    <!-- Charts Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Trend Chart -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">اتجاه المبيعات</h3>
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">مخطط اتجاه المبيعات</p>
                    <p class="text-sm text-gray-400">سيتم عرض البيانات قريباً</p>
                </div>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">أكثر المنتجات مبيعاً</h3>
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-pie text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">مخطط المنتجات الأكثر مبيعاً</p>
                    <p class="text-sm text-gray-400">سيتم عرض البيانات قريباً</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadQuickStats();
});

function loadQuickStats() {
    // Load today's sales
    fetch('/api/sales/dashboard?period=day')
        .then(response => response.json())
        .then(data => {
            document.getElementById('today-sales').textContent = formatNumber(data.stats.total_sales || 0) + ' د.ع';
        })
        .catch(error => console.error('Error loading today sales:', error));

    // Load month's sales
    fetch('/api/sales/dashboard?period=month')
        .then(response => response.json())
        .then(data => {
            document.getElementById('month-sales').textContent = formatNumber(data.stats.total_sales || 0) + ' د.ع';
        })
        .catch(error => console.error('Error loading month sales:', error));

    // Load year's sales
    fetch('/api/sales/dashboard?period=year')
        .then(response => response.json())
        .then(data => {
            document.getElementById('year-sales').textContent = formatNumber(data.stats.total_sales || 0) + ' د.ع';
        })
        .catch(error => console.error('Error loading year sales:', error));
}

function formatNumber(number) {
    return new Intl.NumberFormat('ar-IQ').format(number);
}
</script>
@endpush
