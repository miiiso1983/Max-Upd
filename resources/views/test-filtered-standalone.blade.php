<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار القائمة المنسدلة مع البحث والتصفية</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/filtered-searchable-select.css') }}" rel="stylesheet">
    
    <!-- SweetAlert2 for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto main-container p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold gradient-text mb-4">
                        <i class="fas fa-filter ml-3"></i>
                        القائمة المنسدلة مع البحث والتصفية
                    </h1>
                    <p class="text-gray-600 text-lg">مكون متقدم للبحث والتصفية مع دعم AJAX والتجميع</p>
                    <div class="mt-4 inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        جاهز للاختبار - لا يتطلب تسجيل دخول
                    </div>
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

                    <!-- Example 2: Multi-Select -->
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
                                'surgery' => '🔪 الجراحة العامة'
                            ]"
                        />
                    </div>

                    <!-- Example 3: AJAX Select -->
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

                    <!-- Interactive Demo Controls -->
                    <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">
                            <i class="fas fa-gamepad ml-2 text-purple-500"></i>
                            التحكم التفاعلي (تجريبي)
                        </h3>
                        
                        <div id="demo-controls" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                            <button type="button" class="demo-btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="set-filter">
                                <i class="fas fa-filter mr-1"></i>
                                تطبيق تصفية
                            </button>
                            <button type="button" class="demo-btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="clear-filter">
                                <i class="fas fa-times mr-1"></i>
                                مسح التصفية
                            </button>
                            <button type="button" class="demo-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="get-value">
                                <i class="fas fa-eye mr-1"></i>
                                عرض القيمة
                            </button>
                            <button type="button" class="demo-btn bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="set-value">
                                <i class="fas fa-check mr-1"></i>
                                تحديد قيمة
                            </button>
                            <button type="button" class="demo-btn bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200" data-action="clear-value">
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
                        <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 shadow-lg transform hover:scale-105">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/filtered-searchable-select.js') }}"></script>
    
    <script>
    $(document).ready(function() {
        console.log('Filtered Searchable Select Test Page Loaded');
        
        // Custom event handlers for demonstration
        $('.filtered-select-wrapper').on('filtered-select:select', function(e, data) {
            console.log('Item selected:', data);
            
            Swal.fire({
                title: 'تم الاختيار!',
                text: 'تم اختيار: ' + data.text,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });
        
        $('.filtered-select-wrapper').on('filtered-select:clear', function(e) {
            console.log('Selection cleared');
            
            Swal.fire({
                title: 'تم المسح',
                text: 'تم مسح الاختيار',
                icon: 'info',
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });
        
        // Example of programmatic control
        $('#demo-controls').on('click', '.demo-btn', function() {
            const action = $(this).data('action');
            const target = '[name="medicine"]';
            
            switch(action) {
                case 'set-filter':
                    FilteredSearchableSelect.setFilters(target, {category: 'antibiotic'});
                    Swal.fire({
                        title: 'تم تطبيق التصفية',
                        text: 'تم تصفية الأدوية حسب: مضاد حيوي',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    break;
                case 'clear-filter':
                    FilteredSearchableSelect.clearFilters(target);
                    Swal.fire({
                        title: 'تم مسح التصفية',
                        text: 'تم عرض جميع الأدوية',
                        icon: 'info',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    break;
                case 'get-value':
                    const value = FilteredSearchableSelect.getValue(target);
                    Swal.fire({
                        title: 'القيمة المختارة',
                        text: value || 'لا يوجد اختيار',
                        icon: 'info'
                    });
                    break;
                case 'set-value':
                    FilteredSearchableSelect.setValue(target, 'paracetamol_500');
                    Swal.fire({
                        title: 'تم تحديد القيمة',
                        text: 'تم اختيار: باراسيتامول 500 مجم',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    break;
                case 'clear-value':
                    FilteredSearchableSelect.clear(target);
                    break;
            }
        });
        
        // Form submission handler
        $('form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            Swal.fire({
                title: 'بيانات النموذج',
                html: '<pre style="text-align: left; direction: ltr;">' + JSON.stringify(data, null, 2) + '</pre>',
                icon: 'info',
                confirmButtonText: 'موافق'
            });
        });
    });
    </script>
</body>
</html>
