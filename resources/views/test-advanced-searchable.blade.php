@extends('layouts.app')

@section('page-title', 'القوائم المنسدلة المتقدمة مع مربع البحث')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">القوائم المنسدلة المتقدمة مع مربع البحث</h1>
            <p class="text-gray-600 mt-1">قوائم منسدلة مع مكان خاص للكتابة والتصفية الفورية</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <a href="/test-searchable" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-right ml-2"></i>
                الاختبار الأساسي
            </a>
        </div>
    </div>

    <!-- Demo Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Advanced Select 1: Countries with Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-globe ml-2 text-blue-500"></i>
                        البلدان (مع بحث متقدم)
                    </label>
                    <x-advanced-searchable-select 
                        name="country" 
                        placeholder="اختر البلد..."
                        searchPlaceholder="ابحث عن البلد..."
                        :options="[
                            'iraq' => '🇮🇶 العراق',
                            'saudi' => '🇸🇦 السعودية',
                            'uae' => '🇦🇪 الإمارات',
                            'kuwait' => '🇰🇼 الكويت',
                            'qatar' => '🇶🇦 قطر',
                            'bahrain' => '🇧🇭 البحرين',
                            'oman' => '🇴🇲 عمان',
                            'jordan' => '🇯🇴 الأردن',
                            'lebanon' => '🇱🇧 لبنان',
                            'syria' => '🇸🇾 سوريا',
                            'egypt' => '🇪🇬 مصر',
                            'morocco' => '🇲🇦 المغرب',
                            'tunisia' => '🇹🇳 تونس',
                            'algeria' => '🇩🇿 الجزائر',
                            'libya' => '🇱🇾 ليبيا',
                            'sudan' => '🇸🇩 السودان',
                            'yemen' => '🇾🇪 اليمن'
                        ]"
                    />
                </div>

                <!-- Advanced Select 2: Medical Specialties -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-user-md ml-2 text-green-500"></i>
                        التخصصات الطبية
                    </label>
                    <x-advanced-searchable-select 
                        name="medical_specialty" 
                        placeholder="اختر التخصص..."
                        searchPlaceholder="ابحث في التخصصات..."
                        :options="[
                            'cardiology' => '🫀 أمراض القلب',
                            'neurology' => '🧠 الأعصاب',
                            'orthopedics' => '🦴 العظام',
                            'pediatrics' => '👶 الأطفال',
                            'gynecology' => '👩‍⚕️ النساء والولادة',
                            'dermatology' => '🧴 الجلدية',
                            'ophthalmology' => '👁️ العيون',
                            'dentistry' => '🦷 الأسنان',
                            'psychiatry' => '🧠 الطب النفسي',
                            'surgery' => '🔪 الجراحة العامة',
                            'anesthesia' => '💉 التخدير',
                            'radiology' => '📡 الأشعة',
                            'pathology' => '🔬 علم الأمراض',
                            'emergency' => '🚑 الطوارئ',
                            'family_medicine' => '👨‍👩‍👧‍👦 طب الأسرة'
                        ]"
                    />
                </div>

                <!-- Advanced Select 3: Pharmaceutical Companies -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-industry ml-2 text-purple-500"></i>
                        الشركات الدوائية
                    </label>
                    <x-advanced-searchable-select 
                        name="pharma_company" 
                        placeholder="اختر الشركة..."
                        searchPlaceholder="ابحث في الشركات..."
                        :minimumInputLength="2"
                        :options="[
                            'pfizer' => '💊 شركة فايزر (Pfizer)',
                            'novartis' => '🧬 نوفارتيس (Novartis)',
                            'roche' => '🔬 روش (Roche)',
                            'johnson_johnson' => '🏥 جونسون آند جونسون',
                            'merck' => '⚗️ مرك (Merck)',
                            'abbott' => '🧪 أبوت (Abbott)',
                            'bayer' => '🌿 باير (Bayer)',
                            'sanofi' => '💉 سانوفي (Sanofi)',
                            'gsk' => '🔬 جلاكسو سميث كلاين',
                            'astrazeneca' => '🧬 أسترازينيكا',
                            'bristol_myers' => '💊 بريستول مايرز سكويب',
                            'eli_lilly' => '🧪 إيلي ليلي',
                            'amgen' => '🧬 أمجين',
                            'gilead' => '💉 جيلياد',
                            'biogen' => '🧠 بايوجين'
                        ]"
                    />
                    <p class="text-xs text-gray-500 mt-1">يتطلب كتابة حرفين على الأقل للبحث</p>
                </div>

                <!-- Advanced Select 4: Drug Categories -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-pills ml-2 text-red-500"></i>
                        فئات الأدوية
                    </label>
                    <x-advanced-searchable-select 
                        name="drug_category" 
                        placeholder="اختر فئة الدواء..."
                        searchPlaceholder="ابحث في فئات الأدوية..."
                        :options="[
                            'antibiotics' => '🦠 المضادات الحيوية (Antibiotics)',
                            'analgesics' => '💊 المسكنات (Analgesics)',
                            'antihypertensives' => '❤️ أدوية الضغط (Antihypertensives)',
                            'antidiabetics' => '🍯 أدوية السكري (Antidiabetics)',
                            'antihistamines' => '🤧 مضادات الهيستامين (Antihistamines)',
                            'antidepressants' => '🧠 مضادات الاكتئاب (Antidepressants)',
                            'anticoagulants' => '🩸 مضادات التخثر (Anticoagulants)',
                            'bronchodilators' => '🫁 موسعات الشعب (Bronchodilators)',
                            'corticosteroids' => '💪 الكورتيكوستيرويد (Corticosteroids)',
                            'diuretics' => '💧 مدرات البول (Diuretics)',
                            'vitamins' => '🌟 الفيتامينات (Vitamins)',
                            'vaccines' => '💉 اللقاحات (Vaccines)',
                            'hormones' => '⚖️ الهرمونات (Hormones)',
                            'antacids' => '🔥 مضادات الحموضة (Antacids)',
                            'laxatives' => '🌿 الملينات (Laxatives)'
                        ]"
                    />
                </div>

                <!-- Advanced Select 5: Multiple Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-check-double ml-2 text-indigo-500"></i>
                        المهارات (اختيار متعدد)
                    </label>
                    <x-advanced-searchable-select 
                        name="skills[]" 
                        :multiple="true"
                        placeholder="اختر المهارات..."
                        searchPlaceholder="ابحث في المهارات..."
                        :allowClear="false"
                        :options="[
                            'sales' => '💼 المبيعات والتسويق',
                            'pharmacy' => '💊 الصيدلة الإكلينيكية',
                            'customer_service' => '🤝 خدمة العملاء',
                            'inventory' => '📦 إدارة المخزون',
                            'accounting' => '💰 المحاسبة والمالية',
                            'quality_control' => '✅ مراقبة الجودة',
                            'regulatory' => '📋 الشؤون التنظيمية',
                            'research' => '🔬 البحث والتطوير',
                            'training' => '📚 التدريب والتعليم',
                            'management' => '👔 الإدارة والقيادة',
                            'it_support' => '💻 الدعم التقني',
                            'logistics' => '🚚 اللوجستيات والتوزيع'
                        ]"
                    />
                </div>

                <!-- Advanced Select 6: Cities with Custom Data -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-city ml-2 text-yellow-500"></i>
                        المدن العراقية
                    </label>
                    <x-advanced-searchable-select 
                        name="iraqi_city" 
                        placeholder="اختر المدينة..."
                        searchPlaceholder="ابحث في المدن العراقية..."
                        :options="[
                            'baghdad' => [
                                'text' => '🏛️ بغداد - العاصمة',
                                'data' => ['population' => '7000000', 'region' => 'central']
                            ],
                            'basra' => [
                                'text' => '🛢️ البصرة - العاصمة الاقتصادية',
                                'data' => ['population' => '2500000', 'region' => 'south']
                            ],
                            'mosul' => [
                                'text' => '🏺 الموصل - مدينة التاريخ',
                                'data' => ['population' => '1800000', 'region' => 'north']
                            ],
                            'erbil' => [
                                'text' => '🏔️ أربيل - عاصمة كردستان',
                                'data' => ['population' => '1500000', 'region' => 'kurdistan']
                            ],
                            'najaf' => [
                                'text' => '🕌 النجف الأشرف',
                                'data' => ['population' => '1000000', 'region' => 'central']
                            ],
                            'karbala' => [
                                'text' => '🕌 كربلاء المقدسة',
                                'data' => ['population' => '700000', 'region' => 'central']
                            ],
                            'sulaymaniyah' => [
                                'text' => '🌄 السليمانية',
                                'data' => ['population' => '800000', 'region' => 'kurdistan']
                            ],
                            'kirkuk' => [
                                'text' => '🛢️ كركوك',
                                'data' => ['population' => '600000', 'region' => 'north']
                            ]
                        ]"
                    />
                </div>

            </div>

            <!-- Features Demo -->
            <div class="mt-10 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">
                    <i class="fas fa-star ml-2"></i>
                    مميزات القوائم المتقدمة
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-2">
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-search ml-2 text-blue-500"></i>
                            <span>مربع بحث مدمج ومرئي</span>
                        </div>
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-filter ml-2 text-green-500"></i>
                            <span>تصفية فورية أثناء الكتابة</span>
                        </div>
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-language ml-2 text-purple-500"></i>
                            <span>دعم كامل للغة العربية</span>
                        </div>
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-check-double ml-2 text-indigo-500"></i>
                            <span>اختيار متعدد مع تصميم جميل</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-palette ml-2 text-red-500"></i>
                            <span>تصميم متدرج وألوان جذابة</span>
                        </div>
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-mobile-alt ml-2 text-yellow-500"></i>
                            <span>متجاوب مع جميع الأجهزة</span>
                        </div>
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-bolt ml-2 text-orange-500"></i>
                            <span>أداء سريع ومحسن</span>
                        </div>
                        <div class="flex items-center text-blue-800">
                            <i class="fas fa-cog ml-2 text-gray-500"></i>
                            <span>قابل للتخصيص بالكامل</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-8 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <h3 class="text-lg font-semibold text-yellow-900 mb-2">
                    <i class="fas fa-lightbulb ml-2"></i>
                    تعليمات الاستخدام
                </h3>
                <ul class="text-yellow-800 space-y-1 text-sm">
                    <li>• اضغط على أي قائمة لفتحها ورؤية مربع البحث</li>
                    <li>• ابدأ بالكتابة في مربع البحث لتصفية الخيارات فوراً</li>
                    <li>• جرب البحث بالعربية والإنجليزية</li>
                    <li>• لاحظ التصميم المتدرج والألوان الجميلة</li>
                    <li>• اختبر الاختيار المتعدد في قائمة المهارات</li>
                    <li>• بعض القوائم تتطلب حد أدنى من الأحرف للبحث</li>
                </ul>
            </div>

            <!-- Dynamic Test -->
            <div class="mt-6">
                <button type="button" id="addAdvancedSelect" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-plus ml-2"></i>
                    إضافة قائمة متقدمة جديدة
                </button>
                <div id="dynamicAdvancedSelects" class="mt-4 space-y-4">
                    <!-- Dynamic selects will be added here -->
                </div>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let advancedSelectCounter = 0;
    
    document.getElementById('addAdvancedSelect').addEventListener('click', function() {
        advancedSelectCounter++;
        
        const newSelectHtml = `
            <div class="p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    <i class="fas fa-magic ml-2 text-purple-500"></i>
                    قائمة ديناميكية متقدمة ${advancedSelectCounter}
                </label>
                <select class="advanced-searchable-select w-full" 
                        name="dynamic_advanced_${advancedSelectCounter}"
                        data-placeholder="اختر خيار..."
                        data-search-placeholder="ابحث هنا...">
                    <option value="">اختر خيار...</option>
                    <option value="option1">🎯 خيار رقم 1</option>
                    <option value="option2">🚀 خيار رقم 2</option>
                    <option value="option3">⭐ خيار رقم 3</option>
                    <option value="option4">🎨 خيار رقم 4</option>
                    <option value="option5">🔥 خيار رقم 5</option>
                    <option value="option6">💎 خيار رقم 6</option>
                    <option value="option7">🌟 خيار رقم 7</option>
                    <option value="option8">🎪 خيار رقم 8</option>
                </select>
            </div>
        `;
        
        document.getElementById('dynamicAdvancedSelects').insertAdjacentHTML('beforeend', newSelectHtml);
        
        // Re-initialize advanced searchable selects for new content
        if (window.AdvancedSearchableSelect) {
            AdvancedSearchableSelect.reinitialize(document.getElementById('dynamicAdvancedSelects').lastElementChild);
        }
    });
});
</script>
@endsection
