@extends('layouts.app')

@section('title', 'تعديل الموظف - ' . $employee->full_name_ar)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">تعديل الموظف</h1>
            <p class="text-gray-600 mt-1">تعديل بيانات {{ $employee->full_name_ar }}</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('hr.employees.show', $employee) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-eye ml-2"></i>
                عرض التفاصيل
            </a>
            <a href="{{ route('hr.employees.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('hr.employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الشخصية</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Current Profile Photo -->
                @if($employee->profile_photo)
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الصورة الحالية</label>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <img class="h-16 w-16 rounded-full object-cover" src="{{ asset('storage/' . $employee->profile_photo) }}" alt="{{ $employee->full_name_ar }}">
                        <div>
                            <p class="text-sm text-gray-600">الصورة الحالية</p>
                            <p class="text-xs text-gray-500">اختر صورة جديدة لتغييرها</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">الاسم الأول (English)</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $employee->first_name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 @enderror" 
                           required>
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">اسم العائلة (English)</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $employee->last_name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name') border-red-500 @enderror" 
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Profile Photo -->
                <div>
                    <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">تغيير الصورة الشخصية</label>
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('profile_photo') border-red-500 @enderror">
                    @error('profile_photo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- First Name Arabic -->
                <div>
                    <label for="first_name_ar" class="block text-sm font-medium text-gray-700 mb-2">الاسم الأول (عربي)</label>
                    <input type="text" name="first_name_ar" id="first_name_ar" value="{{ old('first_name_ar', $employee->first_name_ar) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name_ar') border-red-500 @enderror" 
                           required>
                    @error('first_name_ar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name Arabic -->
                <div>
                    <label for="last_name_ar" class="block text-sm font-medium text-gray-700 mb-2">اسم العائلة (عربي)</label>
                    <input type="text" name="last_name_ar" id="last_name_ar" value="{{ old('last_name_ar', $employee->last_name_ar) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name_ar') border-red-500 @enderror" 
                           required>
                    @error('last_name_ar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" 
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile -->
                <div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700 mb-2">رقم الموبايل</label>
                    <input type="text" name="mobile" id="mobile" value="{{ old('mobile', $employee->mobile) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mobile') border-red-500 @enderror">
                    @error('mobile')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- National ID -->
                <div>
                    <label for="national_id" class="block text-sm font-medium text-gray-700 mb-2">رقم الهوية الوطنية</label>
                    <input type="text" name="national_id" id="national_id" value="{{ old('national_id', $employee->national_id) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('national_id') border-red-500 @enderror">
                    @error('national_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Passport Number -->
                <div>
                    <label for="passport_number" class="block text-sm font-medium text-gray-700 mb-2">رقم جواز السفر</label>
                    <input type="text" name="passport_number" id="passport_number" value="{{ old('passport_number', $employee->passport_number) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('passport_number') border-red-500 @enderror">
                    @error('passport_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">تاريخ الميلاد</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '') }}" 
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
                        <option value="male" {{ old('gender', $employee->gender) === 'male' ? 'selected' : '' }}>ذكر</option>
                        <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>أنثى</option>
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
                        <option value="single" {{ old('marital_status', $employee->marital_status) === 'single' ? 'selected' : '' }}>أعزب</option>
                        <option value="married" {{ old('marital_status', $employee->marital_status) === 'married' ? 'selected' : '' }}>متزوج</option>
                        <option value="divorced" {{ old('marital_status', $employee->marital_status) === 'divorced' ? 'selected' : '' }}>مطلق</option>
                        <option value="widowed" {{ old('marital_status', $employee->marital_status) === 'widowed' ? 'selected' : '' }}>أرمل</option>
                    </select>
                    @error('marital_status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nationality -->
                <div>
                    <label for="nationality" class="block text-sm font-medium text-gray-700 mb-2">الجنسية</label>
                    <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $employee->nationality) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nationality') border-red-500 @enderror">
                    @error('nationality')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات التوظيف</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">القسم</label>
                    <select name="department_id" id="department_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('department_id') border-red-500 @enderror">
                        <option value="">اختر القسم</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name_ar }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Position -->
                <div>
                    <label for="position_id" class="block text-sm font-medium text-gray-700 mb-2">المنصب</label>
                    <select name="position_id" id="position_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('position_id') border-red-500 @enderror">
                        <option value="">اختر المنصب</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>
                                {{ $position->title_ar }}
                            </option>
                        @endforeach
                    </select>
                    @error('position_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Manager -->
                <div>
                    <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-2">المدير المباشر</label>
                    <select name="manager_id" id="manager_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('manager_id') border-red-500 @enderror">
                        <option value="">اختر المدير المباشر</option>
                        @foreach($managers as $manager)
                            @if($manager->id !== $employee->id)
                                <option value="{{ $manager->id }}" {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->full_name_ar }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('manager_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hire Date -->
                <div>
                    <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">تاريخ التوظيف</label>
                    <input type="date" name="hire_date" id="hire_date" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hire_date') border-red-500 @enderror" 
                           required>
                    @error('hire_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Employment Type -->
                <div>
                    <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-2">نوع التوظيف</label>
                    <select name="employment_type" id="employment_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('employment_type') border-red-500 @enderror">
                        <option value="">اختر نوع التوظيف</option>
                        @foreach($employment_types_ar as $key => $type)
                            <option value="{{ $key }}" {{ old('employment_type', $employee->employment_type) === $key ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('employment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                    <select name="status" id="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                        <option value="">اختر الحالة</option>
                        @foreach($statuses_ar as $key => $status)
                            <option value="{{ $key }}" {{ old('status', $employee->status) === $key ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Salary Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الراتب</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Basic Salary -->
                <div>
                    <label for="basic_salary" class="block text-sm font-medium text-gray-700 mb-2">الراتب الأساسي</label>
                    <input type="number" name="basic_salary" id="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}"
                           step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('basic_salary') border-red-500 @enderror">
                    @error('basic_salary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hourly Rate -->
                <div>
                    <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-2">الأجر بالساعة</label>
                    <input type="number" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $employee->hourly_rate) }}"
                           step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hourly_rate') border-red-500 @enderror">
                    @error('hourly_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">العملة</label>
                    <select name="currency" id="currency"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('currency') border-red-500 @enderror">
                        <option value="IQD" {{ old('currency', $employee->currency) === 'IQD' ? 'selected' : '' }}>دينار عراقي (IQD)</option>
                        <option value="USD" {{ old('currency', $employee->currency) === 'USD' ? 'selected' : '' }}>دولار أمريكي (USD)</option>
                        <option value="EUR" {{ old('currency', $employee->currency) === 'EUR' ? 'selected' : '' }}>يورو (EUR)</option>
                    </select>
                    @error('currency')
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
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">{{ old('address', $employee->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">المدينة</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $employee->city) }}"
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
                        value="{{ old('governorate', $employee->governorate) }}"
                        class="{{ $errors->has('governorate') ? 'error' : '' }}"
                    />
                        <option value="Sulaymaniyah" {{ old('governorate', $employee->governorate) === 'Sulaymaniyah' ? 'selected' : '' }}>السليمانية</option>
                        <option value="Mosul" {{ old('governorate', $employee->governorate) === 'Mosul' ? 'selected' : '' }}>الموصل</option>
                        <option value="Kirkuk" {{ old('governorate', $employee->governorate) === 'Kirkuk' ? 'selected' : '' }}>كركوك</option>
                        <option value="Najaf" {{ old('governorate', $employee->governorate) === 'Najaf' ? 'selected' : '' }}>النجف</option>
                        <option value="Karbala" {{ old('governorate', $employee->governorate) === 'Karbala' ? 'selected' : '' }}>كربلاء</option>
                        <option value="Hillah" {{ old('governorate', $employee->governorate) === 'Hillah' ? 'selected' : '' }}>الحلة</option>
                        <option value="Ramadi" {{ old('governorate', $employee->governorate) === 'Ramadi' ? 'selected' : '' }}>الرمادي</option>
                        <option value="Fallujah" {{ old('governorate', $employee->governorate) === 'Fallujah' ? 'selected' : '' }}>الفلوجة</option>
                        <option value="Tikrit" {{ old('governorate', $employee->governorate) === 'Tikrit' ? 'selected' : '' }}>تكريت</option>
                        <option value="Samarra" {{ old('governorate', $employee->governorate) === 'Samarra' ? 'selected' : '' }}>سامراء</option>
                        <option value="Duhok" {{ old('governorate', $employee->governorate) === 'Duhok' ? 'selected' : '' }}>دهوك</option>
                        <option value="Amarah" {{ old('governorate', $employee->governorate) === 'Amarah' ? 'selected' : '' }}>العمارة</option>
                        <option value="Nasiriyah" {{ old('governorate', $employee->governorate) === 'Nasiriyah' ? 'selected' : '' }}>الناصرية</option>
                        <option value="Kut" {{ old('governorate', $employee->governorate) === 'Kut' ? 'selected' : '' }}>الكوت</option>
                        <option value="Diwaniyah" {{ old('governorate', $employee->governorate) === 'Diwaniyah' ? 'selected' : '' }}>الديوانية</option>
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
                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('emergency_contact_name') border-red-500 @enderror">
                    @error('emergency_contact_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Emergency Contact Phone -->
                <div>
                    <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"
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
                        <option value="Father" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Father' ? 'selected' : '' }}>الأب</option>
                        <option value="Mother" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Mother' ? 'selected' : '' }}>الأم</option>
                        <option value="Spouse" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Spouse' ? 'selected' : '' }}>الزوج/الزوجة</option>
                        <option value="Brother" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Brother' ? 'selected' : '' }}>الأخ</option>
                        <option value="Sister" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Sister' ? 'selected' : '' }}>الأخت</option>
                        <option value="Son" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Son' ? 'selected' : '' }}>الابن</option>
                        <option value="Daughter" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Daughter' ? 'selected' : '' }}>الابنة</option>
                        <option value="Friend" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Friend' ? 'selected' : '' }}>صديق</option>
                        <option value="Other" {{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) === 'Other' ? 'selected' : '' }}>أخرى</option>
                    </select>
                    @error('emergency_contact_relationship')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">ملاحظات</h3>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">ملاحظات إضافية</label>
                <textarea name="notes" id="notes" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes', $employee->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3 space-x-reverse">
            <a href="{{ route('hr.employees.show', $employee) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                إلغاء
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                <i class="fas fa-save ml-2"></i>
                حفظ التغييرات
            </button>
        </div>
    </form>
</div>
@endsection
