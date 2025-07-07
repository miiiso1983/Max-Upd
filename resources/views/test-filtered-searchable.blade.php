@extends('layouts.app')

@section('title', 'اختبار القائمة المنسدلة مع البحث والتصفية')

@push('styles')
<link href="{{ asset('css/filtered-searchable-select.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-filter ml-3 text-blue-500"></i>
                القائمة المنسدلة مع البحث والتصفية
            </h1>
            <p class="text-gray-600">مكون متقدم للبحث والتصفية مع دعم AJAX والتجميع</p>
        </div>

        <form class="space-y-8">
            <!-- Example 1: Basic Filtered Select -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-pills ml-2 text-green-500"></i>
                    الأدوية مع التصفية
                </h3>
                
                <x-filtered-searchable-select 
                    name="medicine" 
                    placeholder="اختر الدواء..."
                    searchPlaceholder="ابحث في الأدوية..."
                    :showFilters="true"
                    :filterBy="[
                        'category' => [
                            'label' => 'الفئة',
                            'options' => [
                                'antibiotic' => 'مضاد حيوي',
                                'painkiller' => 'مسكن ألم',
                                'vitamin' => 'فيتامين',
                                'supplement' => 'مكمل غذائي'
                            ]
                        ],
                        'form' => [
                            'label' => 'الشكل',
                            'options' => [
                                'tablet' => 'قرص',
                                'capsule' => 'كبسولة',
                                'syrup' => 'شراب',
                                'injection' => 'حقنة'
                            ]
                        ]
                    ]"
                    :options="[
                        'paracetamol_500' => [
                            'text' => '💊 باراسيتامول 500 مجم',
                            'data' => ['category' => 'painkiller', 'form' => 'tablet']
                        ],
                        'amoxicillin_250' => [
                            'text' => '💊 أموكسيسيلين 250 مجم',
                            'data' => ['category' => 'antibiotic', 'form' => 'capsule']
                        ],
                        'vitamin_d3' => [
                            'text' => '🌟 فيتامين د3',
                            'data' => ['category' => 'vitamin', 'form' => 'tablet']
                        ],
                        'cough_syrup' => [
                            'text' => '🍯 شراب السعال',
                            'data' => ['category' => 'painkiller', 'form' => 'syrup']
                        ],
                        'insulin' => [
                            'text' => '💉 الأنسولين',
                            'data' => ['category' => 'supplement', 'form' => 'injection']
                        ],
                        'omega3' => [
                            'text' => '🐟 أوميجا 3',
                            'data' => ['category' => 'supplement', 'form' => 'capsule']
                        ]
                    ]"
                />
            </div>

            <!-- Example 2: Multi-Select with Grouping -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user-md ml-2 text-blue-500"></i>
                    التخصصات الطبية (متعدد الاختيار)
                </h3>
                
                <x-filtered-searchable-select 
                    name="specialties[]" 
                    placeholder="اختر التخصصات..."
                    searchPlaceholder="ابحث في التخصصات..."
                    :multiple="true"
                    :showFilters="true"
                    :groupBy="'department'"
                    :filterBy="[
                        'department' => [
                            'label' => 'القسم',
                            'options' => [
                                'internal' => 'الطب الباطني',
                                'surgical' => 'الجراحة',
                                'diagnostic' => 'التشخيص',
                                'emergency' => 'الطوارئ'
                            ]
                        ]
                    ]"
                    :options="[
                        [
                            'value' => 'cardiology',
                            'label' => '🫀 أمراض القلب',
                            'department' => 'الطب الباطني',
                            'data' => ['department' => 'internal']
                        ],
                        [
                            'value' => 'neurology',
                            'label' => '🧠 الأعصاب',
                            'department' => 'الطب الباطني',
                            'data' => ['department' => 'internal']
                        ],
                        [
                            'value' => 'orthopedics',
                            'label' => '🦴 العظام',
                            'department' => 'الجراحة',
                            'data' => ['department' => 'surgical']
                        ],
                        [
                            'value' => 'surgery',
                            'label' => '🔪 الجراحة العامة',
                            'department' => 'الجراحة',
                            'data' => ['department' => 'surgical']
                        ],
                        [
                            'value' => 'radiology',
                            'label' => '📡 الأشعة',
                            'department' => 'التشخيص',
                            'data' => ['department' => 'diagnostic']
                        ],
                        [
                            'value' => 'pathology',
                            'label' => '🔬 علم الأمراض',
                            'department' => 'التشخيص',
                            'data' => ['department' => 'diagnostic']
                        ],
                        [
                            'value' => 'emergency',
                            'label' => '🚑 الطوارئ',
                            'department' => 'الطوارئ',
                            'data' => ['department' => 'emergency']
                        ]
                    ]"
                />
            </div>

            <!-- Example 3: AJAX Select with Create Option -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-users ml-2 text-purple-500"></i>
                    العملاء (مع AJAX وإضافة جديد)
                </h3>
                
                <x-filtered-searchable-select 
                    name="customer" 
                    placeholder="ابحث عن عميل..."
                    searchPlaceholder="اكتب اسم العميل..."
                    :allowCreate="true"
                    :minimumInputLength="2"
                    ajaxUrl="/api/customers/search"
                    :showFilters="true"
                    :filterBy="[
                        'type' => [
                            'label' => 'نوع العميل',
                            'options' => [
                                'individual' => 'فرد',
                                'company' => 'شركة',
                                'hospital' => 'مستشفى',
                                'pharmacy' => 'صيدلية'
                            ]
                        ],
                        'status' => [
                            'label' => 'الحالة',
                            'options' => [
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'pending' => 'في الانتظار'
                            ]
                        ]
                    ]"
                />
                <p class="text-sm text-gray-500 mt-2">
                    <i class="fas fa-info-circle ml-1"></i>
                    يمكنك البحث عن العملاء أو إضافة عميل جديد بكتابة الاسم
                </p>
            </div>

            <!-- Example 4: Products with Advanced Filtering -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-box ml-2 text-orange-500"></i>
                    المنتجات مع تصفية متقدمة
                </h3>
                
                <x-filtered-searchable-select 
                    name="product" 
                    placeholder="اختر المنتج..."
                    searchPlaceholder="ابحث في المنتجات..."
                    :showFilters="true"
                    :maxResults="20"
                    :filterBy="[
                        'brand' => [
                            'label' => 'العلامة التجارية',
                            'options' => [
                                'pfizer' => 'فايزر',
                                'novartis' => 'نوفارتيس',
                                'roche' => 'روش',
                                'local' => 'محلي'
                            ]
                        ],
                        'price_range' => [
                            'label' => 'نطاق السعر',
                            'options' => [
                                'low' => 'أقل من 10,000 د.ع',
                                'medium' => '10,000 - 50,000 د.ع',
                                'high' => 'أكثر من 50,000 د.ع'
                            ]
                        ],
                        'availability' => [
                            'label' => 'التوفر',
                            'options' => [
                                'in_stock' => 'متوفر',
                                'low_stock' => 'مخزون منخفض',
                                'out_of_stock' => 'غير متوفر'
                            ]
                        ]
                    ]"
                    :options="[
                        'aspirin_100' => [
                            'text' => '💊 أسبرين 100 مجم - فايزر',
                            'data' => ['brand' => 'pfizer', 'price_range' => 'low', 'availability' => 'in_stock']
                        ],
                        'lipitor_20' => [
                            'text' => '💊 ليبيتور 20 مجم - فايزر',
                            'data' => ['brand' => 'pfizer', 'price_range' => 'high', 'availability' => 'low_stock']
                        ],
                        'voltaren_gel' => [
                            'text' => '🧴 فولتارين جل - نوفارتيس',
                            'data' => ['brand' => 'novartis', 'price_range' => 'medium', 'availability' => 'in_stock']
                        ],
                        'local_paracetamol' => [
                            'text' => '💊 باراسيتامول محلي',
                            'data' => ['brand' => 'local', 'price_range' => 'low', 'availability' => 'in_stock']
                        ]
                    ]"
                />
            </div>

            <!-- Interactive Demo Controls -->
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    <i class="fas fa-gamepad ml-2 text-purple-500"></i>
                    التحكم التفاعلي (تجريبي)
                </h3>

                <div id="demo-controls" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <button class="demo-btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="set-filter">
                        <i class="fas fa-filter mr-1"></i>
                        تطبيق تصفية
                    </button>
                    <button class="demo-btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="clear-filter">
                        <i class="fas fa-times mr-1"></i>
                        مسح التصفية
                    </button>
                    <button class="demo-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="get-value">
                        <i class="fas fa-eye mr-1"></i>
                        عرض القيمة
                    </button>
                    <button class="demo-btn bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="set-value">
                        <i class="fas fa-check mr-1"></i>
                        تحديد قيمة
                    </button>
                    <button class="demo-btn bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="clear-value">
                        <i class="fas fa-eraser mr-1"></i>
                        مسح الاختيار
                    </button>
                </div>

                <p class="text-sm text-gray-600 mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    استخدم الأزرار أعلاه للتفاعل مع القائمة الأولى (الأدوية) برمجياً
                </p>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 shadow-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ البيانات
                </button>
            </div>
        </form>

        <!-- Usage Examples -->
        <div class="mt-12 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-code ml-2 text-indigo-500"></i>
                أمثلة الاستخدام
            </h3>
            
            <div class="space-y-4 text-sm">
                <div>
                    <h4 class="font-medium text-gray-800">قائمة بسيطة مع تصفية:</h4>
                    <pre class="bg-gray-800 text-green-400 p-3 rounded mt-2 overflow-x-auto"><code>&lt;x-filtered-searchable-select 
    name="medicine" 
    placeholder="اختر الدواء..."
    :showFilters="true"
    :filterBy="['category' => ['label' => 'الفئة', 'options' => [...]]]"
    :options="[...]"
/&gt;</code></pre>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-800">قائمة مع AJAX:</h4>
                    <pre class="bg-gray-800 text-green-400 p-3 rounded mt-2 overflow-x-auto"><code>&lt;x-filtered-searchable-select 
    name="customer" 
    ajaxUrl="/api/customers/search"
    :allowCreate="true"
    :minimumInputLength="2"
/&gt;</code></pre>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-800">التحكم بـ JavaScript:</h4>
                    <pre class="bg-gray-800 text-green-400 p-3 rounded mt-2 overflow-x-auto"><code>// تحديث الخيارات
FilteredSearchableSelect.updateOptions('#medicine', {value: 'text'});

// تطبيق تصفية
FilteredSearchableSelect.setFilters('#medicine', {category: 'antibiotic'});

// مسح التصفية
FilteredSearchableSelect.clearFilters('#medicine');</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/filtered-searchable-select.js') }}"></script>
<script>
$(document).ready(function() {
    // Example of dynamic interaction
    console.log('Filtered Searchable Select Test Page Loaded');

    // Custom event handlers for demonstration
    $('.filtered-select-wrapper').on('filtered-select:select', function(e, data) {
        console.log('Item selected:', data);

        // Show notification
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'تم الاختيار!',
                text: 'تم اختيار: ' + data.text,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });

    $('.filtered-select-wrapper').on('filtered-select:clear', function(e) {
        console.log('Selection cleared');
    });

    // Example of programmatic control
    $('#demo-controls').on('click', '.demo-btn', function() {
        const action = $(this).data('action');
        const target = '#medicine';

        switch(action) {
            case 'set-filter':
                FilteredSearchableSelect.setFilters(target, {category: 'antibiotic'});
                break;
            case 'clear-filter':
                FilteredSearchableSelect.clearFilters(target);
                break;
            case 'get-value':
                const value = FilteredSearchableSelect.getValue(target);
                alert('القيمة المختارة: ' + (value || 'لا يوجد'));
                break;
            case 'set-value':
                FilteredSearchableSelect.setValue(target, 'paracetamol_500');
                break;
            case 'clear-value':
                FilteredSearchableSelect.clear(target);
                break;
        }
    });
});
</script>
@endpush
