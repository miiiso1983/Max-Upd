@extends('layouts.app')

@section('page-title', 'التحليلات والذكاء الاصطناعي')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg p-6 text-white">
        <div class="flex items-center space-x-4 space-x-reverse">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                <i class="fas fa-brain text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold">مرحباً بك في لوحة التحليلات والذكاء الاصطناعي</h1>
                <p class="text-purple-100 mt-1">تحليلات متقدمة وتوقعات ذكية لاتخاذ قرارات أفضل</p>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">إجمالي الإيرادات</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_revenue']) }} د.ع</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i>
                        {{ $stats['growth_rate'] }}% نمو
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Customer Satisfaction -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">رضا العملاء</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['customer_satisfaction'] }}%</p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-smile"></i>
                        ممتاز
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-heart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Market Share -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">الحصة السوقية</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['market_share'] }}%</p>
                    <p class="text-sm text-purple-600 mt-1">
                        <i class="fas fa-chart-pie"></i>
                        من السوق
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-pie text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- AI Accuracy -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">دقة التوقعات</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['predictions_accuracy'] }}%</p>
                    <p class="text-sm text-indigo-600 mt-1">
                        <i class="fas fa-robot"></i>
                        ذكاء اصطناعي
                    </p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-robot text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- AI Recommendations -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 md:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">التوصيات الذكية</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['ai_recommendations'] }}</p>
                    <p class="text-sm text-orange-600 mt-1">
                        <i class="fas fa-lightbulb"></i>
                        توصية جديدة
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-lightbulb text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Trend Chart -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">اتجاه المبيعات - الفعلي مقابل المتوقع</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <div class="text-center">
                <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                <p class="text-gray-600">سيتم عرض الرسم البياني هنا</p>
                <p class="text-sm text-gray-500 mt-1">يتطلب مكتبة رسوم بيانية مثل Chart.js</p>
            </div>
        </div>
    </div>

    <!-- Customer Segments and AI Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Customer Segments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">شرائح العملاء</h3>
            <div class="space-y-4">
                @foreach($customerSegments as $segment)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-500 rounded-full ml-3"></div>
                        <span class="text-sm font-medium text-gray-900">{{ $segment['segment'] }}</span>
                    </div>
                    <div class="text-left">
                        <span class="text-sm font-medium text-gray-900">{{ $segment['percentage'] }}%</span>
                        <p class="text-xs text-gray-500">{{ number_format($segment['revenue']) }} د.ع</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- AI Insights -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">رؤى الذكاء الاصطناعي</h3>
            <div class="space-y-4">
                @foreach($aiInsights as $insight)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3 space-x-reverse">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            @if($insight['type'] === 'opportunity') bg-green-100 @elseif($insight['type'] === 'warning') bg-yellow-100 @else bg-blue-100 @endif">
                            <i class="fas 
                                @if($insight['type'] === 'opportunity') fa-arrow-up text-green-600 
                                @elseif($insight['type'] === 'warning') fa-exclamation-triangle text-yellow-600 
                                @else fa-lightbulb text-blue-600 @endif text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ $insight['title'] }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $insight['description'] }}</p>
                            <div class="flex items-center mt-2 space-x-4 space-x-reverse">
                                <span class="text-xs text-gray-500">الثقة: {{ $insight['confidence'] }}%</span>
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($insight['impact'] === 'high') bg-red-100 text-red-800 
                                    @elseif($insight['impact'] === 'medium') bg-yellow-100 text-yellow-800 
                                    @else bg-green-100 text-green-800 @endif">
                                    تأثير {{ $insight['impact'] === 'high' ? 'عالي' : ($insight['impact'] === 'medium' ? 'متوسط' : 'منخفض') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('analytics.sales-prediction') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-chart-line text-blue-600 text-xl ml-3"></i>
                <span class="font-medium text-blue-900">توقعات المبيعات</span>
            </a>
            <a href="{{ route('analytics.business-intelligence') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-lightbulb text-purple-600 text-xl ml-3"></i>
                <span class="font-medium text-purple-900">ذكاء الأعمال</span>
            </a>
            <button class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-download text-green-600 text-xl ml-3"></i>
                <span class="font-medium text-green-900">تصدير التقرير</span>
            </button>
        </div>
    </div>
</div>
@endsection
