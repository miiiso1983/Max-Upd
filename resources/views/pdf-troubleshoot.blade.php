<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استكشاف أخطاء PDF - نظام MaxCon ERP</title>
    
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
            <div class="max-w-6xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    <i class="fas fa-tools ml-3 text-blue-500"></i>
                    استكشاف أخطاء PDF
                </h1>

                <!-- Quick Tests -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <button onclick="testPdfStatus()" 
                            class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg transition duration-200">
                        <i class="fas fa-stethoscope text-2xl mb-2"></i>
                        <div>فحص النظام</div>
                    </button>
                    
                    <button onclick="testStreamPdf()" 
                            class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg transition duration-200">
                        <i class="fas fa-eye text-2xl mb-2"></i>
                        <div>عرض PDF</div>
                    </button>
                    
                    <button onclick="testDownloadPdf()" 
                            class="bg-orange-500 hover:bg-orange-600 text-white p-4 rounded-lg transition duration-200">
                        <i class="fas fa-download text-2xl mb-2"></i>
                        <div>تحميل PDF</div>
                    </button>
                    
                    <button onclick="testArabicPdf()" 
                            class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg transition duration-200">
                        <i class="fas fa-language text-2xl mb-2"></i>
                        <div>PDF عربي</div>
                    </button>
                </div>

                <!-- Results Area -->
                <div id="results" class="mb-8 hidden">
                    <h3 class="text-lg font-semibold mb-4">نتائج الاختبار:</h3>
                    <div id="results-content" class="bg-gray-100 p-4 rounded-lg">
                        <!-- Results will be displayed here -->
                    </div>
                </div>

                <!-- Common Issues -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Browser Issues -->
                    <div class="bg-red-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-red-800 mb-4">
                            <i class="fas fa-browser ml-2"></i>
                            مشاكل المتصفح
                        </h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="bg-white p-3 rounded border-r-4 border-red-500">
                                <strong>المشكلة:</strong> "Cannot connect to server"
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>تأكد من تشغيل الخادم</li>
                                    <li>جرب متصفح آخر</li>
                                    <li>امسح cache المتصفح</li>
                                    <li>تعطيل ad blockers</li>
                                </ul>
                            </div>
                            
                            <div class="bg-white p-3 rounded border-r-4 border-red-500">
                                <strong>المشكلة:</strong> PDF لا يفتح
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>تحديث Adobe Reader</li>
                                    <li>تفعيل JavaScript</li>
                                    <li>السماح بـ pop-ups</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Server Issues -->
                    <div class="bg-yellow-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-4">
                            <i class="fas fa-server ml-2"></i>
                            مشاكل الخادم
                        </h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="bg-white p-3 rounded border-r-4 border-yellow-500">
                                <strong>المشكلة:</strong> Memory limit exceeded
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>زيادة memory_limit في PHP</li>
                                    <li>تقليل حجم PDF</li>
                                    <li>تحسين الصور</li>
                                </ul>
                            </div>
                            
                            <div class="bg-white p-3 rounded border-r-4 border-yellow-500">
                                <strong>المشكلة:</strong> Timeout errors
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>زيادة max_execution_time</li>
                                    <li>تبسيط HTML</li>
                                    <li>إزالة CSS المعقد</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Arabic Issues -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4">
                            <i class="fas fa-language ml-2"></i>
                            مشاكل اللغة العربية
                        </h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="bg-white p-3 rounded border-r-4 border-blue-500">
                                <strong>المشكلة:</strong> النص العربي لا يظهر
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>استخدام خط DejaVu Sans</li>
                                    <li>إضافة direction: rtl</li>
                                    <li>تحديد charset=UTF-8</li>
                                </ul>
                            </div>
                            
                            <div class="bg-white p-3 rounded border-r-4 border-blue-500">
                                <strong>المشكلة:</strong> اتجاه النص خاطئ
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>إضافة dir="rtl" للـ HTML</li>
                                    <li>استخدام text-align: right</li>
                                    <li>تطبيق unicode-bidi</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Issues -->
                    <div class="bg-green-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-green-800 mb-4">
                            <i class="fas fa-tachometer-alt ml-2"></i>
                            مشاكل الأداء
                        </h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="bg-white p-3 rounded border-r-4 border-green-500">
                                <strong>المشكلة:</strong> PDF بطيء في الإنشاء
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>تقليل عدد الصور</li>
                                    <li>تبسيط CSS</li>
                                    <li>استخدام cache</li>
                                </ul>
                            </div>
                            
                            <div class="bg-white p-3 rounded border-r-4 border-green-500">
                                <strong>المشكلة:</strong> حجم الملف كبير
                                <br><strong>الحل:</strong>
                                <ul class="list-disc list-inside mr-4 mt-1">
                                    <li>ضغط الصور</li>
                                    <li>تقليل الخطوط</li>
                                    <li>إزالة CSS غير المستخدم</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Commands -->
                <div class="mt-8 bg-gray-800 text-green-400 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        <i class="fas fa-terminal ml-2"></i>
                        أوامر مفيدة للإصلاح
                    </h3>
                    
                    <div class="space-y-2 text-sm font-mono">
                        <div class="text-yellow-400"># إعادة تثبيت مكتبة PDF</div>
                        <div>composer require barryvdh/laravel-dompdf --with-all-dependencies</div>
                        
                        <div class="text-yellow-400 mt-3"># مسح cache</div>
                        <div>php artisan cache:clear</div>
                        <div>php artisan config:clear</div>
                        <div>php artisan view:clear</div>
                        
                        <div class="text-yellow-400 mt-3"># إصلاح صلاحيات المجلدات</div>
                        <div>chmod -R 755 storage/</div>
                        <div>chmod -R 755 bootstrap/cache/</div>
                        
                        <div class="text-yellow-400 mt-3"># إنشاء مجلد temp</div>
                        <div>mkdir -p storage/app/temp</div>
                        <div>chmod 755 storage/app/temp</div>
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

    <script>
    function showResults(title, content, type = 'info') {
        const resultsDiv = document.getElementById('results');
        const contentDiv = document.getElementById('results-content');
        
        let bgColor = 'bg-blue-100';
        let textColor = 'text-blue-800';
        let icon = 'fas fa-info-circle';
        
        if (type === 'success') {
            bgColor = 'bg-green-100';
            textColor = 'text-green-800';
            icon = 'fas fa-check-circle';
        } else if (type === 'error') {
            bgColor = 'bg-red-100';
            textColor = 'text-red-800';
            icon = 'fas fa-exclamation-circle';
        }
        
        contentDiv.innerHTML = `
            <div class="${bgColor} ${textColor} p-4 rounded-lg">
                <h4 class="font-semibold mb-2">
                    <i class="${icon} ml-2"></i>
                    ${title}
                </h4>
                <div class="text-sm">${content}</div>
            </div>
        `;
        
        resultsDiv.classList.remove('hidden');
        resultsDiv.scrollIntoView({ behavior: 'smooth' });
    }

    async function testPdfStatus() {
        try {
            const response = await fetch('/pdf-status');
            const data = await response.json();
            
            if (response.ok) {
                let content = '<strong>حالة النظام:</strong><br>';
                Object.entries(data.checks).forEach(([key, value]) => {
                    const status = value ? '✅' : '❌';
                    content += `${status} ${key}<br>`;
                });
                
                content += `<br><strong>معلومات النظام:</strong><br>`;
                content += `PHP: ${data.info['PHP Version']}<br>`;
                content += `Laravel: ${data.info['Laravel Version']}<br>`;
                content += `Memory: ${data.info['Memory Limit']}<br>`;
                
                if (data.pdf_test.success) {
                    content += `<br>✅ اختبار PDF: نجح (${data.pdf_test.size})`;
                } else {
                    content += `<br>❌ اختبار PDF: فشل`;
                }
                
                showResults('فحص النظام', content, 'success');
            } else {
                showResults('خطأ في الفحص', data.message, 'error');
            }
        } catch (error) {
            showResults('خطأ في الاتصال', 'لا يمكن الاتصال بالخادم', 'error');
        }
    }

    function testStreamPdf() {
        showResults('اختبار عرض PDF', 'جاري فتح PDF في نافذة جديدة...', 'info');
        window.open('/stream-pdf', '_blank');
    }

    function testDownloadPdf() {
        showResults('اختبار تحميل PDF', 'جاري تحميل PDF...', 'info');
        window.open('/download-pdf', '_blank');
    }

    function testArabicPdf() {
        showResults('اختبار PDF العربي', 'جاري فتح PDF العربي...', 'info');
        window.open('/arabic-pdf', '_blank');
    }
    </script>
</body>
</html>
