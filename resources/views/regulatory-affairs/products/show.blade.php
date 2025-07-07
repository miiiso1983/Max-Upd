@extends('layouts.app')

@section('title', 'تفاصيل المنتج - ' . $product->display_trade_name)
@section('page-title', 'تفاصيل المنتج')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $product->display_trade_name }}</h1>
                <p class="text-green-100">{{ $product->display_generic_name }}</p>
                <div class="flex items-center mt-2 space-x-4 space-x-reverse">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($product->license_status === 'active') bg-green-100 text-green-800
                        @elseif($product->license_status === 'expired') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ $product->license_status_arabic }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($product->market_status === 'marketed') bg-green-100 text-green-800
                        @elseif($product->market_status === 'not_marketed') bg-gray-100 text-gray-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ $product->market_status_arabic }}
                    </span>
                    <span class="text-green-100">{{ $product->registration_number }}</span>
                </div>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <a href="{{ route('regulatory-affairs.products.edit', $product) }}" 
                   class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-green-50 transition-colors">
                    <i class="fas fa-edit ml-2"></i>
                    تعديل
                </a>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-pills"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @php
        $alerts = [];
        if($product->isLicenseExpiring(30)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'ينتهي ترخيص هذا المنتج خلال ' . $product->getDaysUntilLicenseExpiry() . ' يوم'
            ];
        }
        if($product->license_status === 'expired') {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'انتهت صلاحية ترخيص هذا المنتج'
            ];
        }
    @endphp

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
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-vials text-purple-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الدفعات المنتجة</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['batches_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الدفعات المطروحة</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['released_batches'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flask text-orange-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">الفحوصات</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['tests_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-search text-blue-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm font-medium text-gray-600">التفتيشات</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['inspections_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">رقم التسجيل</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $product->registration_number }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الاسم التجاري</label>
                        <p class="text-sm text-gray-900">{{ $product->display_trade_name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الاسم العلمي</label>
                        <p class="text-sm text-gray-900">{{ $product->display_generic_name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">المادة الفعالة</label>
                        <p class="text-sm text-gray-900">{{ $product->display_active_ingredient }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">التركيز</label>
                        <p class="text-sm text-gray-900">{{ $product->strength }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الشكل الصيدلاني</label>
                        <p class="text-sm text-gray-900">{{ $product->display_dosage_form }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">طريقة الإعطاء</label>
                        <p class="text-sm text-gray-900">{{ $product->display_route_of_administration }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">حجم العبوة</label>
                        <p class="text-sm text-gray-900">{{ $product->pack_size }}</p>
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الشركة المصنعة</h3>
                
                <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-blue-600 text-xl"></i>
                    </div>
                    <div class="mr-4 flex-1">
                        <h4 class="font-medium text-gray-900">{{ $product->company->display_name }}</h4>
                        <p class="text-sm text-gray-600">{{ $product->company->display_trade_name }}</p>
                        <p class="text-xs text-gray-500">{{ $product->company->registration_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $product->country_of_origin }}</p>
                        <p class="text-xs text-gray-500">{{ $product->company->company_type_arabic }}</p>
                    </div>
                </div>
            </div>

            <!-- License Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الترخيص</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">رقم الترخيص</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $product->license_number }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الجهة المنظمة</label>
                        <p class="text-sm text-gray-900">{{ $product->regulatory_authority }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الإصدار</label>
                        <p class="text-sm text-gray-900">{{ $product->license_issue_date->format('Y-m-d') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">تاريخ الانتهاء</label>
                        <p class="text-sm text-gray-900 {{ $product->isLicenseExpiring(30) ? 'text-orange-600 font-medium' : '' }}">
                            {{ $product->license_expiry_date->format('Y-m-d') }}
                            @if($product->isLicenseExpiring(30))
                                <span class="text-xs">(ينتهي خلال {{ $product->getDaysUntilLicenseExpiry() }} يوم)</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Clinical Information -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات السريرية</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">الفئة العلاجية</label>
                        <p class="text-sm text-gray-900">{{ $product->display_therapeutic_class }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">حالة الوصفة</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($product->prescription_status === 'prescription') bg-blue-100 text-blue-800
                            @elseif($product->prescription_status === 'otc') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $product->prescription_status_arabic }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">مدة الصلاحية</label>
                        <p class="text-sm text-gray-900">{{ $product->shelf_life_months }} شهر</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">ظروف التخزين</label>
                        <p class="text-sm text-gray-900">{{ $product->display_storage_conditions }}</p>
                    </div>
                </div>

                @if($product->indication)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-600 mb-2">دواعي الاستعمال</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $product->indication }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">إجراءات سريعة</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('regulatory-affairs.batches.create', ['product_id' => $product->id]) }}" 
                       class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-vials ml-2"></i>
                        إضافة دفعة جديدة
                    </a>
                    
                    <a href="{{ route('regulatory-affairs.inspections.create', ['product_id' => $product->id]) }}" 
                       class="block w-full bg-indigo-600 text-white text-center py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-search ml-2"></i>
                        جدولة تفتيش
                    </a>
                    
                    <button onclick="updateStatus()" 
                            class="block w-full bg-orange-600 text-white text-center py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-cog ml-2"></i>
                        تحديث الحالة
                    </button>
                    
                    <a href="{{ route('regulatory-affairs.products.edit', $product) }}" 
                       class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-edit ml-2"></i>
                        تعديل المنتج
                    </a>
                </div>
            </div>

            <!-- Recent Batches -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">الدفعات الأخيرة</h3>
                    <a href="{{ route('regulatory-affairs.batches.index', ['product_id' => $product->id]) }}" 
                       class="text-green-600 hover:text-green-700 text-sm">عرض الكل</a>
                </div>
                
                <div class="space-y-3">
                    @forelse($recentBatches ?? [] as $batch)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-vials text-purple-600 text-xs"></i>
                        </div>
                        <div class="mr-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</p>
                            <p class="text-xs text-gray-500">{{ $batch->manufacturing_date ? $batch->manufacturing_date->format('Y-m-d') : 'غير محدد' }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($batch->batch_status === 'released') bg-green-100 text-green-800
                            @elseif($batch->batch_status === 'testing') bg-yellow-100 text-yellow-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ $batch->batch_status_arabic ?? $batch->batch_status }}
                        </span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 text-center py-4">لا توجد دفعات</p>
                    @endforelse
                </div>
            </div>

            <!-- Product Status -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">حالة المنتج</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">حالة الترخيص</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($product->license_status === 'active') bg-green-100 text-green-800
                            @elseif($product->license_status === 'expired') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $product->license_status_arabic }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">حالة التسويق</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($product->market_status === 'marketed') bg-green-100 text-green-800
                            @elseif($product->market_status === 'not_marketed') bg-gray-100 text-gray-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $product->market_status_arabic }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">أيام حتى انتهاء الترخيص</span>
                        <span class="text-sm font-medium {{ $product->isLicenseExpiring(30) ? 'text-orange-600' : 'text-gray-900' }}">
                            {{ $product->getDaysUntilLicenseExpiry() }}
                        </span>
                    </div>
                    
                    <div class="pt-4 border-t">
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-user ml-2"></i>
                            <span>أنشئ بواسطة: {{ $product->creator->name ?? 'غير محدد' }}</span>
                        </div>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <i class="fas fa-calendar ml-2"></i>
                            <span>تاريخ الإنشاء: {{ $product->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    @include('regulatory-affairs.components.document-upload', [
        'entityType' => 'product',
        'entityId' => $product->id,
        'entityName' => $product->display_trade_name
    ])
</div>

@push('scripts')
<script>
function updateStatus() {
    // This would open a modal to update product status
    alert('سيتم فتح نافذة تحديث حالة المنتج');
}
</script>
@endpush
@endsection
