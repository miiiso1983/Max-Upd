@extends('layouts.app')

@section('page-title', 'اختبار القوائم المنسدلة القابلة للبحث')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">اختبار القوائم المنسدلة القابلة للبحث</h1>
            <p class="text-gray-600 mt-1">اختبار وظائف البحث في القوائم المنسدلة</p>
        </div>
    </div>

    <!-- Test Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Test 1: Basic Searchable Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اختبار أساسي - المحافظات</label>
                    <select class="searchable-select w-full" name="governorate">
                        <option value="">اختر المحافظة</option>
                        <option value="baghdad">بغداد</option>
                        <option value="basra">البصرة</option>
                        <option value="mosul">الموصل</option>
                        <option value="erbil">أربيل</option>
                        <option value="najaf">النجف</option>
                        <option value="karbala">كربلاء</option>
                        <option value="hillah">الحلة</option>
                        <option value="ramadi">الرمادي</option>
                        <option value="tikrit">تكريت</option>
                        <option value="samarra">سامراء</option>
                        <option value="fallujah">الفلوجة</option>
                        <option value="kirkuk">كركوك</option>
                        <option value="sulaymaniyah">السليمانية</option>
                        <option value="duhok">دهوك</option>
                        <option value="amarah">العمارة</option>
                        <option value="nasiriyah">الناصرية</option>
                        <option value="diwaniyah">الديوانية</option>
                        <option value="kut">الكوت</option>
                    </select>
                </div>

                <!-- Test 2: Customer Types -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع العميل</label>
                    <select class="searchable-select w-full" name="customer_type">
                        <option value="">اختر نوع العميل</option>
                        <option value="individual">فرد</option>
                        <option value="company">شركة</option>
                        <option value="pharmacy">صيدلية</option>
                        <option value="clinic">عيادة</option>
                        <option value="hospital">مستشفى</option>
                        <option value="laboratory">مختبر</option>
                        <option value="medical_center">مركز طبي</option>
                        <option value="dental_clinic">عيادة أسنان</option>
                        <option value="veterinary">عيادة بيطرية</option>
                    </select>
                </div>

                <!-- Test 3: Payment Methods -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع</label>
                    <select class="searchable-select w-full" name="payment_method">
                        <option value="">اختر طريقة الدفع</option>
                        <option value="cash">نقداً</option>
                        <option value="credit">آجل</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="check">شيك</option>
                        <option value="credit_card">بطاقة ائتمان</option>
                        <option value="debit_card">بطاقة خصم</option>
                        <option value="mobile_payment">دفع عبر الهاتف</option>
                        <option value="installments">أقساط</option>
                    </select>
                </div>

                <!-- Test 4: Product Categories -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">فئة المنتج</label>
                    <select class="searchable-select w-full" name="product_category">
                        <option value="">اختر فئة المنتج</option>
                        <option value="antibiotics">أدوية - مضادات حيوية</option>
                        <option value="painkillers">أدوية - مسكنات</option>
                        <option value="vitamins">أدوية - فيتامينات</option>
                        <option value="heart_medicine">أدوية - أدوية القلب</option>
                        <option value="diabetes_medicine">أدوية - أدوية السكري</option>
                        <option value="cosmetics">مستحضرات تجميل</option>
                        <option value="medical_supplies">مستلزمات طبية</option>
                        <option value="medical_devices">أجهزة طبية</option>
                        <option value="supplements">مكملات غذائية</option>
                        <option value="baby_products">منتجات الأطفال</option>
                    </select>
                </div>

                <!-- Test 5: Brands -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">العلامة التجارية</label>
                    <select class="searchable-select w-full" name="brand">
                        <option value="">اختر العلامة التجارية</option>
                        <option value="pfizer">فايزر (Pfizer)</option>
                        <option value="novartis">نوفارتيس (Novartis)</option>
                        <option value="roche">روش (Roche)</option>
                        <option value="johnson">جونسون آند جونسون</option>
                        <option value="merck">مرك (Merck)</option>
                        <option value="abbott">أبوت (Abbott)</option>
                        <option value="bayer">باير (Bayer)</option>
                        <option value="sanofi">سانوفي (Sanofi)</option>
                        <option value="gsk">جلاكسو سميث كلاين</option>
                        <option value="astrazeneca">أسترازينيكا</option>
                    </select>
                </div>

                <!-- Test 6: Multiple Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اختيار متعدد - المهارات</label>
                    <select class="searchable-select w-full" name="skills[]" multiple>
                        <option value="sales">المبيعات</option>
                        <option value="marketing">التسويق</option>
                        <option value="accounting">المحاسبة</option>
                        <option value="pharmacy">الصيدلة</option>
                        <option value="medicine">الطب</option>
                        <option value="nursing">التمريض</option>
                        <option value="management">الإدارة</option>
                        <option value="it">تكنولوجيا المعلومات</option>
                        <option value="customer_service">خدمة العملاء</option>
                        <option value="logistics">اللوجستيات</option>
                    </select>
                </div>

            </div>

            <!-- Test Results -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">تعليمات الاختبار:</h3>
                <ul class="text-blue-800 space-y-1">
                    <li>• اضغط على أي قائمة منسدلة لفتحها</li>
                    <li>• ابدأ بكتابة أي حرف للبحث</li>
                    <li>• جرب البحث بالعربية والإنجليزية</li>
                    <li>• اختبر الاختيار المتعدد في القائمة الأخيرة</li>
                    <li>• لاحظ التصميم والألوان المتناسقة</li>
                </ul>
            </div>

            <!-- Dynamic Add Test -->
            <div class="mt-6">
                <button type="button" id="addNewSelect" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus ml-2"></i>
                    إضافة قائمة جديدة (اختبار ديناميكي)
                </button>
                <div id="dynamicSelects" class="mt-4 space-y-4">
                    <!-- Dynamic selects will be added here -->
                </div>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectCounter = 0;
    
    document.getElementById('addNewSelect').addEventListener('click', function() {
        selectCounter++;
        
        const newSelectHtml = `
            <div class="p-4 border border-gray-200 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2">قائمة ديناميكية ${selectCounter}</label>
                <select class="searchable-select w-full" name="dynamic_${selectCounter}">
                    <option value="">اختر خيار</option>
                    <option value="option1">خيار 1</option>
                    <option value="option2">خيار 2</option>
                    <option value="option3">خيار 3</option>
                    <option value="option4">خيار 4</option>
                    <option value="option5">خيار 5</option>
                </select>
            </div>
        `;
        
        document.getElementById('dynamicSelects').insertAdjacentHTML('beforeend', newSelectHtml);
        
        // Re-initialize searchable selects for new content
        if (window.SearchableSelect) {
            SearchableSelect.reinitialize(document.getElementById('dynamicSelects').lastElementChild);
        }
    });
});
</script>
@endsection
