@extends('layouts.app')

@section('title', 'تفاصيل المورد - MaxCon ERP')
@section('page-title', 'تفاصيل المورد')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
            <p class="text-gray-600">{{ $supplier->name_ar ?? $supplier->name }}</p>
        </div>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-edit ml-2"></i>
                تعديل
            </a>
            <a href="{{ route('suppliers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex items-center space-x-4 space-x-reverse">
        @if($supplier->status === 'active')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                <i class="fas fa-check-circle ml-1"></i>
                نشط
            </span>
        @elseif($supplier->status === 'inactive')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                <i class="fas fa-pause-circle ml-1"></i>
                غير نشط
            </span>
        @elseif($supplier->status === 'suspended')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                <i class="fas fa-exclamation-circle ml-1"></i>
                معلق
            </span>
        @elseif($supplier->status === 'blocked')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                <i class="fas fa-ban ml-1"></i>
                محظور
            </span>
        @endif

        @if($supplier->is_preferred)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-star ml-1"></i>
                مورد مفضل
            </span>
        @endif

        @if($supplier->rating)
            <div class="flex items-center">
                <span class="text-sm text-gray-600 ml-2">التقييم:</span>
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $supplier->rating)
                            <i class="fas fa-star text-yellow-400"></i>
                        @else
                            <i class="far fa-star text-gray-300"></i>
                        @endif
                    @endfor
                    <span class="text-sm text-gray-600 mr-1">({{ number_format($supplier->rating, 1) }})</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-green-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">إجمالي المشتريات</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($supplier->total_purchases ?? 0) }} د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">المبلغ المستحق</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($supplier->outstanding_amount ?? 0) }} د.ع</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-invoice text-blue-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">عدد الطلبات</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $supplier->purchaseOrders->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 card-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-boxes text-purple-600"></i>
                    </div>
                </div>
                <div class="mr-4">
                    <p class="text-sm font-medium text-gray-500">عدد المنتجات</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $supplier->products->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Supplier Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">المعلومات الأساسية</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم المورد</label>
                            <p class="text-sm text-gray-900">{{ $supplier->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الاسم بالعربية</label>
                            <p class="text-sm text-gray-900">{{ $supplier->name_ar ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
                            <p class="text-sm text-gray-900">
                                @switch($supplier->type)
                                    @case('manufacturer')
                                        مصنع
                                        @break
                                    @case('distributor')
                                        موزع
                                        @break
                                    @case('wholesaler')
                                        تاجر جملة
                                        @break
                                    @case('importer')
                                        مستورد
                                        @break
                                    @case('local_supplier')
                                        مورد محلي
                                        @break
                                    @default
                                        {{ $supplier->type }}
                                @endswitch
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">كود المورد</label>
                            <p class="text-sm text-gray-900 font-mono">{{ $supplier->code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->email }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الموقع الإلكتروني</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->website)
                                    <a href="{{ $supplier->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $supplier->website }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات الاتصال</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->phone)
                                    <a href="tel:{{ $supplier->phone }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->phone }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الجوال</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->mobile)
                                    <a href="tel:{{ $supplier->mobile }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->mobile }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الفاكس</label>
                            <p class="text-sm text-gray-900">{{ $supplier->fax ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الشخص المسؤول</label>
                            <p class="text-sm text-gray-900">{{ $supplier->contact_person ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">هاتف المسؤول</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->contact_phone)
                                    <a href="tel:{{ $supplier->contact_phone }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->contact_phone }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">بريد المسؤول</label>
                            <p class="text-sm text-gray-900">
                                @if($supplier->contact_email)
                                    <a href="mailto:{{ $supplier->contact_email }}" class="text-blue-600 hover:text-blue-800">{{ $supplier->contact_email }}</a>
                                @else
                                    غير محدد
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات العنوان</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                            <p class="text-sm text-gray-900">{{ $supplier->address ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">المدينة</label>
                            <p class="text-sm text-gray-900">{{ $supplier->city ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">المحافظة</label>
                            <p class="text-sm text-gray-900">{{ $supplier->state ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">البلد</label>
                            <p class="text-sm text-gray-900">{{ $supplier->country ?? 'غير محدد' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الرمز البريدي</label>
                            <p class="text-sm text-gray-900">{{ $supplier->postal_code ?? 'غير محدد' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Financial Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">المعلومات المالية</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">شروط الدفع</label>
                        <p class="text-sm text-gray-900">{{ $supplier->payment_terms ?? 0 }} يوم</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">حد الائتمان</label>
                        <p class="text-sm text-gray-900">{{ number_format($supplier->credit_limit ?? 0) }} {{ $supplier->currency ?? 'د.ع' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">العملة</label>
                        <p class="text-sm text-gray-900">{{ $supplier->currency ?? 'د.ع' }}</p>
                    </div>
                </div>
            </div>

            <!-- Legal Information -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">المعلومات القانونية</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الرقم الضريبي</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $supplier->tax_number ?? 'غير محدد' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">رقم الترخيص</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $supplier->license_number ?? 'غير محدد' }}</p>
                    </div>
                </div>
            </div>

            <!-- Banking Information -->
            @if($supplier->bank_name || $supplier->bank_account || $supplier->iban || $supplier->swift_code)
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">المعلومات المصرفية</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($supplier->bank_name)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اسم البنك</label>
                        <p class="text-sm text-gray-900">{{ $supplier->bank_name }}</p>
                    </div>
                    @endif
                    @if($supplier->bank_account)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">رقم الحساب</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $supplier->bank_account }}</p>
                    </div>
                    @endif
                    @if($supplier->iban)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $supplier->iban }}</p>
                    </div>
                    @endif
                    @if($supplier->swift_code)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SWIFT Code</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $supplier->swift_code }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($supplier->notes)
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">ملاحظات</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-900">{{ $supplier->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Timestamps -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">معلومات النظام</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإنشاء</label>
                        <p class="text-sm text-gray-900">{{ $supplier->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">آخر تحديث</label>
                        <p class="text-sm text-gray-900">{{ $supplier->updated_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
