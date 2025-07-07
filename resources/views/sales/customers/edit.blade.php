@extends('layouts.app')

@section('page-title', 'تعديل العميل')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تعديل العميل: {{ $customer->name }}</h1>
            <p class="text-gray-600 mt-1">تعديل بيانات العميل</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
            <a href="{{ route('sales.customers.show', $customer) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-eye ml-2"></i>
                عرض العميل
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('sales.customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم العميل *</label>
                            <input type="text" name="name" value="{{ old('name', $customer->name) }}" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الاسم بالعربية</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar', $customer->name_ar) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name_ar') border-red-500 @enderror">
                            @error('name_ar')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نوع العميل *</label>
                            <x-searchable-dropdown
                                name="type"
                                placeholder="اختر نوع العميل..."
                                search-placeholder="ابحث في أنواع العملاء..."
                                :options="collect($types)->map(fn($label, $key) => [
                                    'value' => $key,
                                    'text' => ($types_ar[$key] ?? $label)
                                ])->values()->toArray()"
                                value="{{ old('type', $customer->type) }}"
                                required
                                class="{{ $errors->has('type') ? 'error' : '' }}"
                            />
                            @error('type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رمز العميل</label>
                            <input type="text" name="code" value="{{ old('code', $customer->code) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror">
                            @error('code')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات الاتصال</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رقم الموبايل</label>
                            <input type="text" name="mobile" value="{{ old('mobile', $customer->mobile) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('mobile') border-red-500 @enderror">
                            @error('mobile')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المحافظة</label>
                            <x-searchable-dropdown
                                name="governorate"
                                placeholder="اختر المحافظة..."
                                search-placeholder="ابحث في المحافظات..."
                                :options="collect(['' => 'اختر المحافظة'])->merge($governorates)->map(fn($name, $key) => [
                                    'value' => $key,
                                    'text' => $name
                                ])->values()->toArray()"
                                value="{{ old('governorate', $customer->governorate) }}"
                                class="{{ $errors->has('governorate') ? 'error' : '' }}"
                            />
                            @error('governorate')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المدينة</label>
                            <input type="text" name="city" value="{{ old('city', $customer->city) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror">
                            @error('city')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الرمز البريدي</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('postal_code') border-red-500 @enderror">
                            @error('postal_code')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">العنوان</label>
                            <textarea name="address" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Business Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات التجارية</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الرقم الضريبي</label>
                            <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('tax_number') border-red-500 @enderror">
                            @error('tax_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رقم الترخيص</label>
                            <input type="text" name="license_number" value="{{ old('license_number', $customer->license_number) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('license_number') border-red-500 @enderror">
                            @error('license_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحد الائتماني</label>
                            <input type="number" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" 
                                   min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('credit_limit') border-red-500 @enderror">
                            @error('credit_limit')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">شروط الدفع (بالأيام)</label>
                            <input type="number" name="payment_terms" value="{{ old('payment_terms', $customer->payment_terms) }}" 
                                   min="0" max="365"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('payment_terms') border-red-500 @enderror">
                            @error('payment_terms')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نسبة الخصم (%)</label>
                            <input type="number" name="discount_percentage" value="{{ old('discount_percentage', $customer->discount_percentage) }}" 
                                   min="0" max="100" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('discount_percentage') border-red-500 @enderror">
                            @error('discount_percentage')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                            <select name="is_active" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('is_active') border-red-500 @enderror">
                                <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>غير نشط</option>
                            </select>
                            @error('is_active')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Person -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">الشخص المسؤول</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم الشخص المسؤول</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('contact_person') border-red-500 @enderror">
                            @error('contact_person')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">هاتف الشخص المسؤول</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $customer->contact_phone) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('contact_phone') border-red-500 @enderror">
                            @error('contact_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">بريد الشخص المسؤول</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $customer->contact_email) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('contact_email') border-red-500 @enderror">
                            @error('contact_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">ملاحظات</h3>
                    <div>
                        <textarea name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                                  placeholder="أي ملاحظات إضافية حول العميل...">{{ old('notes', $customer->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <a href="{{ route('sales.customers.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    إلغاء
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save ml-2"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
