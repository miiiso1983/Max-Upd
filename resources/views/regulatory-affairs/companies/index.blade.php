@extends('layouts.app')

@section('title', 'الشركات الدوائية - MaxCon ERP')
@section('page-title', 'الشركات الدوائية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">الشركات الدوائية</h1>
                <p class="text-blue-100">إدارة ومتابعة الشركات المصنعة والموزعة للأدوية</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.companies.create') }}" 
                   class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                    <i class="fas fa-plus ml-2"></i>
                    شركة جديدة
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي الشركات</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الشركات النشطة</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تراخيص تنتهي قريباً</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['license_expiring'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-certificate text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">GMP تنتهي قريباً</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['gmp_expiring'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('regulatory-affairs.companies.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="البحث في الشركات..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>تحت المراجعة</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>معلق</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة الترخيص</label>
                <select name="license_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع حالات الترخيص</option>
                    <option value="active" {{ request('license_status') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="expired" {{ request('license_status') === 'expired' ? 'selected' : '' }}>منتهي</option>
                    <option value="suspended" {{ request('license_status') === 'suspended' ? 'selected' : '' }}>معلق</option>
                    <option value="cancelled" {{ request('license_status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع الشركة</label>
                <select name="company_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الأنواع</option>
                    <option value="manufacturer" {{ request('company_type') === 'manufacturer' ? 'selected' : '' }}>مصنع</option>
                    <option value="distributor" {{ request('company_type') === 'distributor' ? 'selected' : '' }}>موزع</option>
                    <option value="importer" {{ request('company_type') === 'importer' ? 'selected' : '' }}>مستورد</option>
                    <option value="exporter" {{ request('company_type') === 'exporter' ? 'selected' : '' }}>مصدر</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">بلد المنشأ</label>
                <select name="country_of_origin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع البلدان</option>
                    @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country_of_origin') === $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Companies Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة الشركات الدوائية</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم التسجيل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">بلد المنشأ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الترخيص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ انتهاء الترخيص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($companies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-building text-blue-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $company->display_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $company->display_trade_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $company->registration_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $company->company_type_arabic }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $company->country_of_origin }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($company->license_status === 'active') bg-green-100 text-green-800
                                @elseif($company->license_status === 'expired') bg-red-100 text-red-800
                                @elseif($company->license_status === 'suspended') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $company->license_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="{{ $company->isLicenseExpiring(30) ? 'text-orange-600 font-medium' : '' }}">
                                {{ $company->license_expiry_date->format('Y-m-d') }}
                            </span>
                            @if($company->isLicenseExpiring(30))
                                <div class="text-xs text-orange-600">
                                    ينتهي خلال {{ $company->getDaysUntilLicenseExpiry() }} يوم
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($company->status === 'active') bg-green-100 text-green-800
                                @elseif($company->status === 'inactive') bg-red-100 text-red-800
                                @elseif($company->status === 'under_review') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $company->status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <a href="{{ route('regulatory-affairs.companies.show', $company) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('regulatory-affairs.companies.edit', $company) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-company-id="{{ $company->id }}"
                                        onclick="updateStatus(this.dataset.companyId)"
                                        class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-building text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد شركات دوائية</p>
                                <p class="text-sm">ابدأ بإضافة شركة دوائية جديدة</p>
                                <a href="{{ route('regulatory-affairs.companies.create') }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus ml-2"></i>
                                    إضافة شركة جديدة
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($companies->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $companies->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function updateStatus(companyId) {
    // This would open a modal to update company status
    alert('سيتم فتح نافذة تحديث حالة الشركة');
}
</script>
@endpush
@endsection
