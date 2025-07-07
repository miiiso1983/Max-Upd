@extends('layouts.app')

@section('title', 'إضافة شركة دوائية جديدة - MaxCon ERP')
@section('page-title', 'إضافة شركة دوائية جديدة')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">إضافة شركة دوائية جديدة</h1>
                <p class="text-blue-100">تسجيل شركة دوائية جديدة في النظام</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-plus-circle"></i>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('regulatory-affairs.companies.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم التسجيل *</label>
                    <input type="text" name="registration_number" value="{{ old('registration_number') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('registration_number') border-red-500 @enderror">
                    @error('registration_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم الشركة (إنجليزي) *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم الشركة (عربي)</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name_ar') border-red-500 @enderror">
                    @error('name_ar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الاسم التجاري</label>
                    <input type="text" name="trade_name" value="{{ old('trade_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('trade_name') border-red-500 @enderror">
                    @error('trade_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الاسم التجاري (عربي)</label>
                    <input type="text" name="trade_name_ar" value="{{ old('trade_name_ar') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('trade_name_ar') border-red-500 @enderror">
                    @error('trade_name_ar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">بلد المنشأ *</label>
                    <select name="country_of_origin" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('country_of_origin') border-red-500 @enderror">
                        <option value="">اختر بلد المنشأ</option>
                        <option value="Iraq" {{ old('country_of_origin') === 'Iraq' ? 'selected' : '' }}>العراق</option>
                        <option value="Jordan" {{ old('country_of_origin') === 'Jordan' ? 'selected' : '' }}>الأردن</option>
                        <option value="Egypt" {{ old('country_of_origin') === 'Egypt' ? 'selected' : '' }}>مصر</option>
                        <option value="Lebanon" {{ old('country_of_origin') === 'Lebanon' ? 'selected' : '' }}>لبنان</option>
                        <option value="Turkey" {{ old('country_of_origin') === 'Turkey' ? 'selected' : '' }}>تركيا</option>
                        <option value="India" {{ old('country_of_origin') === 'India' ? 'selected' : '' }}>الهند</option>
                        <option value="Germany" {{ old('country_of_origin') === 'Germany' ? 'selected' : '' }}>ألمانيا</option>
                        <option value="France" {{ old('country_of_origin') === 'France' ? 'selected' : '' }}>فرنسا</option>
                        <option value="USA" {{ old('country_of_origin') === 'USA' ? 'selected' : '' }}>الولايات المتحدة</option>
                    </select>
                    @error('country_of_origin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الشركة *</label>
                    <select name="company_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('company_type') border-red-500 @enderror">
                        <option value="">اختر نوع الشركة</option>
                        <option value="manufacturer" {{ old('company_type') === 'manufacturer' ? 'selected' : '' }}>مصنع</option>
                        <option value="distributor" {{ old('company_type') === 'distributor' ? 'selected' : '' }}>موزع</option>
                        <option value="importer" {{ old('company_type') === 'importer' ? 'selected' : '' }}>مستورد</option>
                        <option value="exporter" {{ old('company_type') === 'exporter' ? 'selected' : '' }}>مصدر</option>
                    </select>
                    @error('company_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع المصنع</label>
                    <select name="manufacturer_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('manufacturer_type') border-red-500 @enderror">
                        <option value="">اختر نوع المصنع</option>
                        <option value="local" {{ old('manufacturer_type') === 'local' ? 'selected' : '' }}>محلي</option>
                        <option value="international" {{ old('manufacturer_type') === 'international' ? 'selected' : '' }}>دولي</option>
                    </select>
                    @error('manufacturer_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">وصف الشركة</label>
                <textarea name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- License Information -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الترخيص</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم الترخيص *</label>
                    <input type="text" name="license_number" value="{{ old('license_number') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('license_number') border-red-500 @enderror">
                    @error('license_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الجهة المنظمة *</label>
                    <input type="text" name="regulatory_authority" value="{{ old('regulatory_authority', 'وزارة الصحة العراقية') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('regulatory_authority') border-red-500 @enderror">
                    @error('regulatory_authority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ إصدار الترخيص *</label>
                    <input type="date" name="license_issue_date" value="{{ old('license_issue_date') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('license_issue_date') border-red-500 @enderror">
                    @error('license_issue_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ انتهاء الترخيص *</label>
                    <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('license_expiry_date') border-red-500 @enderror">
                    @error('license_expiry_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">حالة الترخيص</label>
                    <select name="license_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('license_status') border-red-500 @enderror">
                        <option value="active" {{ old('license_status', 'active') === 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="expired" {{ old('license_status') === 'expired' ? 'selected' : '' }}>منتهي</option>
                        <option value="suspended" {{ old('license_status') === 'suspended' ? 'selected' : '' }}>معلق</option>
                        <option value="cancelled" {{ old('license_status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
                    </select>
                    @error('license_status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">مستوى المخاطر</label>
                    <select name="risk_level"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('risk_level') border-red-500 @enderror">
                        <option value="low" {{ old('risk_level') === 'low' ? 'selected' : '' }}>منخفض</option>
                        <option value="medium" {{ old('risk_level', 'medium') === 'medium' ? 'selected' : '' }}>متوسط</option>
                        <option value="high" {{ old('risk_level') === 'high' ? 'selected' : '' }}>عالي</option>
                    </select>
                    @error('risk_level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                    <textarea name="address" rows="2" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المدينة *</label>
                    <input type="text" name="city" value="{{ old('city') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المحافظة/الولاية</label>
                    <input type="text" name="state" value="{{ old('state') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('state') border-red-500 @enderror">
                    @error('state')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الهاتف *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الشخص المسؤول *</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('contact_person') border-red-500 @enderror">
                    @error('contact_person')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">منصب الشخص المسؤول</label>
                    <input type="text" name="contact_person_title" value="{{ old('contact_person_title') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('contact_person_title') border-red-500 @enderror">
                    @error('contact_person_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-3 space-x-reverse">
            <a href="{{ route('regulatory-affairs.companies.index') }}" 
               class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                إلغاء
            </a>
            <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-save ml-2"></i>
                حفظ الشركة
            </button>
        </div>
    </form>
</div>
@endsection
