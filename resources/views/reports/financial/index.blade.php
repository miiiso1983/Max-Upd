@extends('layouts.app')

@section('page-title', 'التقارير المالية')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-6 text-white">
        <div class="flex items-center space-x-4 space-x-reverse">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold">مرحباً بك في التقارير المالية</h1>
                <p class="text-green-100 mt-1">تقارير مالية شاملة لمراقبة الأداء المالي واتخاذ القرارات الاستراتيجية</p>
            </div>
        </div>
    </div>

    <!-- Key Financial Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي الإيرادات</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_revenue']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">دينار عراقي</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي المصاريف</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_expenses']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">دينار عراقي</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line-down text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">صافي الربح</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['net_profit']) }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ $stats['profit_margin'] }}% هامش ربح</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-coins text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Cash Flow -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">التدفق النقدي</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['cash_flow']) }}</p>
                    <p class="text-xs text-blue-600 mt-1">النسبة الحالية: {{ $stats['current_ratio'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-water text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Reports Menu -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">التقارير المالية المتاحة</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Profit & Loss -->
            <a href="{{ route('reports.financial.profit-loss') }}" class="block p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:from-green-100 hover:to-green-200 transition-all duration-200 border border-green-200">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-bar text-white text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-green-900">قائمة الدخل</h4>
                        <p class="text-sm text-green-700">الإيرادات والمصاريف والأرباح</p>
                    </div>
                </div>
            </a>

            <!-- Balance Sheet -->
            <a href="{{ route('reports.financial.balance-sheet') }}" class="block p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all duration-200 border border-blue-200">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-balance-scale text-white text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-blue-900">الميزانية العمومية</h4>
                        <p class="text-sm text-blue-700">الأصول والخصوم وحقوق الملكية</p>
                    </div>
                </div>
            </a>

            <!-- Cash Flow -->
            <a href="{{ route('reports.financial.cash-flow') }}" class="block p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:from-purple-100 hover:to-purple-200 transition-all duration-200 border border-purple-200">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-water text-white text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-purple-900">قائمة التدفق النقدي</h4>
                        <p class="text-sm text-purple-700">التدفقات النقدية الداخلة والخارجة</p>
                    </div>
                </div>
            </a>

            <!-- Financial Ratios -->
            <a href="{{ route('reports.financial.ratios') }}" class="block p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg hover:from-orange-100 hover:to-orange-200 transition-all duration-200 border border-orange-200">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calculator text-white text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-orange-900">النسب المالية</h4>
                        <p class="text-sm text-orange-700">تحليل الأداء المالي والسيولة</p>
                    </div>
                </div>
            </a>

            <!-- Budget vs Actual -->
            <a href="{{ route('reports.financial.budget-vs-actual') }}" class="block p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg hover:from-indigo-100 hover:to-indigo-200 transition-all duration-200 border border-indigo-200">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-pie text-white text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-indigo-900">الموازنة مقابل الفعلي</h4>
                        <p class="text-sm text-indigo-700">مقارنة الأداء مع الموازنة المخططة</p>
                    </div>
                </div>
            </a>

            <!-- Custom Reports -->
            <div class="block p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="w-12 h-12 bg-gray-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cog text-white text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">تقارير مخصصة</h4>
                        <p class="text-sm text-gray-700">إنشاء تقارير حسب الحاجة</p>
                        <span class="inline-block mt-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">قريباً</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue and Expense Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Streams -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">مصادر الإيرادات</h3>
            <div class="space-y-4">
                @foreach($revenueStreams as $stream)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded-full ml-3"></div>
                        <span class="text-sm font-medium text-gray-900">{{ $stream['stream'] }}</span>
                    </div>
                    <div class="text-left">
                        <span class="text-sm font-medium text-gray-900">{{ $stream['percentage'] }}%</span>
                        <p class="text-xs text-gray-500">{{ number_format($stream['amount']) }} د.ع</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Expense Categories -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">فئات المصاريف</h3>
            <div class="space-y-4">
                @foreach($expenseCategories as $category)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded-full ml-3"></div>
                        <span class="text-sm font-medium text-gray-900">{{ $category['category'] }}</span>
                    </div>
                    <div class="text-left">
                        <span class="text-sm font-medium text-gray-900">{{ $category['percentage'] }}%</span>
                        <p class="text-xs text-gray-500">{{ number_format($category['amount']) }} د.ع</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <button class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-download text-blue-600 text-xl ml-3"></i>
                <span class="font-medium text-blue-900">تصدير التقارير</span>
            </button>
            <button class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-print text-green-600 text-xl ml-3"></i>
                <span class="font-medium text-green-900">طباعة التقارير</span>
            </button>
            <button class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-envelope text-purple-600 text-xl ml-3"></i>
                <span class="font-medium text-purple-900">إرسال بالبريد</span>
            </button>
            <button class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                <i class="fas fa-calendar text-orange-600 text-xl ml-3"></i>
                <span class="font-medium text-orange-900">جدولة التقارير</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add any JavaScript for financial reports here
console.log('Financial Reports Dashboard Loaded');
</script>
@endpush
