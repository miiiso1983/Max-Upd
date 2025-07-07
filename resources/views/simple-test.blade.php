<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار بسيط - القائمة المنسدلة مع البحث والتصفية</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    <i class="fas fa-filter ml-3 text-blue-500"></i>
                    اختبار القائمة المنسدلة مع البحث والتصفية
                </h1>

                <!-- Test 1: Basic Select -->
                <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">اختبار أساسي</h3>
                    
                    <div class="filtered-select-wrapper">
                        <select name="basic_test" class="filtered-searchable-select w-full">
                            <option value="">اختر من القائمة...</option>
                            <option value="option1">الخيار الأول</option>
                            <option value="option2">الخيار الثاني</option>
                            <option value="option3">الخيار الثالث</option>
                        </select>
                    </div>
                </div>

                <!-- Test 2: With Filters -->
                <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">اختبار مع التصفية</h3>
                    
                    <div class="filtered-select-wrapper">
                        <!-- Filter Controls -->
                        <div class="filter-controls mb-3 p-3 bg-gray-50 rounded-lg border">
                            <div class="flex flex-wrap gap-2 items-center">
                                <span class="text-sm font-medium text-gray-700">تصفية حسب:</span>
                                <div class="filter-group">
                                    <label class="text-xs text-gray-600">الفئة</label>
                                    <select class="filter-select text-sm border-gray-300 rounded px-2 py-1" data-filter="category">
                                        <option value="">الكل</option>
                                        <option value="medicine">دواء</option>
                                        <option value="supplement">مكمل</option>
                                    </select>
                                </div>
                                <button type="button" class="clear-filters text-xs text-blue-600 hover:text-blue-800 mr-2">
                                    مسح التصفية
                                </button>
                            </div>
                        </div>

                        <select name="filtered_test" class="filtered-searchable-select w-full" 
                                data-placeholder="اختر الدواء..."
                                data-search-placeholder="ابحث في الأدوية...">
                            <option value="">اختر الدواء...</option>
                            <option value="paracetamol" data-category="medicine">باراسيتامول</option>
                            <option value="vitamin_c" data-category="supplement">فيتامين سي</option>
                            <option value="aspirin" data-category="medicine">أسبرين</option>
                            <option value="omega3" data-category="supplement">أوميجا 3</option>
                        </select>
                        
                        <div class="results-counter text-xs text-gray-500 mt-1 hidden">
                            <span class="results-text"></span>
                        </div>
                    </div>
                </div>

                <!-- Test 3: Multiple Select -->
                <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">اختبار متعدد الاختيار</h3>
                    
                    <div class="filtered-select-wrapper">
                        <select name="multiple_test[]" class="filtered-searchable-select w-full" multiple>
                            <option value="spec1">تخصص أول</option>
                            <option value="spec2">تخصص ثاني</option>
                            <option value="spec3">تخصص ثالث</option>
                            <option value="spec4">تخصص رابع</option>
                        </select>
                    </div>
                </div>

                <!-- Test Controls -->
                <div class="text-center space-x-4 space-x-reverse">
                    <button onclick="testGetValue()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        عرض القيم
                    </button>
                    <button onclick="testSetValue()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        تحديد قيمة
                    </button>
                    <button onclick="testClear()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        مسح الكل
                    </button>
                </div>

                <!-- Results Display -->
                <div id="results" class="mt-8 p-4 bg-gray-100 rounded-lg hidden">
                    <h4 class="font-semibold mb-2">النتائج:</h4>
                    <pre id="results-content" class="text-sm"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
    // Simple initialization without external files
    $(document).ready(function() {
        console.log('Simple test page loaded');
        
        // Initialize all selects
        $('.filtered-searchable-select').each(function() {
            const $select = $(this);
            const isMultiple = $select.prop('multiple');
            const placeholder = $select.data('placeholder') || 'اختر من القائمة...';
            
            $select.select2({
                placeholder: placeholder,
                allowClear: !isMultiple,
                dir: 'rtl',
                language: {
                    noResults: function() { return "لا توجد نتائج"; },
                    searching: function() { return "جاري البحث..."; }
                },
                width: '100%'
            });
        });
        
        // Filter functionality
        $('.filter-select').on('change', function() {
            const $wrapper = $(this).closest('.filtered-select-wrapper');
            const $select = $wrapper.find('.filtered-searchable-select');
            const filterKey = $(this).data('filter');
            const filterValue = $(this).val();
            
            // Get all options
            const $options = $select.find('option');
            
            $options.each(function() {
                const $option = $(this);
                if ($option.val() === '') return; // Skip placeholder
                
                if (!filterValue || $option.data(filterKey) === filterValue) {
                    $option.show();
                } else {
                    $option.hide();
                }
            });
            
            // Refresh Select2
            $select.trigger('change');
            
            // Update counter
            const visibleCount = $options.filter(':visible').length - 1; // -1 for placeholder
            $wrapper.find('.results-text').text(visibleCount + ' نتيجة متاحة');
            $wrapper.find('.results-counter').removeClass('hidden');
        });
        
        // Clear filters
        $('.clear-filters').on('click', function() {
            const $wrapper = $(this).closest('.filtered-select-wrapper');
            $wrapper.find('.filter-select').val('').trigger('change');
        });
    });
    
    // Test functions
    function testGetValue() {
        const values = {};
        $('.filtered-searchable-select').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            values[name] = value;
        });
        
        showResults(values);
    }
    
    function testSetValue() {
        $('[name="basic_test"]').val('option2').trigger('change');
        $('[name="filtered_test"]').val('paracetamol').trigger('change');
        $('[name="multiple_test[]"]').val(['spec1', 'spec3']).trigger('change');
        
        showResults({message: 'تم تحديد القيم بنجاح'});
    }
    
    function testClear() {
        $('.filtered-searchable-select').val(null).trigger('change');
        showResults({message: 'تم مسح جميع القيم'});
    }
    
    function showResults(data) {
        $('#results-content').text(JSON.stringify(data, null, 2));
        $('#results').removeClass('hidden');
        
        setTimeout(() => {
            $('#results').addClass('hidden');
        }, 5000);
    }
    </script>
</body>
</html>
