{{--
    @var \App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany $company
    Note: $company is passed as an Eloquent model object from the controller
--}}
@php
    /** @var \App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany $company */
@endphp
@extends('layouts.app')

@section('title', 'تعديل الشركة - ' . ($company->display_name ?? 'غير محدد'))
@section('page-title', 'تعديل الشركة')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">تعديل الشركة</h1>
                <p class="text-blue-100">{{ $company->display_name }}</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-building"></i>
            </div>
        </div>
    </div>

    <form action="{{ route('regulatory-affairs.companies.update', $company) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Basic Information -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم التسجيل *</label>
                    <input type="text" name="registration_number" value="{{ old('registration_number', $company->registration_number) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('registration_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم الشركة *</label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم الشركة بالعربية</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $company->name_ar) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('name_ar')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الاسم التجاري</label>
                    <input type="text" name="trade_name" value="{{ old('trade_name', $company->trade_name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('trade_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الاسم التجاري بالعربية</label>
                    <input type="text" name="trade_name_ar" value="{{ old('trade_name_ar', $company->trade_name_ar) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('trade_name_ar')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الشركة *</label>
                    <select name="company_type" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر نوع الشركة</option>
                        <option value="manufacturer" {{ old('company_type', $company->company_type) == 'manufacturer' ? 'selected' : '' }}>مصنع</option>
                        <option value="distributor" {{ old('company_type', $company->company_type) == 'distributor' ? 'selected' : '' }}>موزع</option>
                        <option value="importer" {{ old('company_type', $company->company_type) == 'importer' ? 'selected' : '' }}>مستورد</option>
                        <option value="exporter" {{ old('company_type', $company->company_type) == 'exporter' ? 'selected' : '' }}>مصدر</option>
                        <option value="wholesaler" {{ old('company_type', $company->company_type) == 'wholesaler' ? 'selected' : '' }}>تاجر جملة</option>
                        <option value="retailer" {{ old('company_type', $company->company_type) == 'retailer' ? 'selected' : '' }}>تاجر تجزئة</option>
                    </select>
                    @error('company_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع المصنع</label>
                    <select name="manufacturer_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر نوع المصنع</option>
                        <option value="local" {{ old('manufacturer_type', $company->manufacturer_type) == 'local' ? 'selected' : '' }}>محلي</option>
                        <option value="foreign" {{ old('manufacturer_type', $company->manufacturer_type) == 'foreign' ? 'selected' : '' }}>أجنبي</option>
                        <option value="joint_venture" {{ old('manufacturer_type', $company->manufacturer_type) == 'joint_venture' ? 'selected' : '' }}>مشترك</option>
                    </select>
                    @error('manufacturer_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">بلد المنشأ *</label>
                    <input type="text" name="country_of_origin" value="{{ old('country_of_origin', $company->country_of_origin) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('country_of_origin')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الترخيص</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم الترخيص *</label>
                    <input type="text" name="license_number" value="{{ old('license_number', $company->license_number) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('license_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الجهة المنظمة *</label>
                    <input type="text" name="regulatory_authority" value="{{ old('regulatory_authority', $company->regulatory_authority) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('regulatory_authority')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ إصدار الترخيص *</label>
                    <input type="date" name="license_issue_date" value="{{ old('license_issue_date', $company->license_issue_date?->format('Y-m-d')) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('license_issue_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ انتهاء الترخيص *</label>
                    <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date', $company->license_expiry_date?->format('Y-m-d')) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('license_expiry_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">حالة الترخيص *</label>
                    <select name="license_status" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر حالة الترخيص</option>
                        <option value="active" {{ old('license_status', $company->license_status) == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="expired" {{ old('license_status', $company->license_status) == 'expired' ? 'selected' : '' }}>منتهي</option>
                        <option value="suspended" {{ old('license_status', $company->license_status) == 'suspended' ? 'selected' : '' }}>معلق</option>
                        <option value="cancelled" {{ old('license_status', $company->license_status) == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                    </select>
                    @error('license_status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الاتصال</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">العنوان *</label>
                    <textarea name="address" required rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('address', $company->address) }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المدينة *</label>
                    <input type="text" name="city" value="{{ old('city', $company->city) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('city')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف *</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $company->email) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الموقع الإلكتروني</label>
                    <input type="url" name="website" value="{{ old('website', $company->website) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('website')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الشخص المسؤول *</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $company->contact_person) }}" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @error('contact_person')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 space-x-reverse">
            <a href="{{ route('regulatory-affairs.companies.show', $company) }}" 
               class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                إلغاء
            </a>
            <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-save ml-2"></i>
                حفظ التغييرات
            </button>
        </div>
    </form>

    <!-- Documents Section -->
    @include('regulatory-affairs.components.document-upload', [
        'entityType' => 'company',
        'entityId' => $company->id,
        'entityName' => $company->display_name
    ])
</div>
@endsection
