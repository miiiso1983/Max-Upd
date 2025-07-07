@extends('layouts.app')

@section('page-title', 'قائمة الدخل')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">قائمة الدخل</h1>
            <p class="text-gray-600">{{ $data['period'] }}</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download ml-2"></i>
                تصدير PDF
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-print ml-2"></i>
                طباعة
            </button>
        </div>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <div class="space-y-6">
                <!-- Revenue Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">الإيرادات</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">إيرادات المبيعات</span>
                            <span class="font-medium">{{ number_format($data['revenue']['sales_revenue']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">إيرادات الخدمات</span>
                            <span class="font-medium">{{ number_format($data['revenue']['service_revenue']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">إيرادات أخرى</span>
                            <span class="font-medium">{{ number_format($data['revenue']['other_revenue']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-3 font-semibold text-lg">
                            <span class="text-gray-900">إجمالي الإيرادات</span>
                            <span class="text-green-600">{{ number_format($data['revenue']['total_revenue']) }} د.ع</span>
                        </div>
                    </div>
                </div>

                <!-- Cost of Goods Sold -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">تكلفة البضائع المباعة</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">المواد الخام</span>
                            <span class="font-medium">{{ number_format($data['cost_of_goods_sold']['materials']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">العمالة المباشرة</span>
                            <span class="font-medium">{{ number_format($data['cost_of_goods_sold']['labor']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">المصاريف العامة</span>
                            <span class="font-medium">{{ number_format($data['cost_of_goods_sold']['overhead']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-3 font-semibold text-lg">
                            <span class="text-gray-900">إجمالي تكلفة البضائع المباعة</span>
                            <span class="text-red-600">{{ number_format($data['cost_of_goods_sold']['total_cogs']) }} د.ع</span>
                        </div>
                    </div>
                </div>

                <!-- Gross Profit -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center font-bold text-xl">
                        <span class="text-gray-900">إجمالي الربح</span>
                        <span class="text-green-600">{{ number_format($data['gross_profit']) }} د.ع</span>
                    </div>
                    <p class="text-sm text-green-700 mt-1">
                        هامش الربح الإجمالي: {{ number_format(($data['gross_profit'] / $data['revenue']['total_revenue']) * 100, 1) }}%
                    </p>
                </div>

                <!-- Operating Expenses -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">المصاريف التشغيلية</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">الرواتب والأجور</span>
                            <span class="font-medium">{{ number_format($data['operating_expenses']['salaries']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">الإيجار</span>
                            <span class="font-medium">{{ number_format($data['operating_expenses']['rent']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">المرافق</span>
                            <span class="font-medium">{{ number_format($data['operating_expenses']['utilities']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">التسويق والإعلان</span>
                            <span class="font-medium">{{ number_format($data['operating_expenses']['marketing']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">المصاريف الإدارية</span>
                            <span class="font-medium">{{ number_format($data['operating_expenses']['administrative']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">الاستهلاك</span>
                            <span class="font-medium">{{ number_format($data['operating_expenses']['depreciation']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-3 font-semibold text-lg">
                            <span class="text-gray-900">إجمالي المصاريف التشغيلية</span>
                            <span class="text-red-600">{{ number_format($data['operating_expenses']['total_operating']) }} د.ع</span>
                        </div>
                    </div>
                </div>

                <!-- Operating Income -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center font-bold text-xl">
                        <span class="text-gray-900">الدخل التشغيلي</span>
                        <span class="text-blue-600">{{ number_format($data['operating_income']) }} د.ع</span>
                    </div>
                    <p class="text-sm text-blue-700 mt-1">
                        هامش الدخل التشغيلي: {{ number_format(($data['operating_income'] / $data['revenue']['total_revenue']) * 100, 1) }}%
                    </p>
                </div>

                <!-- Other Income -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">الإيرادات الأخرى</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">إيرادات الفوائد</span>
                            <span class="font-medium">{{ number_format($data['other_income']['interest_income']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">إيرادات الاستثمار</span>
                            <span class="font-medium">{{ number_format($data['other_income']['investment_income']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-3 font-semibold">
                            <span class="text-gray-900">إجمالي الإيرادات الأخرى</span>
                            <span class="text-green-600">{{ number_format($data['other_income']['total_other']) }} د.ع</span>
                        </div>
                    </div>
                </div>

                <!-- Other Expenses -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">المصاريف الأخرى</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">مصاريف الفوائد</span>
                            <span class="font-medium">{{ number_format($data['other_expenses']['interest_expense']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">مصاريف الضرائب</span>
                            <span class="font-medium">{{ number_format($data['other_expenses']['tax_expense']) }} د.ع</span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-3 font-semibold">
                            <span class="text-gray-900">إجمالي المصاريف الأخرى</span>
                            <span class="text-red-600">{{ number_format($data['other_expenses']['total_other_exp']) }} د.ع</span>
                        </div>
                    </div>
                </div>

                <!-- Net Income -->
                <div class="bg-gradient-to-r from-green-100 to-blue-100 p-6 rounded-lg border-2 border-green-200">
                    <div class="flex justify-between items-center font-bold text-2xl">
                        <span class="text-gray-900">صافي الدخل</span>
                        <span class="text-green-600">{{ number_format($data['net_income']) }} د.ع</span>
                    </div>
                    <p class="text-sm text-green-700 mt-2">
                        هامش صافي الربح: {{ number_format(($data['net_income'] / $data['revenue']['total_revenue']) * 100, 1) }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">تغيير الفترة</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <button class="p-3 text-center border border-gray-300 rounded-lg hover:bg-gray-50">
                الشهر الحالي
            </button>
            <button class="p-3 text-center border border-gray-300 rounded-lg hover:bg-gray-50">
                الشهر الماضي
            </button>
            <button class="p-3 text-center border border-gray-300 rounded-lg hover:bg-gray-50">
                الربع الحالي
            </button>
            <button class="p-3 text-center border border-gray-300 rounded-lg hover:bg-gray-50">
                السنة الحالية
            </button>
        </div>
    </div>
</div>
@endsection
