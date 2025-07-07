<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مقارنة مكتبات PDF - نظام MaxCon ERP</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    <i class="fas fa-balance-scale ml-3 text-blue-500"></i>
                    مقارنة مكتبات PDF للغة العربية
                </h1>

                <!-- Comparison Table -->
                <div class="overflow-x-auto mb-8">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 p-4 text-right">المعيار</th>
                                <th class="border border-gray-300 p-4 text-center bg-red-50">
                                    <i class="fas fa-times-circle text-red-500"></i>
                                    DomPDF
                                </th>
                                <th class="border border-gray-300 p-4 text-center bg-green-50">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                    mPDF
                                </th>
                                <th class="border border-gray-300 p-4 text-center bg-blue-50">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                    TCPDF
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 p-4 font-semibold">دعم الحروف العربية المتصلة</td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">ضعيف ❌</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">ممتاز ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">جيد ✅</span>
                                </td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 p-4 font-semibold">دعم RTL (اليمين إلى اليسار)</td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">محدود ⚠️</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">ممتاز ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">ممتاز ✅</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-4 font-semibold">سهولة الاستخدام</td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">سهل ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">سهل ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">متوسط ⚠️</span>
                                </td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 p-4 font-semibold">دعم CSS</td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">محدود ⚠️</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">جيد ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">متقدم ✅</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-4 font-semibold">الأداء والسرعة</td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">سريع ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">متوسط ⚡</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">بطيء ⚠️</span>
                                </td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 p-4 font-semibold">حجم المكتبة</td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">صغير ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">متوسط ⚡</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">كبير ❌</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-4 font-semibold">التوافق مع Laravel</td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">ممتاز ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">جيد ✅</span>
                                </td>
                                <td class="border border-gray-300 p-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">جيد ✅</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Recommendations -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- DomPDF -->
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-red-800 mb-4">
                            <i class="fas fa-times-circle ml-2"></i>
                            DomPDF
                        </h3>
                        <div class="space-y-3">
                            <div class="text-sm">
                                <strong class="text-red-700">المشاكل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1 text-red-600">
                                    <li>الحروف العربية منفصلة</li>
                                    <li>مشاكل في RTL</li>
                                    <li>دعم CSS محدود</li>
                                </ul>
                            </div>
                            <div class="text-sm">
                                <strong class="text-red-700">الاستخدام:</strong>
                                <p class="text-red-600 mt-1">غير مناسب للنصوص العربية</p>
                            </div>
                        </div>
                    </div>

                    <!-- mPDF -->
                    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-green-800 mb-4">
                            <i class="fas fa-check-circle ml-2"></i>
                            mPDF - الأفضل
                        </h3>
                        <div class="space-y-3">
                            <div class="text-sm">
                                <strong class="text-green-700">المميزات:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1 text-green-600">
                                    <li>دعم ممتاز للعربية</li>
                                    <li>حروف متصلة صحيحة</li>
                                    <li>RTL مثالي</li>
                                    <li>سهل الاستخدام</li>
                                </ul>
                            </div>
                            <div class="text-sm">
                                <strong class="text-green-700">الاستخدام:</strong>
                                <p class="text-green-600 mt-1">الأفضل للمشاريع العربية</p>
                            </div>
                        </div>
                    </div>

                    <!-- TCPDF -->
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-blue-800 mb-4">
                            <i class="fas fa-info-circle ml-2"></i>
                            TCPDF
                        </h3>
                        <div class="space-y-3">
                            <div class="text-sm">
                                <strong class="text-blue-700">المميزات:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1 text-blue-600">
                                    <li>ميزات متقدمة</li>
                                    <li>دعم جيد للعربية</li>
                                    <li>تحكم دقيق</li>
                                </ul>
                            </div>
                            <div class="text-sm">
                                <strong class="text-blue-700">العيوب:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1 text-blue-600">
                                    <li>معقد الاستخدام</li>
                                    <li>حجم كبير</li>
                                    <li>أداء أبطأ</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Links -->
                <div class="bg-gray-100 rounded-lg p-6 mb-8">
                    <h3 class="text-xl font-bold text-center mb-6 text-gray-800">
                        <i class="fas fa-vial ml-2"></i>
                        اختبر المكتبات بنفسك
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <h4 class="font-semibold mb-2 text-red-700">DomPDF</h4>
                            <a href="/arabic-pdf" target="_blank" 
                               class="block bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded transition duration-200">
                                اختبار DomPDF
                            </a>
                        </div>
                        <div class="text-center">
                            <h4 class="font-semibold mb-2 text-green-700">mPDF</h4>
                            <a href="/mpdf-test" target="_blank" 
                               class="block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded transition duration-200">
                                اختبار mPDF
                            </a>
                        </div>
                        <div class="text-center">
                            <h4 class="font-semibold mb-2 text-blue-700">TCPDF</h4>
                            <a href="/tcpdf-test" target="_blank" 
                               class="block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition duration-200">
                                اختبار TCPDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Final Recommendation -->
                <div class="bg-gradient-to-r from-green-100 to-blue-100 rounded-lg p-8 text-center">
                    <h2 class="text-2xl font-bold text-green-800 mb-4">
                        <i class="fas fa-trophy ml-2 text-yellow-500"></i>
                        التوصية النهائية
                    </h2>
                    <p class="text-lg text-green-700 mb-4">
                        <strong>استخدم mPDF</strong> لجميع ملفات PDF في نظام MaxCon ERP
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="bg-white p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800 mb-2">لماذا mPDF؟</h4>
                            <ul class="text-right text-green-700">
                                <li>✅ حروف عربية متصلة مثالية</li>
                                <li>✅ دعم RTL كامل</li>
                                <li>✅ سهولة في الاستخدام</li>
                                <li>✅ أداء جيد</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-2">كيفية التطبيق:</h4>
                            <ul class="text-right text-blue-700">
                                <li>📝 استخدم MPdfHelper</li>
                                <li>🎨 طبق CSS العربي</li>
                                <li>📄 اختبر الفواتير</li>
                                <li>🚀 انشر في الإنتاج</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="text-center mt-8">
                    <a href="/test-pdf-page" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة لاختبار PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
