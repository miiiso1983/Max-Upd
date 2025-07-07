@extends('layouts.app')

@section('title', 'تقرير الامتثال - MaxCon ERP')
@section('page-title', 'تقرير الامتثال')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">تقرير الامتثال التنظيمي</h1>
                <p class="text-blue-100">مراقبة مستوى الامتثال للمتطلبات التنظيمية</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
    </div>

    <!-- Compliance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">معدل امتثال الشركات</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ $stats['total_companies'] > 0 ? round(($stats['compliant_companies'] / $stats['total_companies']) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">معدل امتثال المنتجات</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $stats['total_products'] > 0 ? round(($stats['compliant_products'] / $stats['total_products']) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-search text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">معدل التفتيشات المرضية</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ $stats['total_inspections'] > 0 ? round(($stats['satisfactory_inspections'] / $stats['total_inspections']) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">عناصر غير ممتثلة</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ ($stats['total_companies'] - $stats['compliant_companies']) + ($stats['total_products'] - $stats['compliant_products']) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Inspections -->
    @if($recentInspections->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">التفتيشات الأخيرة</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم التفتيش</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكيان المفتش</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ التفتيش</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النتيجة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentInspections as $inspection)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $inspection->inspection_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($inspection->company)
                                <div class="text-sm font-medium text-gray-900">{{ $inspection->company->display_name }}</div>
                                <div class="text-sm text-gray-500">شركة</div>
                            @elseif($inspection->product)
                                <div class="text-sm font-medium text-gray-900">{{ $inspection->product->display_trade_name }}</div>
                                <div class="text-sm text-gray-500">منتج</div>
                            @elseif($inspection->batch)
                                <div class="text-sm font-medium text-gray-900">{{ $inspection->batch->batch_number }}</div>
                                <div class="text-sm text-gray-500">دفعة</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $inspection->inspection_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($inspection->inspection_result)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($inspection->inspection_result === 'satisfactory') bg-green-100 text-green-800
                                    @elseif($inspection->inspection_result === 'minor_deficiencies') bg-yellow-100 text-yellow-800
                                    @elseif($inspection->inspection_result === 'major_deficiencies') bg-orange-100 text-orange-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $inspection->inspection_result_arabic }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">في الانتظار</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.inspections.show', $inspection) }}" 
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

    <!-- Non-Compliant Companies -->
    @if($nonCompliantCompanies->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h3 class="text-lg font-semibold text-red-900">الشركات غير الممتثلة ({{ $nonCompliantCompanies->count() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الترخيص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المشكلة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($nonCompliantCompanies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-building text-red-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $company->display_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $company->registration_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($company->license_status === 'active') bg-green-100 text-green-800
                                @elseif($company->license_status === 'expired') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $company->license_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $company->license_expiry_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                            @if($company->license_status !== 'active')
                                ترخيص غير نشط
                            @elseif($company->license_expiry_date <= now())
                                ترخيص منتهي
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.companies.show', $company) }}" 
                               class="text-blue-600 hover:text-blue-900 ml-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('regulatory-affairs.companies.edit', $company) }}" 
                               class="text-green-600 hover:text-green-900">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Non-Compliant Products -->
    @if($nonCompliantProducts->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-orange-50">
            <h3 class="text-lg font-semibold text-orange-900">المنتجات غير الممتثلة ({{ $nonCompliantProducts->count() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الترخيص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المشكلة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($nonCompliantProducts as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-pills text-orange-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->display_trade_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $product->registration_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->company->display_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($product->license_status === 'active') bg-green-100 text-green-800
                                @elseif($product->license_status === 'expired') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $product->license_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->license_expiry_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">
                            @if($product->license_status !== 'active')
                                ترخيص غير نشط
                            @elseif($product->license_expiry_date <= now())
                                ترخيص منتهي
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.products.show', $product) }}" 
                               class="text-blue-600 hover:text-blue-900 ml-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('regulatory-affairs.products.edit', $product) }}" 
                               class="text-green-600 hover:text-green-900">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- All Compliant -->
    @if($nonCompliantCompanies->count() == 0 && $nonCompliantProducts->count() == 0)
    <div class="bg-white rounded-lg p-12 card-shadow text-center">
        <div class="text-green-500 mb-4">
            <i class="fas fa-shield-alt text-6xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">امتثال كامل</h3>
        <p class="text-gray-600">جميع الشركات والمنتجات ممتثلة للمتطلبات التنظيمية</p>
    </div>
    @endif
</div>
@endsection
