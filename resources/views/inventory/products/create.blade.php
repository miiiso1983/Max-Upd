@extends('layouts.app')

@section('page-title', 'إضافة منتج جديد')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">إضافة منتج جديد</h1>
            <p class="text-gray-600 mt-1">إضافة منتج جديد إلى المخزون</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="{{ route('inventory.products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('inventory.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="p-6">
                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">المعلومات الأساسية</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">اسم المنتج *</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الاسم العلمي</label>
                            <input type="text" name="scientific_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">رمز المنتج *</label>
                            <input type="text" name="sku" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الباركود</label>
                            <input type="text" name="barcode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الفئة *</label>
                            <x-searchable-dropdown
                                name="category_id"
                                placeholder="اختر الفئة..."
                                search-placeholder="ابحث في الفئات..."
                                :options="[
                                    ['value' => '1', 'text' => '💊 أدوية - مضادات حيوية'],
                                    ['value' => '2', 'text' => '💊 أدوية - مسكنات'],
                                    ['value' => '3', 'text' => '💊 أدوية - فيتامينات'],
                                    ['value' => '4', 'text' => '💊 أدوية - أدوية القلب'],
                                    ['value' => '5', 'text' => '💊 أدوية - أدوية السكري'],
                                    ['value' => '6', 'text' => '💄 مستحضرات تجميل'],
                                    ['value' => '7', 'text' => '🏥 مستلزمات طبية'],
                                    ['value' => '8', 'text' => '🔬 أجهزة طبية'],
                                    ['value' => '9', 'text' => '🌿 مكملات غذائية'],
                                    ['value' => '10', 'text' => '👶 منتجات الأطفال']
                                ]"
                                value="{{ old('category_id') }}"
                                required
                                class="{{ $errors->has('category_id') ? 'error' : '' }}"
                            />
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">العلامة التجارية</label>
                            <x-searchable-dropdown
                                name="brand_id"
                                placeholder="اختر العلامة التجارية..."
                                search-placeholder="ابحث في العلامات التجارية..."
                                :options="[
                                    ['value' => '1', 'text' => '🏆 فايزر (Pfizer)'],
                                    ['value' => '2', 'text' => '🏆 نوفارتيس (Novartis)'],
                                    ['value' => '3', 'text' => '🏆 روش (Roche)'],
                                    ['value' => '4', 'text' => '🏆 جونسون آند جونسون'],
                                    ['value' => '5', 'text' => '🏆 مرك (Merck)'],
                                    ['value' => '6', 'text' => '🏆 أبوت (Abbott)'],
                                    ['value' => '7', 'text' => '🏆 باير (Bayer)'],
                                    ['value' => '8', 'text' => '🏆 سانوفي (Sanofi)'],
                                    ['value' => '9', 'text' => '🏆 جلاكسو سميث كلاين'],
                                    ['value' => '10', 'text' => '🏆 أسترازينيكا']
                                ]"
                                value="{{ old('brand_id') }}"
                                class="{{ $errors->has('brand_id') ? 'error' : '' }}"
                            />
                            @error('brand_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المورد الرئيسي</label>
                            <x-searchable-dropdown
                                name="supplier_id"
                                placeholder="اختر المورد..."
                                search-placeholder="ابحث في الموردين..."
                                :options="[
                                    ['value' => '1', 'text' => '🚚 شركة الأدوية العراقية'],
                                    ['value' => '2', 'text' => '🚚 مؤسسة الكندي للأدوية'],
                                    ['value' => '3', 'text' => '🚚 شركة بغداد للأدوية'],
                                    ['value' => '4', 'text' => '🚚 مختبرات الحكمة'],
                                    ['value' => '5', 'text' => '🚚 شركة النهرين للأدوية'],
                                    ['value' => '6', 'text' => '🚚 مؤسسة الرافدين الطبية'],
                                    ['value' => '7', 'text' => '🚚 شركة دجلة للمستلزمات الطبية'],
                                    ['value' => '8', 'text' => '🚚 مختبرات الفرات']
                                ]"
                                value="{{ old('supplier_id') }}"
                                class="{{ $errors->has('supplier_id') ? 'error' : '' }}"
                            />
                            @error('supplier_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">وحدة القياس *</label>
                            <x-searchable-dropdown
                                name="unit_of_measure"
                                placeholder="اختر وحدة القياس..."
                                search-placeholder="ابحث في وحدات القياس..."
                                :options="[
                                    ['value' => 'piece', 'text' => '📦 قطعة'],
                                    ['value' => 'box', 'text' => '📦 علبة'],
                                    ['value' => 'bottle', 'text' => '🍼 زجاجة'],
                                    ['value' => 'tube', 'text' => '🧪 أنبوب'],
                                    ['value' => 'pack', 'text' => '📦 عبوة'],
                                    ['value' => 'strip', 'text' => '📋 شريط'],
                                    ['value' => 'vial', 'text' => '🧪 قارورة'],
                                    ['value' => 'ampoule', 'text' => '💉 أمبولة'],
                                    ['value' => 'sachet', 'text' => '📦 كيس'],
                                    ['value' => 'tablet', 'text' => '💊 قرص'],
                                    ['value' => 'capsule', 'text' => '💊 كبسولة'],
                                    ['value' => 'ml', 'text' => '🥤 مليلتر'],
                                    ['value' => 'gram', 'text' => '⚖️ جرام'],
                                    ['value' => 'kg', 'text' => '⚖️ كيلوجرام']
                                ]"
                                value="{{ old('unit_of_measure') }}"
                                required
                                class="{{ $errors->has('unit_of_measure') ? 'error' : '' }}"
                            />
                            @error('unit_of_measure')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات التسعير</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">سعر الشراء (د.ع) *</label>
                            <input type="number" name="purchase_price" required min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">سعر البيع (د.ع) *</label>
                            <input type="number" name="selling_price" required min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">هامش الربح (%)</label>
                            <input type="number" name="profit_margin" min="0" max="1000" step="0.01" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>
                    </div>
                </div>

                <!-- Stock Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات المخزون</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الكمية الحالية</label>
                            <input type="number" name="current_stock" min="0" step="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحد الأدنى للمخزون</label>
                            <input type="number" name="minimum_stock" min="0" step="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الحد الأقصى للمخزون</label>
                            <input type="number" name="maximum_stock" min="0" step="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">تفاصيل المنتج</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نوع المنتج</label>
                            <x-searchable-dropdown
                                name="product_type"
                                placeholder="اختر نوع المنتج..."
                                search-placeholder="ابحث في أنواع المنتجات..."
                                :options="[
                                    ['value' => 'medicine', 'text' => '💊 دواء'],
                                    ['value' => 'supplement', 'text' => '🌿 مكمل غذائي'],
                                    ['value' => 'cosmetic', 'text' => '💄 مستحضر تجميل'],
                                    ['value' => 'medical_device', 'text' => '🔬 جهاز طبي'],
                                    ['value' => 'medical_supply', 'text' => '🏥 مستلزم طبي'],
                                    ['value' => 'baby_product', 'text' => '👶 منتج أطفال'],
                                    ['value' => 'personal_care', 'text' => '🧴 عناية شخصية']
                                ]"
                                value="{{ old('product_type') }}"
                                class="{{ $errors->has('product_type') ? 'error' : '' }}"
                            />
                            @error('product_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الاستخدام</label>
                            <x-searchable-dropdown
                                name="usage_method"
                                placeholder="اختر طريقة الاستخدام..."
                                search-placeholder="ابحث في طرق الاستخدام..."
                                :options="[
                                    ['value' => 'oral', 'text' => '👄 عن طريق الفم'],
                                    ['value' => 'topical', 'text' => '🖐️ موضعي'],
                                    ['value' => 'injection', 'text' => '💉 حقن'],
                                    ['value' => 'inhalation', 'text' => '🫁 استنشاق'],
                                    ['value' => 'eye_drops', 'text' => '👁️ قطرة عين'],
                                    ['value' => 'ear_drops', 'text' => '👂 قطرة أذن'],
                                    ['value' => 'nasal', 'text' => '👃 أنفي'],
                                    ['value' => 'rectal', 'text' => '🔄 شرجي'],
                                    ['value' => 'external', 'text' => '🖐️ خارجي']
                                ]"
                                value="{{ old('usage_method') }}"
                                class="{{ $errors->has('usage_method') ? 'error' : '' }}"
                            />
                            @error('usage_method')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">حالة المنتج</label>
                            <x-searchable-dropdown
                                name="status"
                                placeholder="اختر حالة المنتج..."
                                search-placeholder="ابحث في الحالات..."
                                :options="[
                                    ['value' => 'active', 'text' => '✅ نشط'],
                                    ['value' => 'inactive', 'text' => '❌ غير نشط'],
                                    ['value' => 'discontinued', 'text' => '🚫 متوقف'],
                                    ['value' => 'pending', 'text' => '⏳ في الانتظار']
                                ]"
                                value="{{ old('status', 'active') }}"
                                class="{{ $errors->has('status') ? 'error' : '' }}"
                            />
                            @error('status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">يتطلب وصفة طبية</label>
                            <x-searchable-dropdown
                                name="requires_prescription"
                                placeholder="اختر نوع الوصفة..."
                                search-placeholder="ابحث في أنواع الوصفات..."
                                :options="[
                                    ['value' => '0', 'text' => '🆓 لا يتطلب وصفة'],
                                    ['value' => '1', 'text' => '📋 يتطلب وصفة طبية']
                                ]"
                                value="{{ old('requires_prescription', '0') }}"
                                class="{{ $errors->has('requires_prescription') ? 'error' : '' }}"
                            />
                            @error('requires_prescription')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الكلمات المفتاحية</label>
                            <input type="text" name="keywords"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="أدخل الكلمات المفتاحية مفصولة بفواصل..."
                                   value="{{ old('keywords') }}">
                            <p class="text-xs text-gray-500 mt-1">مثال: مضاد حيوي، مسكن، فيتامين</p>
                            @error('keywords')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">وصف المنتج</label>
                            <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="وصف تفصيلي للمنتج..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Expiry and Storage -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">انتهاء الصلاحية والتخزين</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ انتهاء الصلاحية</label>
                            <input type="date" name="expiry_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">شروط التخزين</label>
                            <x-searchable-dropdown
                                name="storage_conditions"
                                placeholder="اختر شروط التخزين..."
                                search-placeholder="ابحث في شروط التخزين..."
                                :options="[
                                    ['value' => 'room_temperature', 'text' => '🌡️ درجة حرارة الغرفة (15-25°م)'],
                                    ['value' => 'cool_place', 'text' => '❄️ مكان بارد (2-8°م)'],
                                    ['value' => 'freezer', 'text' => '🧊 مجمد (-18°م)'],
                                    ['value' => 'dry_place', 'text' => '🏜️ مكان جاف'],
                                    ['value' => 'dark_place', 'text' => '🌑 مكان مظلم'],
                                    ['value' => 'refrigerated', 'text' => '🧊 مبرد'],
                                    ['value' => 'controlled_temperature', 'text' => '🌡️ درجة حرارة محكومة']
                                ]"
                                value="{{ old('storage_conditions') }}"
                                class="{{ $errors->has('storage_conditions') ? 'error' : '' }}"
                            />
                            @error('storage_conditions')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Image -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">صورة المنتج</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رفع صورة</label>
                        <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-sm text-gray-500 mt-1">يُفضل صورة بحجم 500x500 بكسل، بصيغة JPG أو PNG</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <a href="{{ route('inventory.products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                    إلغاء
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ المنتج
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Calculate profit margin automatically
document.addEventListener('DOMContentLoaded', function() {
    const purchasePrice = document.querySelector('input[name="purchase_price"]');
    const sellingPrice = document.querySelector('input[name="selling_price"]');
    const profitMargin = document.querySelector('input[name="profit_margin"]');
    
    function calculateProfitMargin() {
        const purchase = parseFloat(purchasePrice.value) || 0;
        const selling = parseFloat(sellingPrice.value) || 0;
        
        if (purchase > 0) {
            const margin = ((selling - purchase) / purchase) * 100;
            profitMargin.value = margin.toFixed(2);
        } else {
            profitMargin.value = '';
        }
    }
    
    purchasePrice.addEventListener('input', calculateProfitMargin);
    sellingPrice.addEventListener('input', calculateProfitMargin);
});
</script>
@endsection
