{{--
    @var \App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany $company
    @var array $stats
    @var \Illuminate\Database\Eloquent\Collection $recentProducts
    @var \Illuminate\Database\Eloquent\Collection $recentInspections
    @var array $alerts
--}}
@php
    /** @var \App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany $company */
    /** @var array $stats */
    /** @var \Illuminate\Database\Eloquent\Collection $recentProducts */
    /** @var \Illuminate\Database\Eloquent\Collection $recentInspections */
    /** @var array $alerts */
@endphp
@extends('layouts.app')

@section('title', $company->display_name . ' - MaxCon ERP')
@section('page-title', $company->display_name)

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $company->display_name }}</h1>
                <p class="text-blue-100">{{ $company->display_trade_name }} - {{ $company->company_type_arabic }}</p>
                <div class="flex items-center mt-2 space-x-4 space-x-reverse">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($company->status === 'active') bg-green-100 text-green-800
                        @elseif($company->status === 'inactive') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ $company->status_arabic }}
                    </span>
                    <span class="text-blue-100">{{ $company->registration_number }}</span>
                </div>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.companies.edit', $company) }}" 
                   class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
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

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">المنتجات المسجلة</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['products_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">المنتجات النشطة</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['active_products'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-search text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">التفتيشات</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['inspections_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">نقاط الامتثال</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['compliance_score'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Company Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">رقم التسجيل</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $company->registration_number }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">نوع الشركة</label>
                        <p class="text-sm text-gray-900">{{ $company->company_type_arabic }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">بلد المنشأ</label>
                        <p class="text-sm text-gray-900">{{ $company->country_of_origin }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">مستوى المخاطر</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($company->risk_level === 'low') bg-green-100 text-green-800
                            @elseif($company->risk_level === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $company->risk_level_arabic }}
                        </span>
                    </div>
                </div>

                @if($company->description)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-600">وصف الشركة</label>
                    <p class="text-sm text-gray-900 mt-1">{{ $company->description }}</p>
                </div>
                @endif
            </div>

            <!-- License Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الترخيص</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">رقم الترخيص</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $company->license_number }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الجهة المنظمة</label>
                        <p class="text-sm text-gray-900">{{ $company->regulatory_authority }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الإصدار</label>
                        <p class="text-sm text-gray-900">{{ $company->license_issue_date->format('Y-m-d') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الانتهاء</label>
                        <p class="text-sm text-gray-900 {{ $company->isLicenseExpiring(30) ? 'text-orange-600 font-medium' : '' }}">
                            {{ $company->license_expiry_date->format('Y-m-d') }}
                            @if($company->isLicenseExpiring(30))
                                <span class="text-xs">(ينتهي خلال {{ $company->getDaysUntilLicenseExpiry() }} يوم)</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">حالة الترخيص</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($company->license_status === 'active') bg-green-100 text-green-800
                            @elseif($company->license_status === 'expired') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $company->license_status_arabic }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الاتصال</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600">العنوان</label>
                        <p class="text-sm text-gray-900">{{ $company->address }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">المدينة</label>
                        <p class="text-sm text-gray-900">{{ $company->city }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الهاتف</label>
                        <p class="text-sm text-gray-900">{{ $company->phone }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">البريد الإلكتروني</label>
                        <p class="text-sm text-gray-900">{{ $company->email }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الشخص المسؤول</label>
                        <p class="text-sm text-gray-900">{{ $company->contact_person }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('regulatory-affairs.products.create', ['company_id' => $company->id]) }}" 
                       class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة منتج جديد
                    </a>
                    
                    <a href="{{ route('regulatory-affairs.inspections.create', ['company_id' => $company->id]) }}" 
                       class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-search ml-2"></i>
                        جدولة تفتيش
                    </a>
                    
                    <button onclick="updateStatus()" 
                            class="block w-full bg-orange-600 text-white text-center py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-cog ml-2"></i>
                        تحديث الحالة
                    </button>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">المنتجات الأخيرة</h3>
                    <a href="{{ route('regulatory-affairs.products.index', ['company_id' => $company->id]) }}" 
                       class="text-blue-600 hover:text-blue-700 text-sm">عرض الكل</a>
                </div>
                
                <div class="space-y-3">
                    @forelse($recentProducts as $product)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-pills text-green-600 text-xs"></i>
                        </div>
                        <div class="mr-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $product->display_trade_name }}</p>
                            <p class="text-xs text-gray-500">{{ $product->display_active_ingredient }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">لا توجد منتجات مسجلة</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Inspections -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">التفتيشات الأخيرة</h3>
                    <a href="{{ route('regulatory-affairs.inspections.index', ['company_id' => $company->id]) }}" 
                       class="text-blue-600 hover:text-blue-700 text-sm">عرض الكل</a>
                </div>
                
                <div class="space-y-3">
                    @forelse($recentInspections as $inspection)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-search text-purple-600 text-xs"></i>
                        </div>
                        <div class="mr-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $inspection->inspection_type_arabic }}</p>
                            <p class="text-xs text-gray-500">{{ $inspection->inspection_date->format('Y-m-d') }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($inspection->inspection_result === 'satisfactory') bg-green-100 text-green-800
                            @elseif($inspection->inspection_result === 'minor_deficiencies') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $inspection->inspection_result_arabic }}
                        </span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">لا توجد تفتيشات</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    @include('regulatory-affairs.components.document-upload', [
        'entityType' => 'company',
        'entityId' => $company->id,
        'entityName' => $company->display_name
    ])
</div>

@push('scripts')
<script>
function updateStatus() {
    // This would open a modal to update company status
    alert('سيتم فتح نافذة تحديث حالة الشركة');
}
</script>
@endpush
@endsection
