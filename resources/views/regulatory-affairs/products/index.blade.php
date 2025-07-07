@extends('layouts.app')

@section('title', 'الأصناف الدوائية - MaxCon ERP')
@section('page-title', 'الأصناف الدوائية')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">الأصناف الدوائية</h1>
                <p class="text-green-100">إدارة ومتابعة المنتجات الدوائية المسجلة</p>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.products.create') }}" 
                   class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 transition-colors font-medium">
                    <i class="fas fa-plus ml-2"></i>
                    منتج جديد
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-pills"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">إجمالي المنتجات</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['total'] }}</p>
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
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">مطروح في السوق</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['marketed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">تراخيص تنتهي</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['license_expiring'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dna text-teal-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">أدوية جنيسة</p>
                    <p class="text-2xl font-bold text-teal-600">{{ $stats['generic'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <form method="GET" action="{{ route('regulatory-affairs.products.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="البحث في المنتجات..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الشركة</label>
                <select name="company_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">جميع الشركات</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الفئة العلاجية</label>
                <select name="therapeutic_class" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">جميع الفئات</option>
                    @foreach($therapeuticClasses as $class)
                        <option value="{{ $class }}" {{ request('therapeutic_class') === $class ? 'selected' : '' }}>
                            {{ $class }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة الوصفة</label>
                <select name="prescription_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">جميع الحالات</option>
                    <option value="prescription" {{ request('prescription_status') === 'prescription' ? 'selected' : '' }}>بوصفة طبية</option>
                    <option value="otc" {{ request('prescription_status') === 'otc' ? 'selected' : '' }}>بدون وصفة</option>
                    <option value="controlled" {{ request('prescription_status') === 'controlled' ? 'selected' : '' }}>مادة مخدرة</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">حالة التسويق</label>
                <select name="market_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">جميع الحالات</option>
                    <option value="marketed" {{ request('market_status') === 'marketed' ? 'selected' : '' }}>مطروح</option>
                    <option value="not_marketed" {{ request('market_status') === 'not_marketed' ? 'selected' : '' }}>غير مطروح</option>
                    <option value="discontinued" {{ request('market_status') === 'discontinued' ? 'selected' : '' }}>متوقف</option>
                    <option value="withdrawn" {{ request('market_status') === 'withdrawn' ? 'selected' : '' }}>مسحوب</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors w-full">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg card-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">قائمة الأصناف الدوائية</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشركة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الفئة العلاجية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الوصفة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الترخيص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة التسويق</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-pills text-green-600"></i>
                                </div>
                                <div class="mr-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $product->display_trade_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $product->display_generic_name }}</div>
                                    <div class="text-xs text-gray-400">{{ $product->display_active_ingredient }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->company->display_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->display_therapeutic_class }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($product->prescription_status === 'prescription') bg-blue-100 text-blue-800
                                @elseif($product->prescription_status === 'otc') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $product->prescription_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($product->license_status === 'active') bg-green-100 text-green-800
                                @elseif($product->license_status === 'expired') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $product->license_status_arabic }}
                            </span>
                            @if($product->isLicenseExpiring(30))
                                <div class="text-xs text-orange-600 mt-1">
                                    ينتهي خلال {{ $product->getDaysUntilLicenseExpiry() }} يوم
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($product->market_status === 'marketed') bg-green-100 text-green-800
                                @elseif($product->market_status === 'not_marketed') bg-gray-100 text-gray-800
                                @elseif($product->market_status === 'discontinued') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $product->market_status_arabic }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <a href="{{ route('regulatory-affairs.products.show', $product) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('regulatory-affairs.products.edit', $product) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-product-id="{{ $product->id }}"
                                        onclick="updateStatus(this.dataset.productId)"
                                        class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-pills text-4xl mb-4"></i>
                                <p class="text-lg font-medium">لا توجد منتجات دوائية</p>
                                <p class="text-sm">ابدأ بإضافة منتج دوائي جديد</p>
                                <a href="{{ route('regulatory-affairs.products.create') }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-plus ml-2"></i>
                                    إضافة منتج جديد
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function updateStatus(productId) {
    // This would open a modal to update product status
    alert('سيتم فتح نافذة تحديث حالة المنتج');
}
</script>
@endpush
@endsection
