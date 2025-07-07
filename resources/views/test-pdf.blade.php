<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار PDF - نظام MaxCon ERP</title>
    
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
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    <i class="fas fa-file-pdf ml-3 text-red-500"></i>
                    اختبار إنشاء ملفات PDF
                </h1>

                <!-- mPDF Section -->
                <div class="bg-gradient-to-r from-green-100 to-blue-100 rounded-lg p-6 mb-8 border-2 border-green-300">
                    <h2 class="text-xl font-bold text-center mb-4 text-green-800">
                        <i class="fas fa-star ml-2 text-yellow-500"></i>
                        مكتبة mPDF - الأفضل للعربية
                    </h2>
                    <p class="text-center text-green-700 mb-4">
                        مكتبة mPDF توفر دعماً أفضل للحروف العربية المتصلة والتنسيق RTL
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="/mpdf-test" target="_blank"
                           class="bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-eye mr-2"></i>
                            عرض mPDF
                        </a>
                        <a href="/working-invoice-pdf" target="_blank"
                           class="bg-emerald-600 hover:bg-emerald-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>
                            فاتورة تعمل
                        </a>
                        <a href="/simple-invoice-pdf" target="_blank"
                           class="bg-teal-600 hover:bg-teal-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-file-alt mr-2"></i>
                            فاتورة بسيطة
                        </a>
                        <a href="/invoice-with-qr-pdf" target="_blank"
                           class="bg-purple-600 hover:bg-purple-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-qrcode mr-2"></i>
                            فاتورة مع QR
                        </a>
                    </div>
                </div>

                <!-- TCPDF Section -->
                <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-lg p-6 mb-8 border-2 border-purple-300">
                    <h2 class="text-xl font-bold text-center mb-4 text-purple-800">
                        <i class="fas fa-cogs ml-2 text-purple-500"></i>
                        مكتبة TCPDF - بديل آخر للعربية
                    </h2>
                    <p class="text-center text-purple-700 mb-4">
                        مكتبة TCPDF توفر دعماً جيداً للحروف العربية مع ميزات متقدمة
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                        <a href="/tcpdf-test" target="_blank"
                           class="bg-purple-600 hover:bg-purple-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-eye mr-2"></i>
                            اختبار TCPDF
                        </a>
                    </div>
                </div>

                <!-- DomPDF Section -->
                <div class="bg-gradient-to-r from-orange-100 to-red-100 rounded-lg p-6 mb-8 border-2 border-orange-300">
                    <h2 class="text-xl font-bold text-center mb-4 text-orange-800">
                        <i class="fas fa-exclamation-triangle ml-2 text-orange-500"></i>
                        مكتبة DomPDF - مشكلة في الحروف العربية
                    </h2>
                    <p class="text-center text-orange-700 mb-4">
                        مكتبة DomPDF لديها مشاكل في عرض الحروف العربية المتصلة بشكل صحيح
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Arabic PDF Test -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg border border-blue-200">
                        <div class="text-center mb-4">
                            <i class="fas fa-language text-4xl text-blue-600 mb-2"></i>
                            <h3 class="text-lg font-semibold text-blue-800">اختبار PDF العربي</h3>
                        </div>
                        <p class="text-sm text-blue-700 mb-4">
                            اختبار دعم اللغة العربية والاتجاه RTL في ملفات PDF
                        </p>
                        <a href="/arabic-pdf" target="_blank" 
                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded transition duration-200">
                            <i class="fas fa-download mr-2"></i>
                            تحميل PDF العربي
                        </a>
                    </div>

                    <!-- Invoice PDF Test -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg border border-green-200">
                        <div class="text-center mb-4">
                            <i class="fas fa-receipt text-4xl text-green-600 mb-2"></i>
                            <h3 class="text-lg font-semibold text-green-800">فاتورة تجريبية</h3>
                        </div>
                        <p class="text-sm text-green-700 mb-4">
                            اختبار إنشاء فاتورة مبيعات بتنسيق PDF
                        </p>
                        <a href="/test-pdf" target="_blank" 
                           class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded transition duration-200">
                            <i class="fas fa-download mr-2"></i>
                            تحميل فاتورة PDF
                        </a>
                    </div>

                    <!-- Simple PDF Test -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg border border-purple-200">
                        <div class="text-center mb-4">
                            <i class="fas fa-file-alt text-4xl text-purple-600 mb-2"></i>
                            <h3 class="text-lg font-semibold text-purple-800">PDF بسيط</h3>
                        </div>
                        <p class="text-sm text-purple-700 mb-4">
                            اختبار إنشاء ملف PDF بسيط مع نص عربي
                        </p>
                        <a href="/simple-pdf" target="_blank"
                           class="block w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-4 rounded transition duration-200 mb-2">
                            <i class="fas fa-download mr-2"></i>
                            تحميل PDF بسيط
                        </a>
                    </div>

                    <!-- PDF Helper Test -->
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-lg border border-orange-200">
                        <div class="text-center mb-4">
                            <i class="fas fa-tools text-4xl text-orange-600 mb-2"></i>
                            <h3 class="text-lg font-semibold text-orange-800">PDF Helper</h3>
                        </div>
                        <p class="text-sm text-orange-700 mb-4">
                            اختبار مساعد PDF مع تنسيق محسن للعربية
                        </p>
                        <a href="/helper-pdf" target="_blank"
                           class="block w-full bg-orange-600 hover:bg-orange-700 text-white text-center py-2 px-4 rounded transition duration-200">
                            <i class="fas fa-download mr-2"></i>
                            تحميل PDF Helper
                        </a>
                    </div>

                    <!-- Stream PDF Test -->
                    <div class="bg-gradient-to-br from-teal-50 to-teal-100 p-6 rounded-lg border border-teal-200">
                        <div class="text-center mb-4">
                            <i class="fas fa-eye text-4xl text-teal-600 mb-2"></i>
                            <h3 class="text-lg font-semibold text-teal-800">عرض PDF</h3>
                        </div>
                        <p class="text-sm text-teal-700 mb-4">
                            عرض PDF مباشرة في المتصفح
                        </p>
                        <a href="/stream-pdf" target="_blank"
                           class="block w-full bg-teal-600 hover:bg-teal-700 text-white text-center py-2 px-4 rounded transition duration-200">
                            <i class="fas fa-eye mr-2"></i>
                            عرض في المتصفح
                        </a>
                    </div>

                    <!-- Download PDF Test -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-lg border border-red-200">
                        <div class="text-center mb-4">
                            <i class="fas fa-download text-4xl text-red-600 mb-2"></i>
                            <h3 class="text-lg font-semibold text-red-800">تحميل PDF</h3>
                        </div>
                        <p class="text-sm text-red-700 mb-4">
                            تحميل PDF مباشرة إلى الجهاز
                        </p>
                        <a href="/download-pdf"
                           class="block w-full bg-red-600 hover:bg-red-700 text-white text-center py-2 px-4 rounded transition duration-200">
                            <i class="fas fa-download mr-2"></i>
                            تحميل مباشر
                        </a>
                    </div>

                    <!-- PDF Status Check -->
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 rounded-lg border border-indigo-200">
                        <div class="text-center mb-4">
                            <i class="fas fa-info-circle text-4xl text-indigo-600 mb-2"></i>
                            <h3 class="text-lg font-semibold text-indigo-800">حالة النظام</h3>
                        </div>
                        <p class="text-sm text-indigo-700 mb-4">
                            فحص حالة نظام PDF
                        </p>
                        <a href="/pdf-status" target="_blank"
                           class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-4 rounded transition duration-200">
                            <i class="fas fa-stethoscope mr-2"></i>
                            فحص النظام
                        </a>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="bg-gradient-to-r from-yellow-100 to-orange-100 rounded-lg p-6 mb-8 border-2 border-yellow-300">
                    <h2 class="text-xl font-bold text-center mb-4 text-yellow-800">
                        <i class="fas fa-qrcode ml-2 text-yellow-600"></i>
                        QR Code للفواتير
                    </h2>
                    <p class="text-center text-yellow-700 mb-4">
                        كل فاتورة تحتوي على QR Code يشمل جميع التفاصيل والمديونية
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="/qr-display-test" target="_blank"
                           class="bg-yellow-600 hover:bg-yellow-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-qrcode mr-2"></i>
                            عرض QR Code
                        </a>
                        <a href="/qr-test" target="_blank"
                           class="bg-amber-600 hover:bg-amber-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-list mr-2"></i>
                            QR متعدد
                        </a>
                        <a href="/verify-invoice-page/INV-001" target="_blank"
                           class="bg-orange-600 hover:bg-orange-700 text-white text-center py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            صفحة التحقق
                        </a>
                    </div>
                </div>

                <!-- PDF Features -->
                <div class="mt-12 bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-check-circle ml-2 text-green-500"></i>
                        ميزات PDF المدعومة
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>دعم اللغة العربية</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>الاتجاه من اليمين إلى اليسار (RTL)</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>خطوط DejaVu Sans</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>تنسيق A4</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>QR Code لكل فاتورة</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>جداول منسقة</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>ألوان وتدرجات</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>تخطيط متجاوب</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>المديونية السابقة والحالية</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check text-green-500 ml-2"></i>
                                <span>أمان محسن</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Info -->
                <div class="mt-8 bg-blue-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4">
                        <i class="fas fa-cog ml-2"></i>
                        المعلومات التقنية
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <strong>مكتبة PDF:</strong> barryvdh/laravel-dompdf
                        </div>
                        <div>
                            <strong>محرك PDF:</strong> DomPDF
                        </div>
                        <div>
                            <strong>الخط الافتراضي:</strong> DejaVu Sans
                        </div>
                        <div>
                            <strong>حجم الورق:</strong> A4 Portrait
                        </div>
                        <div>
                            <strong>دعم HTML5:</strong> مفعل
                        </div>
                        <div>
                            <strong>دعم CSS:</strong> مفعل
                        </div>
                    </div>
                </div>

                <!-- Troubleshooting -->
                <div class="mt-8 bg-yellow-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-4">
                        <i class="fas fa-exclamation-triangle ml-2"></i>
                        استكشاف الأخطاء
                    </h3>
                    
                    <div class="space-y-3 text-sm text-yellow-700">
                        <div>
                            <strong>إذا لم يعمل PDF:</strong>
                            <ul class="list-disc list-inside mr-4 mt-1">
                                <li>تأكد من أن مكتبة dompdf مثبتة</li>
                                <li>تحقق من صلاحيات مجلد storage/app/temp</li>
                                <li>تأكد من أن الخادم يدعم إنشاء ملفات PDF</li>
                            </ul>
                        </div>
                        
                        <div>
                            <strong>إذا لم تظهر العربية بشكل صحيح:</strong>
                            <ul class="list-disc list-inside mr-4 mt-1">
                                <li>تأكد من استخدام خط DejaVu Sans</li>
                                <li>تحقق من إعدادات RTL في CSS</li>
                                <li>تأكد من ترميز UTF-8</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Commands -->
                <div class="mt-8 bg-gray-800 text-green-400 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        <i class="fas fa-terminal ml-2"></i>
                        أوامر مفيدة
                    </h3>
                    
                    <div class="space-y-2 text-sm font-mono">
                        <div># تثبيت مكتبة PDF</div>
                        <div>composer require barryvdh/laravel-dompdf</div>
                        <div class="mt-3"># نشر إعدادات PDF</div>
                        <div>php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"</div>
                        <div class="mt-3"># إنشاء مجلد temp</div>
                        <div>mkdir -p storage/app/temp</div>
                        <div>chmod 755 storage/app/temp</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-8 space-x-4 space-x-reverse">
                    <a href="/pdf-troubleshoot" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-tools ml-2"></i>
                        استكشاف الأخطاء
                    </a>
                    <a href="/" class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Add click tracking
    document.querySelectorAll('a[href*="pdf"]').forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('PDF link clicked:', this.href);

            // Show loading message
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>جاري الإنشاء...';
            this.style.pointerEvents = 'none';

            // Reset after 3 seconds
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 3000);
        });
    });

    // Generate custom PDF
    function generateCustomPdf() {
        const button = event.target;
        const originalText = button.innerHTML;

        // Show loading
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>جاري الإنشاء...';
        button.disabled = true;

        // Simulate PDF generation
        setTimeout(() => {
            // Open helper PDF for now
            window.open('/helper-pdf', '_blank');

            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
        }, 1000);
    }

    // Show success message when PDF opens
    window.addEventListener('focus', function() {
        // Check if we came back from a PDF
        if (document.hidden === false) {
            console.log('Window focused - PDF might have opened');
        }
    });
    </script>
</body>
</html>
