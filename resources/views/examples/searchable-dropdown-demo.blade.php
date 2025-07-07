@extends('layouts.app')

@section('title', 'مثال على القوائم المنسدلة القابلة للبحث')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">أمثلة على القوائم المنسدلة القابلة للبحث</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- مثال 1: قائمة الدول -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">اختيار الدولة</h3>
            <label class="block text-sm font-medium text-gray-700 mb-2">الدولة</label>
            <x-searchable-dropdown 
                name="country"
                placeholder="اختر الدولة"
                search-placeholder="ابحث عن دولة..."
                :options="[
                    ['value' => 'iq', 'text' => 'العراق'],
                    ['value' => 'sa', 'text' => 'السعودية'],
                    ['value' => 'ae', 'text' => 'الإمارات'],
                    ['value' => 'kw', 'text' => 'الكويت'],
                    ['value' => 'qa', 'text' => 'قطر'],
                    ['value' => 'bh', 'text' => 'البحرين'],
                    ['value' => 'om', 'text' => 'عمان'],
                    ['value' => 'jo', 'text' => 'الأردن'],
                    ['value' => 'lb', 'text' => 'لبنان'],
                    ['value' => 'sy', 'text' => 'سوريا'],
                    ['value' => 'eg', 'text' => 'مصر'],
                    ['value' => 'ma', 'text' => 'المغرب'],
                    ['value' => 'tn', 'text' => 'تونس'],
                    ['value' => 'dz', 'text' => 'الجزائر']
                ]"
                value="iq"
            />
        </div>

        <!-- مثال 2: قائمة الموظفين -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">اختيار الموظف</h3>
            <label class="block text-sm font-medium text-gray-700 mb-2">الموظف</label>
            <x-searchable-dropdown 
                name="employee"
                placeholder="اختر الموظف"
                search-placeholder="ابحث عن موظف..."
                :options="[
                    ['value' => '', 'text' => 'جميع الموظفين'],
                    ['value' => '1', 'text' => 'أحمد محمد علي'],
                    ['value' => '2', 'text' => 'فاطمة أحمد حسن'],
                    ['value' => '3', 'text' => 'محمد عبدالله سالم'],
                    ['value' => '4', 'text' => 'نور الهدى محمود'],
                    ['value' => '5', 'text' => 'عبدالرحمن خالد'],
                    ['value' => '6', 'text' => 'زينب عبدالعزيز'],
                    ['value' => '7', 'text' => 'يوسف إبراهيم'],
                    ['value' => '8', 'text' => 'مريم سعد الدين'],
                    ['value' => '9', 'text' => 'عمر حسام الدين'],
                    ['value' => '10', 'text' => 'ليلى عبدالرحمن']
                ]"
                required
            />
        </div>

        <!-- مثال 3: قائمة الحالات -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">حالة الطلب</h3>
            <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
            <x-searchable-dropdown 
                name="status"
                placeholder="اختر الحالة"
                search-placeholder="ابحث عن حالة..."
                :options="[
                    ['value' => 'pending', 'text' => 'قيد الانتظار'],
                    ['value' => 'processing', 'text' => 'قيد المعالجة'],
                    ['value' => 'approved', 'text' => 'موافق عليه'],
                    ['value' => 'rejected', 'text' => 'مرفوض'],
                    ['value' => 'completed', 'text' => 'مكتمل'],
                    ['value' => 'cancelled', 'text' => 'ملغي']
                ]"
                value="pending"
                class="size-sm"
            />
        </div>

        <!-- مثال 4: قائمة معطلة -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">قائمة معطلة</h3>
            <label class="block text-sm font-medium text-gray-700 mb-2">خيار معطل</label>
            <x-searchable-dropdown 
                name="disabled_example"
                placeholder="هذه القائمة معطلة"
                :options="[
                    ['value' => '1', 'text' => 'خيار 1'],
                    ['value' => '2', 'text' => 'خيار 2']
                ]"
                disabled
            />
        </div>

        <!-- مثال 5: قائمة كبيرة -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">قائمة كبيرة الحجم</h3>
            <label class="block text-sm font-medium text-gray-700 mb-2">اختيار كبير</label>
            <x-searchable-dropdown 
                name="large_example"
                placeholder="اختر خيار"
                search-placeholder="ابحث..."
                :options="[
                    ['value' => '1', 'text' => 'خيار رقم واحد'],
                    ['value' => '2', 'text' => 'خيار رقم اثنين'],
                    ['value' => '3', 'text' => 'خيار رقم ثلاثة']
                ]"
                class="size-lg"
            />
        </div>

        <!-- مثال 6: قائمة مع خطأ -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">قائمة مع خطأ</h3>
            <label class="block text-sm font-medium text-gray-700 mb-2">حقل مطلوب</label>
            <x-searchable-dropdown 
                name="error_example"
                placeholder="هذا الحقل مطلوب"
                :options="[
                    ['value' => '1', 'text' => 'خيار 1'],
                    ['value' => '2', 'text' => 'خيار 2']
                ]"
                required
                class="error"
            />
            <p class="text-red-500 text-sm mt-1">هذا الحقل مطلوب</p>
        </div>
    </div>

    <!-- مثال على الاستخدام مع JavaScript -->
    <div class="bg-white p-6 rounded-lg shadow mt-6">
        <h3 class="text-lg font-semibold mb-4">التحكم بـ JavaScript</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">اختيار المدينة</label>
                <x-searchable-dropdown 
                    name="city_js"
                    placeholder="اختر المدينة"
                    search-placeholder="ابحث عن مدينة..."
                    :options="[
                        ['value' => 'baghdad', 'text' => 'بغداد'],
                        ['value' => 'basra', 'text' => 'البصرة'],
                        ['value' => 'mosul', 'text' => 'الموصل'],
                        ['value' => 'erbil', 'text' => 'أربيل'],
                        ['value' => 'najaf', 'text' => 'النجف'],
                        ['value' => 'karbala', 'text' => 'كربلاء']
                    ]"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">النتيجة</label>
                <input type="text" id="result" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
            </div>
        </div>
        
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="getSelectedValue()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                احصل على القيمة المختارة
            </button>
            <button onclick="setSelectedValue()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                اختر بغداد
            </button>
            <button onclick="clearSelection()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                مسح الاختيار
            </button>
        </div>
    </div>

    <!-- شرح الاستخدام -->
    <div class="bg-gray-50 p-6 rounded-lg mt-6">
        <h3 class="text-lg font-semibold mb-4">كيفية الاستخدام</h3>
        <div class="prose max-w-none">
            <h4 class="font-semibold">الخصائص المتاحة:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li><code>name</code> - اسم الحقل (مطلوب)</li>
                <li><code>value</code> - القيمة المختارة مسبقاً</li>
                <li><code>placeholder</code> - النص الظاهر عند عدم الاختيار</li>
                <li><code>search-placeholder</code> - نص البحث</li>
                <li><code>options</code> - مصفوفة الخيارات</li>
                <li><code>required</code> - حقل مطلوب</li>
                <li><code>disabled</code> - تعطيل القائمة</li>
                <li><code>class</code> - فئات CSS إضافية</li>
            </ul>
            
            <h4 class="font-semibold mt-4">الأحجام المتاحة:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li><code>size-sm</code> - حجم صغير</li>
                <li><code>size-lg</code> - حجم كبير</li>
                <li>الحجم الافتراضي - متوسط</li>
            </ul>
            
            <h4 class="font-semibold mt-4">الحالات المتاحة:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li><code>error</code> - حالة خطأ</li>
                <li><code>success</code> - حالة نجاح</li>
            </ul>
        </div>
    </div>
</div>

<script>
// مثال على التحكم بـ JavaScript
function getSelectedValue() {
    const value = getDropdownValue('city_js');
    const dropdown = document.querySelector('[data-name="city_js"]');
    const text = dropdown.querySelector('.selected-text').textContent;
    document.getElementById('result').value = `القيمة: ${value}, النص: ${text}`;
}

function setSelectedValue() {
    setDropdownValue('city_js', 'baghdad');
}

function clearSelection() {
    setDropdownValue('city_js', '');
}

// مثال على الاستماع للتغييرات
document.addEventListener('DOMContentLoaded', function() {
    const cityDropdown = document.querySelector('[data-name="city_js"]');
    if (cityDropdown) {
        cityDropdown.addEventListener('change', function(event) {
            console.log('تم تغيير المدينة:', event.detail);
        });
    }
});
</script>
@endsection
