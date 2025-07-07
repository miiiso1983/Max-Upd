@extends('layouts.app')

@section('title', 'تنبيهات انتهاء الصلاحية - MaxCon ERP')
@section('page-title', 'تنبيهات انتهاء الصلاحية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-red-600 to-orange-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">تنبيهات انتهاء الصلاحية</h1>
                <p class="text-red-100">مراقبة التراخيص والصلاحيات المنتهية أو التي تنتهي قريباً</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تراخيص شركات منتهية</p>
                    <p class="text-2xl font-bold text-red-600">{{ $expiredCompanies->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تراخيص منتجات منتهية</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $expiredProducts->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-vials text-yellow-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">دفعات منتهية الصلاحية</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $expiredBatches->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تنتهي خلال 30 يوم</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $expiringSoon->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Expired Company Licenses -->
    @if($expiredCompanies->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h3 class="text-lg font-semibold text-red-900 flex items-center">
                <i class="fas fa-building ml-2"></i>
                تراخيص الشركات المنتهية ({{ $expiredCompanies->count() }})
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الترخيص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">منتهي منذ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($expiredCompanies as $company)
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $company->license_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                            {{ $company->license_expiry_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                            {{ abs($company->license_expiry_date->diffInDays(now())) }} يوم
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

    <!-- Expired Product Licenses -->
    @if($expiredProducts->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-orange-50">
            <h3 class="text-lg font-semibold text-orange-900 flex items-center">
                <i class="fas fa-pills ml-2"></i>
                تراخيص المنتجات المنتهية ({{ $expiredProducts->count() }})
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الترخيص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">منتهي منذ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($expiredProducts as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-pills text-orange-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->display_trade_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $product->display_generic_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->company->display_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $product->license_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 font-medium">
                            {{ $product->license_expiry_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">
                            {{ abs($product->license_expiry_date->diffInDays(now())) }} يوم
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

    <!-- Expired Batches -->
    @if($expiredBatches->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50">
            <h3 class="text-lg font-semibold text-yellow-900 flex items-center">
                <i class="fas fa-vials ml-2"></i>
                الدفعات منتهية الصلاحية ({{ $expiredBatches->count() }})
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدفعة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">منتهي منذ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($expiredBatches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                                    <i class="fas fa-vials text-yellow-600"></i>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 font-medium">
                            {{ $batch->expiry_date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                            {{ abs($batch->expiry_date->diffInDays(now())) }} يوم
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($batch->quantity_released) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('regulatory-affairs.batches.show', $batch) }}" 
                               class="text-blue-600 hover:text-blue-900 ml-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('regulatory-affairs.batches.edit', $batch) }}" 
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

    <!-- Items Expiring Soon -->
    @if($expiringSoon->count() > 0)
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
            <h3 class="text-lg font-semibold text-purple-900 flex items-center">
                <i class="fas fa-clock ml-2"></i>
                عناصر تنتهي خلال 30 يوم ({{ $expiringSoon->count() }})
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الانتهاء</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ينتهي خلال</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($expiringSoon as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($item['type'] === 'company') bg-blue-100 text-blue-800
                                @elseif($item['type'] === 'product') bg-green-100 text-green-800
                                @else bg-purple-100 text-purple-800 @endif">
                                @if($item['type'] === 'company') شركة
                                @elseif($item['type'] === 'product') منتج
                                @else دفعة @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                            <div class="text-sm text-gray-500">{{ $item['identifier'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-medium">
                            {{ $item['expiry_date'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600">
                            {{ $item['days_until_expiry'] }} يوم
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ $item['view_url'] }}" 
                               class="text-blue-600 hover:text-blue-900 ml-3">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ $item['edit_url'] }}" 
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

    <!-- No Alerts -->
    @if($expiredCompanies->count() == 0 && $expiredProducts->count() == 0 && $expiredBatches->count() == 0 && $expiringSoon->count() == 0)
    <div class="bg-white rounded-lg p-12 card-shadow text-center">
        <div class="text-green-500 mb-4">
            <i class="fas fa-check-circle text-6xl"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">لا توجد تنبيهات انتهاء صلاحية</h3>
        <p class="text-gray-600">جميع التراخيص والصلاحيات سارية المفعول</p>
    </div>
    @endif
</div>
@endsection
