@extends('layouts.app')

@section('title', 'إضافة موظف جديد')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">إضافة موظف جديد</h1>
            <p class="text-gray-600 mt-1">إدخال بيانات الموظف الجديد</p>
        </div>
        <a href="{{ route('hr.employees.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
            <i class="fas fa-arrow-right ml-2"></i>
            العودة للقائمة
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('hr.employees.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الشخصية</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">الاسم الأول (English)</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 @enderror" 
                           required>
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">اسم العائلة (English)</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name') border-red-500 @enderror" 
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Profile Photo -->
                <div>
                    <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">الصورة الشخصية</label>
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('profile_photo') border-red-500 @enderror">
                    @error('profile_photo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- First Name Arabic -->
                <div>
                    <label for="first_name_ar" class="block text-sm font-medium text-gray-700 mb-2">الاسم الأول (عربي)</label>
                    <input type="text" name="first_name_ar" id="first_name_ar" value="{{ old('first_name_ar') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name_ar') border-red-500 @enderror" 
                           required>
                    @error('first_name_ar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name Arabic -->
                <div>
                    <label for="last_name_ar" class="block text-sm font-medium text-gray-700 mb-2">اسم العائلة (عربي)</label>
                    <input type="text" name="last_name_ar" id="last_name_ar" value="{{ old('last_name_ar') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name_ar') border-red-500 @enderror" 
                           required>
                    @error('last_name_ar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" 
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile -->
                <div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700 mb-2">رقم الموبايل</label>
                    <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mobile') border-red-500 @enderror">
                    @error('mobile')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- National ID -->
                <div>
                    <label for="national_id" class="block text-sm font-medium text-gray-700 mb-2">رقم الهوية الوطنية</label>
                    <input type="text" name="national_id" id="national_id" value="{{ old('national_id') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('national_id') border-red-500 @enderror">
                    @error('national_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">تاريخ الميلاد</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('date_of_birth') border-red-500 @enderror">
                    @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gender -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">الجنس</label>
                    <select name="gender" id="gender" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('gender') border-red-500 @enderror" 
                            required>
                        <option value="">اختر الجنس</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>ذكر</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>أنثى</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Marital Status -->
                <div>
                    <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">الحالة الاجتماعية</label>
                    <select name="marital_status" id="marital_status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('marital_status') border-red-500 @enderror">
                        <option value="">اختر الحالة الاجتماعية</option>
                        <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>أعزب</option>
                        <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>متزوج</option>
                        <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>مطلق</option>
                        <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>أرمل</option>
                    </select>
                    @error('marital_status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nationality -->
                <div>
                    <label for="nationality" class="block text-sm font-medium text-gray-700 mb-2">الجنسية</label>
                    <input type="text" name="nationality" id="nationality" value="{{ old('nationality', 'Iraqi') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nationality') border-red-500 @enderror">
                    @error('nationality')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات العنوان</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">العنوان</label>
                    <textarea name="address" id="address" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">المدينة</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('city') border-red-500 @enderror">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Governorate -->
                <div>
                    <label for="governorate" class="block text-sm font-medium text-gray-700 mb-2">المحافظة</label>
                    <x-searchable-dropdown
                        name="governorate"
                        placeholder="اختر المحافظة"
                        search-placeholder="ابحث في المحافظات..."
                        :options="[
                            ['value' => '', 'text' => 'اختر المحافظة'],
                            ['value' => 'Baghdad', 'text' => 'بغداد'],
                            ['value' => 'Basra', 'text' => 'البصرة'],
                            ['value' => 'Erbil', 'text' => 'أربيل'],
                            ['value' => 'Sulaymaniyah', 'text' => 'السليمانية']
                        ]"
                        value="{{ old('governorate') }}"
                        class="{{ $errors->has('governorate') ? 'error' : '' }}"
                    />
                        <option value="Mosul" {{ old('governorate') === 'Mosul' ? 'selected' : '' }}>الموصل</option>
                        <option value="Kirkuk" {{ old('governorate') === 'Kirkuk' ? 'selected' : '' }}>كركوك</option>
                        <option value="Najaf" {{ old('governorate') === 'Najaf' ? 'selected' : '' }}>النجف</option>
                        <option value="Karbala" {{ old('governorate') === 'Karbala' ? 'selected' : '' }}>كربلاء</option>
                        <option value="Hillah" {{ old('governorate') === 'Hillah' ? 'selected' : '' }}>الحلة</option>
                        <option value="Ramadi" {{ old('governorate') === 'Ramadi' ? 'selected' : '' }}>الرمادي</option>
                        <option value="Fallujah" {{ old('governorate') === 'Fallujah' ? 'selected' : '' }}>الفلوجة</option>
                        <option value="Tikrit" {{ old('governorate') === 'Tikrit' ? 'selected' : '' }}>تكريت</option>
                        <option value="Samarra" {{ old('governorate') === 'Samarra' ? 'selected' : '' }}>سامراء</option>
                        <option value="Duhok" {{ old('governorate') === 'Duhok' ? 'selected' : '' }}>دهوك</option>
                        <option value="Amarah" {{ old('governorate') === 'Amarah' ? 'selected' : '' }}>العمارة</option>
                        <option value="Nasiriyah" {{ old('governorate') === 'Nasiriyah' ? 'selected' : '' }}>الناصرية</option>
                        <option value="Kut" {{ old('governorate') === 'Kut' ? 'selected' : '' }}>الكوت</option>
                        <option value="Diwaniyah" {{ old('governorate') === 'Diwaniyah' ? 'selected' : '' }}>الديوانية</option>
                    </select>
                    @error('governorate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">جهة الاتصال في حالات الطوارئ</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Emergency Contact Name -->
                <div>
                    <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">اسم جهة الاتصال</label>
                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('emergency_contact_name') border-red-500 @enderror">
                    @error('emergency_contact_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Emergency Contact Phone -->
                <div>
                    <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('emergency_contact_phone') border-red-500 @enderror">
                    @error('emergency_contact_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Emergency Contact Relationship -->
                <div>
                    <label for="emergency_contact_relationship" class="block text-sm font-medium text-gray-700 mb-2">صلة القرابة</label>
                    <select name="emergency_contact_relationship" id="emergency_contact_relationship" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('emergency_contact_relationship') border-red-500 @enderror">
                        <option value="">اختر صلة القرابة</option>
                        <option value="Father" {{ old('emergency_contact_relationship') === 'Father' ? 'selected' : '' }}>الأب</option>
                        <option value="Mother" {{ old('emergency_contact_relationship') === 'Mother' ? 'selected' : '' }}>الأم</option>
                        <option value="Spouse" {{ old('emergency_contact_relationship') === 'Spouse' ? 'selected' : '' }}>الزوج/الزوجة</option>
                        <option value="Brother" {{ old('emergency_contact_relationship') === 'Brother' ? 'selected' : '' }}>الأخ</option>
                        <option value="Sister" {{ old('emergency_contact_relationship') === 'Sister' ? 'selected' : '' }}>الأخت</option>
                        <option value="Son" {{ old('emergency_contact_relationship') === 'Son' ? 'selected' : '' }}>الابن</option>
                        <option value="Daughter" {{ old('emergency_contact_relationship') === 'Daughter' ? 'selected' : '' }}>الابنة</option>
                        <option value="Friend" {{ old('emergency_contact_relationship') === 'Friend' ? 'selected' : '' }}>صديق</option>
                        <option value="Other" {{ old('emergency_contact_relationship') === 'Other' ? 'selected' : '' }}>أخرى</option>
                    </select>
                    @error('emergency_contact_relationship')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3 space-x-reverse">
            <a href="{{ route('hr.employees.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                إلغاء
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                <i class="fas fa-save ml-2"></i>
                حفظ الموظف
            </button>
        </div>
    </form>
</div>
@endsection
