@extends('layouts.app')

@section('title', 'إضافة مستأجر جديد')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">إضافة مستأجر جديد</h1>
            <p class="text-gray-600 mt-1">إنشاء حساب مستأجر جديد مع مدير النظام</p>
        </div>
        <a href="{{ route('master-admin.tenants.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-right ml-2"></i>
            العودة للقائمة
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('master-admin.tenants.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Company Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الشركة</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">اسم المستأجر</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">اسم الشركة</label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('company_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">الشخص المسؤول</label>
                        <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('contact_person')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">النطاق (Domain)</label>
                        <input type="text" id="domain" name="domain" value="{{ old('domain') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="example" required>
                        <p class="text-sm text-gray-500 mt-1">سيكون الرابط: https://example.maxcon-erp.com</p>
                        @error('domain')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company_type" class="block text-sm font-medium text-gray-700 mb-2">نوع الشركة</label>
                        <select id="company_type" name="company_type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                required>
                            <option value="">اختر نوع الشركة</option>
                            <option value="pharmacy" {{ old('company_type') == 'pharmacy' ? 'selected' : '' }}>صيدلية</option>
                            <option value="medical_distributor" {{ old('company_type') == 'medical_distributor' ? 'selected' : '' }}>موزع أدوية</option>
                            <option value="clinic" {{ old('company_type') == 'clinic' ? 'selected' : '' }}>عيادة</option>
                            <option value="hospital" {{ old('company_type') == 'hospital' ? 'selected' : '' }}>مستشفى</option>
                            <option value="other" {{ old('company_type') == 'other' ? 'selected' : '' }}>أخرى</option>
                        </select>
                        @error('company_type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_users" class="block text-sm font-medium text-gray-700 mb-2">الحد الأقصى للمستخدمين</label>
                        <input type="number" id="max_users" name="max_users" value="{{ old('max_users', 10) }}" 
                               min="1" max="1000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                        @error('max_users')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الاتصال</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف <span class="text-red-500">*</span></label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">العنوان <span class="text-red-500">*</span></label>
                        <textarea id="address" name="address" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  required>{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">المدينة <span class="text-red-500">*</span></label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('city')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="governorate" class="block text-sm font-medium text-gray-700 mb-2">المحافظة <span class="text-red-500">*</span></label>
                        <select id="governorate" name="governorate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">اختر المحافظة</option>
                            <option value="بغداد" {{ old('governorate') == 'بغداد' ? 'selected' : '' }}>بغداد</option>
                            <option value="البصرة" {{ old('governorate') == 'البصرة' ? 'selected' : '' }}>البصرة</option>
                            <option value="نينوى" {{ old('governorate') == 'نينوى' ? 'selected' : '' }}>نينوى</option>
                            <option value="أربيل" {{ old('governorate') == 'أربيل' ? 'selected' : '' }}>أربيل</option>
                            <option value="النجف" {{ old('governorate') == 'النجف' ? 'selected' : '' }}>النجف</option>
                            <option value="كربلاء" {{ old('governorate') == 'كربلاء' ? 'selected' : '' }}>كربلاء</option>
                            <option value="بابل" {{ old('governorate') == 'بابل' ? 'selected' : '' }}>بابل</option>
                            <option value="الأنبار" {{ old('governorate') == 'الأنبار' ? 'selected' : '' }}>الأنبار</option>
                            <option value="ديالى" {{ old('governorate') == 'ديالى' ? 'selected' : '' }}>ديالى</option>
                            <option value="ذي قار" {{ old('governorate') == 'ذي قار' ? 'selected' : '' }}>ذي قار</option>
                            <option value="المثنى" {{ old('governorate') == 'المثنى' ? 'selected' : '' }}>المثنى</option>
                            <option value="القادسية" {{ old('governorate') == 'القادسية' ? 'selected' : '' }}>القادسية</option>
                            <option value="ميسان" {{ old('governorate') == 'ميسان' ? 'selected' : '' }}>ميسان</option>
                            <option value="واسط" {{ old('governorate') == 'واسط' ? 'selected' : '' }}>واسط</option>
                            <option value="كركوك" {{ old('governorate') == 'كركوك' ? 'selected' : '' }}>كركوك</option>
                            <option value="صلاح الدين" {{ old('governorate') == 'صلاح الدين' ? 'selected' : '' }}>صلاح الدين</option>
                            <option value="دهوك" {{ old('governorate') == 'دهوك' ? 'selected' : '' }}>دهوك</option>
                            <option value="السليمانية" {{ old('governorate') == 'السليمانية' ? 'selected' : '' }}>السليمانية</option>
                        </select>
                        @error('governorate')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Admin User Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات مدير النظام</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-2">اسم المدير</label>
                        <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                        @error('admin_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني للمدير</label>
                        <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                        @error('admin_email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور</label>
                        <input type="password" id="admin_password" name="admin_password" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required minlength="8">
                        <p class="text-sm text-gray-500 mt-1">يجب أن تكون 8 أحرف على الأقل</p>
                        @error('admin_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">تأكيد كلمة المرور</label>
                        <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required minlength="8">
                        @error('admin_password_confirmation')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- License Information -->
            <div class="pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الترخيص</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="license_expires_at" class="block text-sm font-medium text-gray-700 mb-2">تاريخ انتهاء الترخيص</label>
                        <input type="date" id="license_expires_at" name="license_expires_at" 
                               value="{{ old('license_expires_at', now()->addYear()->format('Y-m-d')) }}" 
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                        @error('license_expires_at')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4 space-x-reverse pt-6 border-t border-gray-200">
                <a href="{{ route('master-admin.tenants.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-save ml-2"></i>
                    إنشاء المستأجر
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-generate domain from company name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const domain = name.toLowerCase()
                      .replace(/[^a-z0-9\s]/g, '')
                      .replace(/\s+/g, '-')
                      .substring(0, 20);
    document.getElementById('domain').value = domain;
});

// Validate password confirmation
document.getElementById('admin_password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('admin_password').value;
    const confirmation = this.value;
    
    if (password !== confirmation) {
        this.setCustomValidity('كلمات المرور غير متطابقة');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endpush
