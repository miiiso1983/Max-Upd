@extends('layouts.app')

@section('page-title', 'إضافة عميل جديد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إضافة عميل جديد</h1>
            <p class="text-gray-600 mt-1">إضافة عميل جديد إلى قاعدة البيانات</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('sales.customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('sales.customers.store') }}" method="POST">
            @csrf
            
            <div class="p-6">
                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم العميل *</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نوع العميل *</label>
                            <x-searchable-dropdown
                                name="customer_type"
                                placeholder="اختر نوع العميل..."
                                search-placeholder="ابحث في أنواع العملاء..."
                                :options="[
                                    ['value' => 'individual', 'text' => '👤 فرد'],
                                    ['value' => 'company', 'text' => '🏢 شركة'],
                                    ['value' => 'pharmacy', 'text' => '💊 صيدلية'],
                                    ['value' => 'clinic', 'text' => '🏥 عيادة'],
                                    ['value' => 'hospital', 'text' => '🏥 مستشفى'],
                                    ['value' => 'laboratory', 'text' => '🔬 مختبر']
                                ]"
                                value="{{ old('customer_type') }}"
                                required
                                class="{{ $errors->has('customer_type') ? 'error' : '' }}"
                            />
                            @error('customer_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف *</label>
                            <input type="tel" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                            <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المحافظة *</label>
                            <x-searchable-dropdown
                                name="governorate"
                                placeholder="اختر المحافظة..."
                                search-placeholder="ابحث في المحافظات..."
                                :options="[
                                    ['value' => 'baghdad', 'text' => '🏛️ بغداد'],
                                    ['value' => 'basra', 'text' => '🏭 البصرة'],
                                    ['value' => 'mosul', 'text' => '🏛️ الموصل'],
                                    ['value' => 'erbil', 'text' => '🏛️ أربيل'],
                                    ['value' => 'najaf', 'text' => '🕌 النجف'],
                                    ['value' => 'karbala', 'text' => '🕌 كربلاء'],
                                    ['value' => 'hillah', 'text' => '🏛️ الحلة'],
                                    ['value' => 'ramadi', 'text' => '🏛️ الرمادي'],
                                    ['value' => 'tikrit', 'text' => '🏛️ تكريت'],
                                    ['value' => 'samarra', 'text' => '🏛️ سامراء'],
                                    ['value' => 'fallujah', 'text' => '🏛️ الفلوجة'],
                                    ['value' => 'kirkuk', 'text' => '🏭 كركوك'],
                                    ['value' => 'sulaymaniyah', 'text' => '🏛️ السليمانية'],
                                    ['value' => 'duhok', 'text' => '🏛️ دهوك'],
                                    ['value' => 'amarah', 'text' => '🏛️ العمارة'],
                                    ['value' => 'nasiriyah', 'text' => '🏛️ الناصرية'],
                                    ['value' => 'diwaniyah', 'text' => '🏛️ الديوانية'],
                                    ['value' => 'kut', 'text' => '🏛️ الكوت']
                                ]"
                                value="{{ old('governorate') }}"
                                required
                                class="{{ $errors->has('governorate') ? 'error' : '' }}"
                            />
                            @error('governorate')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المدينة</label>
                            <input type="text" name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">العنوان</label>
                            <textarea name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Business Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات التجارية</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">فئة العميل</label>
                            <x-searchable-dropdown
                                name="customer_category"
                                placeholder="اختر فئة العميل..."
                                search-placeholder="ابحث في فئات العملاء..."
                                :options="[
                                    ['value' => 'vip', 'text' => '⭐ VIP - عميل مميز'],
                                    ['value' => 'wholesale', 'text' => '📦 تاجر جملة'],
                                    ['value' => 'retail', 'text' => '🛒 تاجر تجزئة'],
                                    ['value' => 'regular', 'text' => '👤 عميل عادي'],
                                    ['value' => 'new', 'text' => '🆕 عميل جديد']
                                ]"
                                value="{{ old('customer_category') }}"
                                class="{{ $errors->has('customer_category') ? 'error' : '' }}"
                            />
                            @error('customer_category')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع المفضلة</label>
                            <x-searchable-dropdown
                                name="preferred_payment_method"
                                placeholder="اختر طريقة الدفع..."
                                search-placeholder="ابحث في طرق الدفع..."
                                :options="[
                                    ['value' => 'cash', 'text' => '💵 نقداً'],
                                    ['value' => 'credit', 'text' => '📅 آجل'],
                                    ['value' => 'bank_transfer', 'text' => '🏦 تحويل بنكي'],
                                    ['value' => 'check', 'text' => '📝 شيك'],
                                    ['value' => 'credit_card', 'text' => '💳 بطاقة ائتمان']
                                ]"
                                value="{{ old('preferred_payment_method') }}"
                                class="{{ $errors->has('preferred_payment_method') ? 'error' : '' }}"
                            />
                            @error('preferred_payment_method')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">حد الائتمان (د.ع)</label>
                            <input type="number" name="credit_limit" min="0" step="1000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">شروط الدفع (أيام)</label>
                            <x-searchable-dropdown
                                name="payment_terms"
                                placeholder="اختر شروط الدفع..."
                                search-placeholder="ابحث في شروط الدفع..."
                                :options="[
                                    ['value' => '0', 'text' => '⚡ فوري'],
                                    ['value' => '7', 'text' => '📅 7 أيام'],
                                    ['value' => '15', 'text' => '📅 15 يوم'],
                                    ['value' => '30', 'text' => '📅 30 يوم'],
                                    ['value' => '45', 'text' => '📅 45 يوم'],
                                    ['value' => '60', 'text' => '📅 60 يوم'],
                                    ['value' => '90', 'text' => '📅 90 يوم']
                                ]"
                                value="{{ old('payment_terms') }}"
                                class="{{ $errors->has('payment_terms') ? 'error' : '' }}"
                            />
                            @error('payment_terms')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">مندوب المبيعات</label>
                            <x-searchable-dropdown
                                name="sales_rep_id"
                                placeholder="اختر مندوب المبيعات..."
                                search-placeholder="ابحث في مندوبي المبيعات..."
                                :options="[
                                    ['value' => '1', 'text' => '👨‍💼 أحمد محمد - منطقة الكرخ'],
                                    ['value' => '2', 'text' => '👩‍💼 فاطمة علي - منطقة الرصافة'],
                                    ['value' => '3', 'text' => '👨‍💼 محمد حسن - منطقة الكاظمية'],
                                    ['value' => '4', 'text' => '👩‍💼 زينب أحمد - منطقة الأعظمية']
                                ]"
                                value="{{ old('sales_rep_id') }}"
                                class="{{ $errors->has('sales_rep_id') ? 'error' : '' }}"
                            />
                            @error('sales_rep_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">حالة العميل</label>
                            <x-searchable-dropdown
                                name="status"
                                placeholder="اختر حالة العميل..."
                                search-placeholder="ابحث في الحالات..."
                                :options="[
                                    ['value' => 'active', 'text' => '✅ نشط'],
                                    ['value' => 'inactive', 'text' => '❌ غير نشط'],
                                    ['value' => 'suspended', 'text' => '⏸️ معلق'],
                                    ['value' => 'blacklisted', 'text' => '🚫 قائمة سوداء']
                                ]"
                                value="{{ old('status', 'active') }}"
                                class="{{ $errors->has('status') ? 'error' : '' }}"
                            />
                            @error('status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Person -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">الشخص المسؤول</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم الشخص المسؤول</label>
                            <input type="text" name="contact_person" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">منصب الشخص المسؤول</label>
                            <x-searchable-dropdown
                                name="contact_position"
                                placeholder="اختر المنصب..."
                                search-placeholder="ابحث في المناصب..."
                                :options="[
                                    ['value' => 'owner', 'text' => '👑 المالك'],
                                    ['value' => 'manager', 'text' => '👨‍💼 المدير'],
                                    ['value' => 'pharmacist', 'text' => '💊 صيدلي'],
                                    ['value' => 'doctor', 'text' => '👨‍⚕️ طبيب'],
                                    ['value' => 'nurse', 'text' => '👩‍⚕️ ممرض/ممرضة'],
                                    ['value' => 'accountant', 'text' => '📊 محاسب'],
                                    ['value' => 'assistant', 'text' => '👤 مساعد'],
                                    ['value' => 'other', 'text' => '❓ أخرى']
                                ]"
                                value="{{ old('contact_position') }}"
                                class="{{ $errors->has('contact_position') ? 'error' : '' }}"
                            />
                            @error('contact_position')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">هاتف الشخص المسؤول</label>
                            <input type="tel" name="contact_phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">بريد الشخص المسؤول</label>
                            <input type="email" name="contact_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات إضافية</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                            <textarea name="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="أي ملاحظات إضافية عن العميل..."></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_tax_exempt" id="is_tax_exempt" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_tax_exempt" class="mr-2 block text-sm text-gray-900">معفى من الضريبة</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="receive_notifications" id="receive_notifications" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                            <label for="receive_notifications" class="mr-2 block text-sm text-gray-900">استقبال الإشعارات</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <a href="{{ route('sales.customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                    إلغاء
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ العميل
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
