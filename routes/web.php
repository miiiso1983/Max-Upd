<?php

use Illuminate\Support\Facades\Route;

// Simple test route to bypass complex routing
Route::get('/simple-login', function () {
    return view('simple-login');
});

Route::post('/simple-login', function (Illuminate\Http\Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        // Check if user is super admin and redirect appropriately
        $user = Auth::user();
        if ($user->hasRole('super-admin') || $user->hasRole('super_admin') || $user->is_super_admin) {
            return redirect()->route('master-admin.dashboard')->with('success', 'تم تسجيل الدخول بنجاح');
        }
        return redirect('/dashboard')->with('success', 'تم تسجيل الدخول بنجاح');
    }

    return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
});

// Test route to bypass authentication for master admin dashboard
Route::get('/test-master-admin-direct', function() {
    // Manually authenticate the super admin user for testing
    $user = App\Models\User::where('email', 'admin@maxcon-erp.com')->first();
    Auth::login($user);
    return redirect()->route('master-admin.dashboard');
});

// Test route to access tenants page directly
Route::get('/test-tenants-direct', function() {
    // Manually authenticate the super admin user for testing
    $user = App\Models\User::where('email', 'admin@maxcon-erp.com')->first();
    Auth::login($user);
    return redirect('/master-admin/tenants');
});

// Test route to access system monitoring directly
Route::get('/test-system-monitoring', function() {
    // Manually authenticate the super admin user for testing
    $user = App\Models\User::where('email', 'admin@maxcon-erp.com')->first();
    Auth::login($user);
    return redirect('/master-admin/system/monitoring');
});
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\SuperAdmin\TenantController; // Deprecated - use MasterAdmin instead
use App\Models\Tenant;
use App\Helpers\TenantHelper;

// Sales Module Controllers
use App\Modules\Sales\Controllers\SalesController;
use App\Modules\Sales\Controllers\CustomerController;
use App\Modules\Sales\Controllers\InvoiceController;
use App\Modules\Sales\Controllers\SalesOrderController;
use App\Modules\Sales\Controllers\PaymentController;

// Inventory Module Controllers
use App\Modules\Inventory\Controllers\InventoryController;
use App\Modules\Inventory\Controllers\ProductController;
use App\Modules\Inventory\Controllers\WarehouseController;

// Reports Module Controllers
use App\Modules\Reports\Controllers\ReportsController;

// Admin Controllers
use App\Http\Controllers\Admin\SecurityController;

// Root redirect - Simple redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Test Routes for Components
Route::get('/test-filtered-searchable', function () {
    return view('test-filtered-searchable');
})->name('test.filtered-searchable');

// Standalone test page (no authentication required)
Route::get('/test-filtered-standalone', function () {
    return view('test-filtered-standalone');
})->name('test.filtered-standalone');

// Simple test page
Route::get('/simple-test', function () {
    return view('simple-test');
})->name('simple.test');

// PDF Test Route
Route::get('/test-pdf', function () {
    try {
        // Create a sample invoice data
        $invoice = (object) [
            'id' => 1,
            'invoice_number' => 'INV-2024-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'payment_method' => 'cash',
            'payment_terms' => 30,
            'status' => 'pending',
            'subtotal' => 100000,
            'discount_amount' => 5000,
            'tax_rate' => 10,
            'tax_amount' => 9500,
            'total_amount' => 104500,
            'notes' => 'شكراً لتعاملكم معنا',
            'customer' => (object) [
                'name' => 'أحمد محمد علي',
                'phone' => '+964 770 123 4567',
                'email' => 'ahmed@example.com',
                'address' => 'بغداد - الكرادة'
            ],
            'items' => [
                (object) [
                    'quantity' => 2,
                    'unit_price' => 25000,
                    'discount_percentage' => 5,
                    'total_amount' => 47500,
                    'product' => (object) ['name' => 'باراسيتامول 500 مجم']
                ],
                (object) [
                    'quantity' => 1,
                    'unit_price' => 50000,
                    'discount_percentage' => 0,
                    'total_amount' => 50000,
                    'product' => (object) ['name' => 'فيتامين د3']
                ]
            ]
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales.invoices.pdf', compact('invoice'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                      'isFontSubsettingEnabled' => true,
                      'tempDir' => storage_path('app/temp'),
                      'chroot' => base_path(),
                  ]);

        return $pdf->stream('test-invoice.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'PDF generation failed',
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
})->name('test.pdf');

// Simple PDF Test
Route::get('/simple-pdf', function () {
    try {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>اختبار PDF</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; direction: rtl; text-align: right; }
                .header { background: #3498db; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>اختبار PDF - نظام MaxCon ERP</h1>
            </div>
            <div class="content">
                <h2>مرحباً بك في نظام MaxCon ERP</h2>
                <p>هذا اختبار بسيط لإنشاء ملف PDF باللغة العربية.</p>
                <p>التاريخ: ' . now()->format('Y-m-d H:i:s') . '</p>
                <ul>
                    <li>البند الأول</li>
                    <li>البند الثاني</li>
                    <li>البند الثالث</li>
                </ul>
            </div>
        </body>
        </html>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                  ]);

        return $pdf->stream('simple-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Simple PDF generation failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('simple.pdf');

// Arabic PDF Test
Route::get('/arabic-pdf', function () {
    try {
        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>اختبار PDF العربي</title>
            <style>
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    direction: rtl;
                    text-align: right;
                    font-size: 14px;
                    line-height: 1.6;
                }
                .header {
                    background: #3498db;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    margin-bottom: 20px;
                }
                .content {
                    padding: 20px;
                    direction: rtl;
                    text-align: right;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                    direction: rtl;
                }
                .table th, .table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: center;
                }
                .table th {
                    background: #f2f2f2;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>نظام MaxCon ERP</h1>
                <p>اختبار دعم اللغة العربية في PDF</p>
            </div>
            <div class="content">
                <h2>مرحباً بك في نظام إدارة الموارد المؤسسية</h2>
                <p>هذا اختبار لدعم اللغة العربية والاتجاه من اليمين إلى اليسار (RTL) في ملفات PDF.</p>

                <h3>معلومات النظام:</h3>
                <ul>
                    <li>اسم النظام: MaxCon ERP</li>
                    <li>الإصدار: 1.0.0</li>
                    <li>التاريخ: ' . now()->format('Y-m-d') . '</li>
                    <li>الوقت: ' . now()->format('H:i:s') . '</li>
                </ul>

                <h3>جدول تجريبي:</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>الرقم</th>
                            <th>اسم المنتج</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>باراسيتامول 500 مجم</td>
                            <td>10</td>
                            <td>25,000 د.ع</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>فيتامين د3</td>
                            <td>5</td>
                            <td>50,000 د.ع</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>أوميجا 3</td>
                            <td>3</td>
                            <td>75,000 د.ع</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>المجموع الكلي: 150,000 دينار عراقي</strong></p>

                <hr>
                <p style="text-align: center; margin-top: 30px;">
                    شكراً لاستخدامكم نظام MaxCon ERP
                </p>
            </div>
        </body>
        </html>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                      'isFontSubsettingEnabled' => true,
                      'tempDir' => storage_path('app/temp'),
                      'chroot' => base_path(),
                  ]);

        return $pdf->stream('arabic-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Arabic PDF generation failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('arabic.pdf');

// PDF Test Page
Route::get('/test-pdf-page', function () {
    return view('test-pdf');
})->name('test.pdf.page');

// PDF Helper Test
Route::get('/helper-pdf', function () {
    try {
        // Ensure temp directory exists
        \App\Helpers\PdfHelper::ensureTempDirectory();

        $content = '
        <h2>مرحباً بك في نظام MaxCon ERP</h2>
        <p>هذا اختبار لـ PDF Helper مع دعم اللغة العربية.</p>

        <h3>معلومات النظام:</h3>
        <ul>
            <li>اسم النظام: MaxCon ERP</li>
            <li>الإصدار: 1.0.0</li>
            <li>التاريخ: ' . now()->format('Y-m-d') . '</li>
            <li>الوقت: ' . now()->format('H:i:s') . '</li>
        </ul>

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>اسم المنتج</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>باراسيتامول 500 مجم</td>
                    <td>10</td>
                    <td>25,000 د.ع</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>فيتامين د3</td>
                    <td>5</td>
                    <td>50,000 د.ع</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-4 text-center">
            <strong>المجموع الكلي: 75,000 دينار عراقي</strong>
        </div>';

        $pdf = \App\Helpers\PdfHelper::createSimpleArabicPdf(
            'اختبار PDF Helper - نظام MaxCon ERP',
            $content
        );

        return $pdf->stream('helper-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'PDF Helper test failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('helper.pdf');

// Diagnostic PDF Test
Route::get('/diagnostic-pdf', function () {
    try {
        // Check if all required classes exist
        $checks = [
            'PDF Facade' => class_exists('\Barryvdh\DomPDF\Facade\Pdf'),
            'DomPDF' => class_exists('\Dompdf\Dompdf'),
            'Temp Directory' => is_dir(storage_path('app/temp')),
            'Temp Writable' => is_writable(storage_path('app/temp')),
        ];

        // If any check fails, return diagnostic info
        foreach ($checks as $check => $result) {
            if (!$result) {
                return response()->json([
                    'error' => 'Diagnostic failed',
                    'failed_check' => $check,
                    'all_checks' => $checks,
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version()
                ], 500);
            }
        }

        // Create very simple HTML
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Test</title></head><body><h1>PDF Test</h1><p>This is a simple test.</p></body></html>';

        // Try to create PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                  ->setPaper('a4', 'portrait')
                  ->setOptions(['defaultFont' => 'DejaVu Sans']);

        // Return as download
        return $pdf->download('diagnostic-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Diagnostic PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'checks' => $checks ?? []
        ], 500);
    }
})->name('diagnostic.pdf');

// PDF Status Check
Route::get('/pdf-status', function () {
    try {
        $checks = [
            'PDF Facade Available' => class_exists('\Barryvdh\DomPDF\Facade\Pdf'),
            'DomPDF Available' => class_exists('\Dompdf\Dompdf'),
            'Temp Directory Exists' => is_dir(storage_path('app/temp')),
            'Temp Directory Writable' => is_writable(storage_path('app/temp')),
            'Storage Directory Writable' => is_writable(storage_path('app')),
            'Base Path Readable' => is_readable(base_path()),
        ];

        $info = [
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Memory Limit' => ini_get('memory_limit'),
            'Max Execution Time' => ini_get('max_execution_time'),
            'Temp Directory' => storage_path('app/temp'),
            'Base Path' => base_path(),
        ];

        // Try to create a simple PDF
        $pdfTest = null;
        try {
            $html = '<h1>Test</h1>';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $output = $pdf->output();
            $pdfTest = [
                'success' => true,
                'size' => strlen($output) . ' bytes',
                'type' => 'PDF created successfully'
            ];
        } catch (\Exception $e) {
            $pdfTest = [
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }

        return response()->json([
            'status' => 'PDF System Status',
            'checks' => $checks,
            'info' => $info,
            'pdf_test' => $pdfTest,
            'timestamp' => now()->toDateTimeString()
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Status check failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('pdf.status');

// PDF Stream (display in browser)
Route::get('/stream-pdf', function () {
    try {
        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>اختبار PDF</title>
            <style>
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    direction: rtl;
                    text-align: right;
                    padding: 20px;
                    font-size: 14px;
                }
                .header {
                    background: #3498db;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    margin-bottom: 20px;
                }
                .content {
                    line-height: 1.6;
                }
                ul {
                    margin: 10px 0;
                    padding-right: 20px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>نظام MaxCon ERP</h1>
                <p>اختبار PDF مع عرض في المتصفح</p>
            </div>
            <div class="content">
                <h2>مرحباً بك في النظام</h2>
                <p>هذا اختبار لعرض PDF مباشرة في المتصفح بدون تحميل.</p>
                <p><strong>التاريخ:</strong> ' . now()->format('Y-m-d H:i:s') . '</p>

                <h3>قائمة العناصر:</h3>
                <ul>
                    <li>العنصر الأول - اختبار النص العربي</li>
                    <li>العنصر الثاني - اختبار الترقيم والأرقام 123</li>
                    <li>العنصر الثالث - اختبار الرموز والعملات د.ع</li>
                </ul>

                <p style="margin-top: 30px; text-align: center; border-top: 1px solid #ccc; padding-top: 20px;">
                    <strong>شكراً لاستخدامكم نظام MaxCon ERP</strong>
                </p>
            </div>
        </body>
        </html>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                  ]);

        // Return PDF as inline display
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="stream-test.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Stream PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('stream.pdf');

// Force Download PDF
Route::get('/download-pdf', function () {
    try {
        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>تحميل PDF</title>
            <style>
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    direction: rtl;
                    text-align: right;
                    padding: 20px;
                }
                .header {
                    background: #e74c3c;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>تحميل PDF</h1>
                <p>ملف PDF للتحميل المباشر</p>
            </div>
            <h2>تم إنشاء الملف بنجاح</h2>
            <p>هذا الملف تم تحميله مباشرة إلى جهازك.</p>
            <p>التاريخ: ' . now()->format('Y-m-d H:i:s') . '</p>
        </body>
        </html>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                  ]);

        // Force download
        return $pdf->download('maxcon-download-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Download PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('download.pdf');

// PDF Troubleshooting Page
Route::get('/pdf-troubleshoot', function () {
    return view('pdf-troubleshoot');
})->name('pdf.troubleshoot');

// mPDF Tests (Better Arabic Support)
Route::get('/mpdf-test', function () {
    try {
        \App\Helpers\MPdfHelper::ensureTempDirectory();

        $content = '
        <h2>مرحباً بك في نظام MaxCon ERP</h2>
        <p>هذا اختبار لمكتبة mPDF مع دعم محسن للغة العربية والحروف المتصلة.</p>

        <h3>معلومات النظام:</h3>
        <ul>
            <li>اسم النظام: نظام MaxCon لإدارة الموارد المؤسسية</li>
            <li>الإصدار: الإصدار الأول 1.0.0</li>
            <li>التاريخ: ' . now()->format('Y-m-d') . '</li>
            <li>الوقت: ' . now()->format('H:i:s') . '</li>
        </ul>

        <h3>اختبار النصوص العربية:</h3>
        <p>هذا نص تجريبي لاختبار الحروف العربية المتصلة مثل: <strong>محمد، أحمد، فاطمة، خديجة</strong></p>
        <p>اختبار الأرقام العربية: ١٢٣٤٥٦٧٨٩٠</p>
        <p>اختبار الأرقام الإنجليزية: 1234567890</p>
        <p>اختبار العملات: 25,000 دينار عراقي - $100 - €50</p>

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>اسم المنتج</th>
                    <th>الكمية</th>
                    <th>السعر بالدينار العراقي</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>باراسيتامول 500 مجم</td>
                    <td>10</td>
                    <td>2,500</td>
                    <td>25,000</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>فيتامين د3 كبسولات</td>
                    <td>5</td>
                    <td>10,000</td>
                    <td>50,000</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>أوميجا 3 زيت السمك</td>
                    <td>3</td>
                    <td>25,000</td>
                    <td>75,000</td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background: #e8f5e8; font-weight: bold;">
                    <td colspan="4">المجموع الكلي</td>
                    <td>150,000 دينار عراقي</td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-4 p-3 bg-blue-100 rounded">
            <h4>ملاحظات مهمة:</h4>
            <ul>
                <li>جميع الحروف العربية يجب أن تظهر متصلة بشكل صحيح</li>
                <li>اتجاه النص من اليمين إلى اليسار</li>
                <li>الجداول منسقة بشكل صحيح</li>
                <li>الأرقام والعملات واضحة</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>شكراً لاستخدامكم نظام MaxCon ERP</strong></p>
            <p>تم إنشاء هذا التقرير في: ' . now()->format('Y-m-d H:i:s') . '</p>
        </div>';

        $mpdf = \App\Helpers\MPdfHelper::createSimpleArabicPdf(
            'اختبار mPDF - نظام MaxCon ERP',
            $content
        );

        return \App\Helpers\MPdfHelper::streamPdf($mpdf, 'mpdf-arabic-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'mPDF test failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('mpdf.test');

// mPDF Download Test
Route::get('/mpdf-download', function () {
    try {
        \App\Helpers\MPdfHelper::ensureTempDirectory();

        $content = '
        <h2>تحميل ملف PDF باستخدام mPDF</h2>
        <p>هذا اختبار لتحميل ملف PDF مع دعم الحروف العربية المتصلة.</p>

        <h3>اختبار الكلمات العربية:</h3>
        <ul>
            <li><strong>محمد</strong> - يجب أن تظهر الحروف متصلة</li>
            <li><strong>أحمد</strong> - اختبار الألف والهمزة</li>
            <li><strong>فاطمة</strong> - اختبار التاء المربوطة</li>
            <li><strong>خديجة</strong> - اختبار الجيم والخاء</li>
            <li><strong>عبدالله</strong> - اختبار الحروف المتصلة الطويلة</li>
        </ul>

        <h3>اختبار الجمل:</h3>
        <p>بسم الله الرحمن الرحيم، والحمد لله رب العالمين.</p>
        <p>هذا نص تجريبي لاختبار جودة عرض النصوص العربية في ملفات PDF.</p>

        <div class="mt-4 p-3 bg-green-100 rounded">
            <h4>نتائج متوقعة:</h4>
            <ul>
                <li>✅ الحروف العربية متصلة بشكل صحيح</li>
                <li>✅ اتجاه النص من اليمين إلى اليسار</li>
                <li>✅ التنسيق والألوان واضحة</li>
                <li>✅ الخطوط مقروءة وجميلة</li>
            </ul>
        </div>';

        $mpdf = \App\Helpers\MPdfHelper::createSimpleArabicPdf(
            'تحميل mPDF - اختبار الحروف العربية',
            $content
        );

        return \App\Helpers\MPdfHelper::downloadPdf($mpdf, 'mpdf-arabic-download.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'mPDF download failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('mpdf.download');

// mPDF Invoice Test
Route::get('/mpdf-invoice', function () {
    try {
        \App\Helpers\MPdfHelper::ensureTempDirectory();

        // Create sample invoice data
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 150000,
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'payment_method' => 'نقداً',
            'payment_status' => 'غير مدفوع',
            'paid_amount' => 0,
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543',
                'email' => 'pharmacy@example.com',
                'address' => 'بغداد - الكرخ - حي الجامعة',
                'customer_number' => 'CUST-001',
                'type' => 'صيدلية'
            ],
            'items' => collect([
                (object) [
                    'product' => (object) [
                        'name' => 'باراسيتامول 500 مجم',
                        'description' => 'أقراص مسكنة للألم وخافضة للحرارة'
                    ],
                    'quantity' => 10,
                    'unit_price' => 2500,
                    'discount_amount' => 0,
                    'total_amount' => 25000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'فيتامين د3 كبسولات',
                        'description' => 'مكمل غذائي لتقوية العظام'
                    ],
                    'quantity' => 5,
                    'unit_price' => 10000,
                    'discount_amount' => 0,
                    'total_amount' => 50000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'أوميجا 3 زيت السمك',
                        'description' => 'مكمل غذائي لصحة القلب والدماغ'
                    ],
                    'quantity' => 3,
                    'unit_price' => 25000,
                    'discount_amount' => 0,
                    'total_amount' => 75000
                ]
            ])
        ];

        $mpdf = \App\Helpers\MPdfHelper::generateInvoice($invoice);

        return \App\Helpers\MPdfHelper::streamPdf($mpdf, 'invoice-' . $invoice->invoice_number . '.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'mPDF invoice failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('mpdf.invoice');

// TCPDF Test (Another Alternative)
Route::get('/tcpdf-test', function () {
    try {
        \App\Helpers\TCPdfHelper::ensureTempDirectory();

        $content = '
        <h2>مرحباً بك في نظام MaxCon ERP</h2>
        <p>هذا اختبار لمكتبة TCPDF مع دعم الحروف العربية المتصلة.</p>

        <h3>اختبار النصوص العربية:</h3>
        <p>هذا نص تجريبي لاختبار الحروف العربية المتصلة مثل: <strong>محمد، أحمد، فاطمة، خديجة</strong></p>
        <p>اختبار الكلمات الطويلة: <strong>عبدالرحمن، عبدالله، محمدأمين</strong></p>
        <p>اختبار الأرقام: ١٢٣٤٥٦٧٨٩٠ و 1234567890</p>
        <p>اختبار العملات: 25,000 دينار عراقي</p>

        <h3>جدول تجريبي:</h3>
        <table>
            <tr>
                <th>الرقم</th>
                <th>اسم المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
            </tr>
            <tr>
                <td>1</td>
                <td>باراسيتامول 500 مجم</td>
                <td>10</td>
                <td>25,000 د.ع</td>
            </tr>
            <tr>
                <td>2</td>
                <td>فيتامين د3</td>
                <td>5</td>
                <td>50,000 د.ع</td>
            </tr>
        </table>

        <div class="bg-blue">
            <h4>ملاحظات:</h4>
            <ul>
                <li>الحروف العربية يجب أن تظهر متصلة</li>
                <li>اتجاه النص من اليمين إلى اليسار</li>
                <li>التنسيق والجداول صحيحة</li>
            </ul>
        </div>';

        $pdf = \App\Helpers\TCPdfHelper::createSimpleArabicPdf(
            'اختبار TCPDF - نظام MaxCon ERP',
            $content
        );

        return \App\Helpers\TCPdfHelper::streamPdf($pdf, 'tcpdf-arabic-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'TCPDF test failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('tcpdf.test');

// PDF Libraries Comparison Page
Route::get('/pdf-comparison', function () {
    return view('pdf-comparison');
})->name('pdf.comparison');

// Quick Invoice PDF Test (if no invoices exist)
Route::get('/quick-invoice-pdf', function () {
    try {
        // Create sample invoice data
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 150000,
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'previous_balance' => 75000,
            'payment_method' => 'نقداً',
            'payment_status' => 'غير مدفوع',
            'payment_date' => null,
            'paid_amount' => 0,
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543',
                'email' => 'pharmacy@example.com',
                'address' => 'بغداد - الكرخ - حي الجامعة',
                'customer_number' => 'CUST-001',
                'type' => 'صيدلية'
            ],
            'items' => collect([
                (object) [
                    'product' => (object) [
                        'name' => 'باراسيتامول 500 مجم',
                        'description' => 'أقراص مسكنة للألم وخافضة للحرارة - عبوة 20 قرص',
                        'code' => 'MED-001'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 10,
                    'unit_price' => 2500,
                    'discount_amount' => 0,
                    'total_amount' => 25000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'فيتامين د3 كبسولات',
                        'description' => 'مكمل غذائي لتقوية العظام - عبوة 30 كبسولة',
                        'code' => 'VIT-003'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 5,
                    'unit_price' => 10000,
                    'discount_amount' => 0,
                    'total_amount' => 50000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'أوميجا 3 زيت السمك',
                        'description' => 'مكمل غذائي لصحة القلب والدماغ - عبوة 60 كبسولة',
                        'code' => 'SUP-007'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 3,
                    'unit_price' => 25000,
                    'discount_amount' => 0,
                    'total_amount' => 75000
                ]
            ])
        ];

        // Use mPDF for better Arabic support
        $mpdf = \App\Helpers\MPdfHelper::generateInvoice($invoice);

        return \App\Helpers\MPdfHelper::streamPdf($mpdf, 'invoice-' . $invoice->invoice_number . '.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Quick invoice PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('quick.invoice.pdf');

// Invoice Verification Route (for QR Code)
Route::get('/verify-invoice/{invoice_number}', function ($invoice_number) {
    try {
        // Get current tenant
        /** @var Tenant|null $tenant */
        $tenant = Tenant::current();

        // In real application, fetch from database
        // For demo, create sample data
        $invoice = (object) [
            'invoice_number' => $invoice_number,
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 150000,
            'previous_balance' => 75000,
            'paid_amount' => 0,
            'payment_status' => 'غير مدفوع',
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543',
                'address' => 'بغداد - الكرخ - حي الجامعة'
            ],
            'items' => collect([
                (object) ['product' => (object) ['name' => 'باراسيتامول 500 مجم'], 'quantity' => 10, 'unit_price' => 2500],
                (object) ['product' => (object) ['name' => 'فيتامين د3'], 'quantity' => 5, 'unit_price' => 10000],
                (object) ['product' => (object) ['name' => 'أوميجا 3'], 'quantity' => 3, 'unit_price' => 25000]
            ])
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'تم التحقق من الفاتورة بنجاح',
            'invoice' => [
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name,
                'total_amount' => number_format($invoice->total_amount, 0) . ' د.ع',
                'previous_balance' => number_format($invoice->previous_balance, 0) . ' د.ع',
                'current_balance' => number_format($invoice->previous_balance + $invoice->total_amount - $invoice->paid_amount, 0) . ' د.ع',
                'payment_status' => $invoice->payment_status,
                'issue_date' => $invoice->created_at->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'items_count' => $invoice->items->count(),
                'verification_time' => now()->format('Y-m-d H:i:s')
            ],
            'company' => [
                // @phpstan-ignore-next-line - Properties exist in Tenant model
                'name' => TenantHelper::getCompanyName(),
                // @phpstan-ignore-next-line - Properties exist in Tenant model
                'phone' => TenantHelper::getCompanyInfo()['phone'],
                // @phpstan-ignore-next-line - Properties exist in Tenant model
                'email' => TenantHelper::getCompanyInfo()['email'],
                // @phpstan-ignore-next-line - Properties exist in Tenant model
                'address' => TenantHelper::getFormattedAddress()
            ]
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'فشل في التحقق من الفاتورة',
            'error' => $e->getMessage()
        ], 404, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }
})->name('verify.invoice');

// Invoice Verification HTML Page
Route::get('/verify-invoice-page/{invoice_number}', function ($invoice_number) {
    try {
        // In real application, fetch from database
        $invoice = (object) [
            'invoice_number' => $invoice_number,
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 150000,
            'previous_balance' => 75000,
            'paid_amount' => 0,
            'payment_status' => 'غير مدفوع',
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543',
                'address' => 'بغداد - الكرخ - حي الجامعة'
            ],
            'items' => collect([
                (object) ['product' => (object) ['name' => 'باراسيتامول 500 مجم'], 'quantity' => 10, 'unit_price' => 2500],
                (object) ['product' => (object) ['name' => 'فيتامين د3'], 'quantity' => 5, 'unit_price' => 10000],
                (object) ['product' => (object) ['name' => 'أوميجا 3'], 'quantity' => 3, 'unit_price' => 25000]
            ])
        ];

        $currentBalance = $invoice->previous_balance + $invoice->total_amount - $invoice->paid_amount;

        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>التحقق من الفاتورة - ' . $invoice->invoice_number . '</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
            </style>
        </head>
        <body class="bg-gradient-to-br from-blue-50 to-green-50 min-h-screen">
            <div class="container mx-auto px-4 py-8">
                <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 text-white p-6">
                        <div class="text-center">
                            <i class="fas fa-check-circle text-4xl mb-4"></i>
                            <h1 class="text-2xl font-bold">تم التحقق من الفاتورة بنجاح</h1>
                            <p class="text-green-100 mt-2">الفاتورة صحيحة ومعتمدة من شركة MaxCon للأدوية</p>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Invoice Info -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-blue-800 mb-3">
                                    <i class="fas fa-file-invoice ml-2"></i>
                                    معلومات الفاتورة
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">رقم الفاتورة:</span>
                                        <span class="font-semibold">' . $invoice->invoice_number . '</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">تاريخ الإصدار:</span>
                                        <span class="font-semibold">' . $invoice->created_at->format('Y-m-d') . '</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">تاريخ الاستحقاق:</span>
                                        <span class="font-semibold">' . $invoice->due_date->format('Y-m-d') . '</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">عدد الأصناف:</span>
                                        <span class="font-semibold">' . $invoice->items->count() . ' صنف</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">حالة الدفع:</span>
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">' . $invoice->payment_status . '</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold text-green-800 mb-3">
                                    <i class="fas fa-user ml-2"></i>
                                    معلومات العميل
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">اسم العميل:</span>
                                        <span class="font-semibold">' . $invoice->customer->name . '</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">رقم الهاتف:</span>
                                        <span class="font-semibold">' . $invoice->customer->phone . '</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">العنوان:</span>
                                        <span class="font-semibold">' . $invoice->customer->address . '</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary -->
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">
                                <i class="fas fa-calculator ml-2"></i>
                                الملخص المالي
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-blue-100 p-3 rounded text-center">
                                    <div class="text-blue-600 text-sm">المبلغ الإجمالي</div>
                                    <div class="text-blue-800 font-bold text-lg">' . number_format($invoice->total_amount, 0) . ' د.ع</div>
                                </div>
                                <div class="bg-red-100 p-3 rounded text-center">
                                    <div class="text-red-600 text-sm">المديونية السابقة</div>
                                    <div class="text-red-800 font-bold text-lg">' . number_format($invoice->previous_balance, 0) . ' د.ع</div>
                                </div>
                                <div class="bg-green-100 p-3 rounded text-center">
                                    <div class="text-green-600 text-sm">المبلغ المدفوع</div>
                                    <div class="text-green-800 font-bold text-lg">' . number_format($invoice->paid_amount, 0) . ' د.ع</div>
                                </div>
                                <div class="bg-orange-100 p-3 rounded text-center">
                                    <div class="text-orange-600 text-sm">المديونية الحالية</div>
                                    <div class="text-orange-800 font-bold text-lg">' . number_format($currentBalance, 0) . ' د.ع</div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Info -->
                        <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-800 mb-3">
                                <i class="fas fa-building ml-2"></i>
                                معلومات الشركة
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong>الاسم:</strong> شركة MaxCon للأدوية
                                </div>
                                <div>
                                    <strong>الهاتف:</strong> +964 770 123 4567
                                </div>
                                <div>
                                    <strong>البريد الإلكتروني:</strong> info@maxcon.iq
                                </div>
                                <div>
                                    <strong>العنوان:</strong> بغداد - الكرادة
                                </div>
                            </div>
                        </div>

                        <!-- Verification Info -->
                        <div class="mt-6 bg-green-50 p-4 rounded-lg border-r-4 border-green-500">
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt text-green-500 text-2xl ml-3"></i>
                                <div>
                                    <h4 class="font-semibold text-green-800">تم التحقق بنجاح</h4>
                                    <p class="text-green-600 text-sm">تم التحقق من صحة هذه الفاتورة في: ' . now()->format('Y-m-d H:i:s') . '</p>
                                    <p class="text-green-600 text-sm">هذه الفاتورة صادرة رسمياً من شركة MaxCon للأدوية</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 text-center space-x-4 space-x-reverse">
                            <a href="/quick-invoice-pdf" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-download ml-2"></i>
                                تحميل الفاتورة
                            </a>
                            <a href="/test-pdf-page"
                               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-arrow-right ml-2"></i>
                                العودة للاختبارات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        return response($html);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'فشل في التحقق من الفاتورة',
            'error' => $e->getMessage()
        ], 404, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }
})->name('verify.invoice.page');

// Professional Invoice PDF
Route::get('/professional-invoice-pdf', function () {
    try {
        \App\Helpers\MPdfHelper::ensureTempDirectory();

        // Create detailed professional invoice data
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 285000,
            'subtotal' => 275000,
            'discount_amount' => 15000,
            'tax_amount' => 25000,
            'delivery_fee' => 5000,
            'previous_balance' => 125000,
            'payment_method' => 'آجل - 30 يوم',
            'payment_status' => 'غير مدفوع',
            'payment_date' => null,
            'paid_amount' => 0,
            'sales_rep' => 'أحمد محمد علي',
            'reference_number' => 'REF-2025-001',
            'customer' => (object) [
                'name' => 'صيدلية النور الطبية',
                'phone' => '+964 770 123 4567',
                'email' => 'alnoor.pharmacy@gmail.com',
                'address' => 'بغداد - الكرخ - شارع الأطباء - مجمع النور الطبي',
                'customer_number' => 'CUST-2025-001',
                'type' => 'صيدلية كبرى',
                'tax_number' => 'TAX-123456789'
            ],
            'items' => collect([
                (object) [
                    'product' => (object) [
                        'name' => 'أموكسيسيلين 500 مجم',
                        'description' => 'مضاد حيوي واسع المجال - كبسولات',
                        'code' => 'ANT-001'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 15,
                    'unit_price' => 8500,
                    'discount_amount' => 5000,
                    'total_amount' => 122500
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'أوميجا 3 بلس فيتامين د',
                        'description' => 'مكمل غذائي متطور - 60 كبسولة جيلاتينية',
                        'code' => 'SUP-015'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 8,
                    'unit_price' => 15000,
                    'discount_amount' => 8000,
                    'total_amount' => 112000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'فيتامين ب المركب',
                        'description' => 'مجموعة فيتامينات ب الكاملة - أقراص مغلفة',
                        'code' => 'VIT-008'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 10,
                    'unit_price' => 4200,
                    'discount_amount' => 2000,
                    'total_amount' => 40000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'كريم مرطب للبشرة الحساسة',
                        'description' => 'كريم طبي مرطب ومهدئ - 100 مل',
                        'code' => 'COS-003'
                    ],
                    'unit' => 'أنبوب',
                    'quantity' => 12,
                    'unit_price' => 3500,
                    'discount_amount' => 0,
                    'total_amount' => 42000
                ]
            ])
        ];

        $mpdf = \App\Helpers\MPdfHelper::generateInvoice($invoice);

        return \App\Helpers\MPdfHelper::streamPdf($mpdf, 'professional-invoice-' . $invoice->invoice_number . '.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Professional invoice PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('professional.invoice.pdf');

// Simple Invoice PDF without QR Code (temporary solution)
Route::get('/simple-invoice-pdf', function () {
    try {
        \App\Helpers\MPdfHelper::ensureTempDirectory();

        // Create sample invoice data
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 150000,
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'previous_balance' => 75000,
            'payment_method' => 'نقداً',
            'payment_status' => 'غير مدفوع',
            'payment_date' => null,
            'paid_amount' => 0,
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543',
                'email' => 'pharmacy@example.com',
                'address' => 'بغداد - الكرخ - حي الجامعة',
                'customer_number' => 'CUST-001',
                'type' => 'صيدلية'
            ],
            'items' => collect([
                (object) [
                    'product' => (object) [
                        'name' => 'باراسيتامول 500 مجم',
                        'description' => 'أقراص مسكنة للألم وخافضة للحرارة'
                    ],
                    'quantity' => 10,
                    'unit_price' => 2500,
                    'discount_amount' => 0,
                    'total_amount' => 25000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'فيتامين د3 كبسولات',
                        'description' => 'مكمل غذائي لتقوية العظام'
                    ],
                    'quantity' => 5,
                    'unit_price' => 10000,
                    'discount_amount' => 0,
                    'total_amount' => 50000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'أوميجا 3 زيت السمك',
                        'description' => 'مكمل غذائي لصحة القلب والدماغ'
                    ],
                    'quantity' => 3,
                    'unit_price' => 25000,
                    'discount_amount' => 0,
                    'total_amount' => 75000
                ]
            ])
        ];

        // Create simple HTML without QR Code
        $html = view('sales.invoices.simple', compact('invoice'))->render();
        $mpdf = \App\Helpers\MPdfHelper::createFromHtml($html);

        return \App\Helpers\MPdfHelper::streamPdf($mpdf, 'simple-invoice-' . $invoice->invoice_number . '.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Simple invoice PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('simple.invoice.pdf');

// Working Invoice PDF (Simplified)
Route::get('/working-invoice-pdf', function () {
    try {
        \App\Helpers\MPdfHelper::ensureTempDirectory();

        // Get current tenant
        /** @var Tenant|null $tenant */
        $tenant = Tenant::current();

        // Create very simple invoice data
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 150000,
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'previous_balance' => 75000,
            'paid_amount' => 0,
            'tenant' => $tenant, // Add tenant information
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543',
                'email' => 'pharmacy@example.com',
                'address' => 'بغداد - الكرخ - حي الجامعة'
            ],
            'items' => collect([
                (object) [
                    'product' => (object) [
                        'name' => 'باراسيتامول 500 مجم',
                        'description' => 'أقراص مسكنة للألم'
                    ],
                    'quantity' => 10,
                    'unit_price' => 2500,
                    'total_amount' => 25000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'فيتامين د3',
                        'description' => 'مكمل غذائي'
                    ],
                    'quantity' => 5,
                    'unit_price' => 10000,
                    'total_amount' => 50000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'أوميجا 3',
                        'description' => 'زيت السمك'
                    ],
                    'quantity' => 3,
                    'unit_price' => 25000,
                    'total_amount' => 75000
                ]
            ])
        ];

        // Get company information
        // @phpstan-ignore-next-line - Properties exist in Tenant model
        $companyInfo = TenantHelper::getCompanyInfo();
        $companyName = $companyInfo['name'];
        // @phpstan-ignore-next-line - Properties exist in Tenant model
        $companyAddress = TenantHelper::getFormattedAddress();
        $companyPhone = $companyInfo['phone'];
        $companyEmail = $companyInfo['email'];

        // Create basic HTML
        $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فاتورة رقم ' . $invoice->invoice_number . '</title>
    <style>
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            direction: rtl;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            background: #2a5298;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            width: 48%;
        }
        .balance-section {
            background: #e9ecef;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .balance-grid {
            display: flex;
            justify-content: space-between;
        }
        .balance-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            margin: 0 5px;
            flex: 1;
        }
        .balance-amount {
            font-size: 16px;
            font-weight: bold;
            color: #2a5298;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #2a5298;
            color: white;
        }
        .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">' . $companyName . '</div>
        <div>نظام إدارة الموارد المؤسسية</div>
        <div>' . $companyAddress . '</div>
        <div>هاتف: ' . $companyPhone . '</div>
        <div>البريد الإلكتروني: ' . $companyEmail . '</div>
    </div>

    <div class="invoice-info">
        <div class="info-box">
            <h3>تفاصيل الفاتورة</h3>
            <p><strong>رقم الفاتورة:</strong> ' . $invoice->invoice_number . '</p>
            <p><strong>تاريخ الإصدار:</strong> ' . $invoice->created_at->format('Y-m-d') . '</p>
            <p><strong>تاريخ الاستحقاق:</strong> ' . $invoice->due_date->format('Y-m-d') . '</p>
        </div>
        <div class="info-box">
            <h3>بيانات العميل</h3>
            <p><strong>اسم العميل:</strong> ' . $invoice->customer->name . '</p>
            <p><strong>رقم الهاتف:</strong> ' . $invoice->customer->phone . '</p>
            <p><strong>العنوان:</strong> ' . $invoice->customer->address . '</p>
        </div>
    </div>

    <div class="balance-section">
        <h3 style="text-align: center; margin-bottom: 15px;">ملخص المديونية</h3>
        <div class="balance-grid">
            <div class="balance-item">
                <div>المديونية السابقة</div>
                <div class="balance-amount">' . number_format($invoice->previous_balance, 0) . ' د.ع</div>
            </div>
            <div class="balance-item">
                <div>فاتورة حالية</div>
                <div class="balance-amount">' . number_format($invoice->total_amount, 0) . ' د.ع</div>
            </div>
            <div class="balance-item">
                <div>المديونية الحالية</div>
                <div class="balance-amount">' . number_format($invoice->previous_balance + $invoice->total_amount - $invoice->paid_amount, 0) . ' د.ع</div>
            </div>
        </div>
    </div>

    <h3>تفاصيل الأصناف</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم المنتج</th>
                <th>الوصف</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($invoice->items as $index => $item) {
            $html .= '<tr>
                <td>' . ($index + 1) . '</td>
                <td>' . $item->product->name . '</td>
                <td>' . $item->product->description . '</td>
                <td>' . number_format($item->quantity, 0) . '</td>
                <td>' . number_format($item->unit_price, 0) . ' د.ع</td>
                <td>' . number_format($item->total_amount, 0) . ' د.ع</td>
            </tr>';
        }

        $html .= '
            <tr class="total-row">
                <td colspan="5">المجموع الإجمالي</td>
                <td>' . number_format($invoice->total_amount, 0) . ' د.ع</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>شكراً لتعاملكم معنا</strong></p>
        <p>' . $companyName . ' - نحو صحة أفضل للجميع</p>
        <p>تم إنشاء هذه الفاتورة في: ' . now()->format('Y-m-d H:i:s') . '</p>
    </div>
</body>
</html>';

        $mpdf = \App\Helpers\MPdfHelper::createFromHtml($html);

        return \App\Helpers\MPdfHelper::streamPdf($mpdf, 'working-invoice-' . $invoice->invoice_number . '.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Working invoice PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('working.invoice.pdf');

// Invoice with QR Code (Fixed)
Route::get('/invoice-with-qr-pdf', function () {
    try {
        \App\Helpers\MPdfHelper::ensureTempDirectory();

        // Get current tenant
        /** @var Tenant|null $tenant */
        $tenant = Tenant::current();

        // Create invoice data with QR
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 285000,
            'subtotal' => 275000,
            'discount_amount' => 15000,
            'tax_amount' => 25000,
            'previous_balance' => 125000,
            'paid_amount' => 0,
            'payment_status' => 'غير مدفوع',
            'payment_method' => 'آجل - 30 يوم',
            'sales_rep' => 'أحمد محمد علي',
            'tenant' => $tenant,
            'customer' => (object) [
                'name' => 'صيدلية النور الطبية',
                'phone' => '+964 770 123 4567',
                'email' => 'alnoor.pharmacy@gmail.com',
                'address' => 'بغداد - الكرخ - شارع الأطباء - مجمع النور الطبي',
                'customer_number' => 'CUST-2025-001'
            ],
            'items' => collect([
                (object) [
                    'product' => (object) [
                        'name' => 'أموكسيسيلين 500 مجم',
                        'description' => 'مضاد حيوي واسع المجال - كبسولات',
                        'code' => 'ANT-001'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 15,
                    'unit_price' => 8500,
                    'discount_amount' => 5000,
                    'total_amount' => 122500
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'أوميجا 3 بلس فيتامين د',
                        'description' => 'مكمل غذائي متطور - 60 كبسولة',
                        'code' => 'SUP-015'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 8,
                    'unit_price' => 15000,
                    'discount_amount' => 8000,
                    'total_amount' => 112000
                ],
                (object) [
                    'product' => (object) [
                        'name' => 'فيتامين ب المركب',
                        'description' => 'مجموعة فيتامينات ب الكاملة',
                        'code' => 'VIT-008'
                    ],
                    'unit' => 'عبوة',
                    'quantity' => 10,
                    'unit_price' => 4200,
                    'discount_amount' => 2000,
                    'total_amount' => 40000
                ]
            ])
        ];

        // Use the advanced template with QR Code
        $html = view('sales.invoices.mpdf', compact('invoice'))->render();
        $mpdf = \App\Helpers\MPdfHelper::createFromHtml($html);

        return \App\Helpers\MPdfHelper::streamPdf($mpdf, 'invoice-with-qr-' . $invoice->invoice_number . '.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Invoice with QR PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('invoice.with.qr.pdf');



// QR Code Test Page
Route::get('/qr-test', function () {
    try {
        // Create sample invoice
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 150000,
            'previous_balance' => 75000,
            'paid_amount' => 0,
            'payment_status' => 'غير مدفوع',
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543',
                'address' => 'بغداد - الكرخ - حي الجامعة'
            ],
            'items' => collect([
                (object) ['product' => (object) ['name' => 'باراسيتامول'], 'quantity' => 10],
                (object) ['product' => (object) ['name' => 'فيتامين د3'], 'quantity' => 5],
                (object) ['product' => (object) ['name' => 'أوميجا 3'], 'quantity' => 3]
            ])
        ];

        // Generate multiple QR codes
        $qrCodes = \App\Helpers\InvoiceQrHelper::generateMultipleQrs($invoice);

        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>اختبار QR Code</title>
            <style>
                body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; }
                .qr-container { display: flex; flex-wrap: wrap; gap: 20px; }
                .qr-item { border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; }
                .qr-item img { width: 150px; height: 150px; }
                .qr-title { font-weight: bold; margin-bottom: 10px; color: #333; }
                .qr-description { font-size: 12px; color: #666; margin-top: 10px; }
            </style>
        </head>
        <body>
            <h1>اختبار QR Code للفاتورة رقم: ' . $invoice->invoice_number . '</h1>

            <div class="qr-container">
                <div class="qr-item">
                    <div class="qr-title">تفاصيل الفاتورة</div>
                    <img src="data:image/png;base64,' . $qrCodes['invoice_details'] . '" alt="QR Code">
                    <div class="qr-description">يحتوي على جميع تفاصيل الفاتورة والمديونية</div>
                </div>

                <div class="qr-item">
                    <div class="qr-title">رابط التحقق</div>
                    <img src="data:image/png;base64,' . $qrCodes['verification_url'] . '" alt="QR Code">
                    <div class="qr-description">رابط للتحقق من صحة الفاتورة</div>
                </div>

                <div class="qr-item">
                    <div class="qr-title">معلومات الدفع</div>
                    <img src="data:image/png;base64,' . $qrCodes['payment_info'] . '" alt="QR Code">
                    <div class="qr-description">معلومات الدفع للتطبيقات المصرفية</div>
                </div>

                <div class="qr-item">
                    <div class="qr-title">معلومات الاتصال</div>
                    <img src="data:image/png;base64,' . $qrCodes['contact_info'] . '" alt="QR Code">
                    <div class="qr-description">معلومات الاتصال بالشركة</div>
                </div>
            </div>

            <h2>اختبار التحقق:</h2>
            <p>رابط التحقق: <a href="/verify-invoice/' . $invoice->invoice_number . '" target="_blank">/verify-invoice/' . $invoice->invoice_number . '</a></p>

            <h2>معلومات الفاتورة:</h2>
            <ul>
                <li>رقم الفاتورة: ' . $invoice->invoice_number . '</li>
                <li>العميل: ' . $invoice->customer->name . '</li>
                <li>المبلغ الإجمالي: ' . number_format($invoice->total_amount, 0) . ' د.ع</li>
                <li>المديونية السابقة: ' . number_format($invoice->previous_balance, 0) . ' د.ع</li>
                <li>المديونية الحالية: ' . number_format($invoice->previous_balance + $invoice->total_amount - $invoice->paid_amount, 0) . ' د.ع</li>
            </ul>
        </body>
        </html>';

        return response($html);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'QR test failed',
            'message' => $e->getMessage()
        ], 500);
    }
})->name('qr.test');

// QR Code Display Test
Route::get('/qr-display-test', function () {
    try {
        // Create sample invoice
        $invoice = (object) [
            'invoice_number' => 'INV-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'created_at' => now(),
            'total_amount' => 150000,
            'previous_balance' => 75000,
            'paid_amount' => 0,
            'customer' => (object) [
                'name' => 'صيدلية الشفاء',
                'phone' => '+964 770 987 6543'
            ]
        ];

        // Generate QR Code
        $qrCode = \App\Helpers\InvoiceQrHelper::generateInvoiceQr($invoice);
        $company = TenantHelper::getCompanyInfo();

        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>اختبار QR Code</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
            </style>
        </head>
        <body class="bg-gray-100 min-h-screen">
            <div class="container mx-auto px-4 py-8">
                <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
                    <h1 class="text-2xl font-bold text-center mb-6 text-blue-800">اختبار QR Code للفاتورة</h1>

                    <div class="text-center mb-6">
                        <div class="inline-block p-4 bg-gray-50 rounded-lg border-2 border-blue-300">
                            <img src="data:image/svg+xml;base64,' . $qrCode . '"
                                 alt="QR Code للفاتورة"
                                 style="width: 200px; height: 200px; display: block; margin: 0 auto;">
                            <p class="text-sm text-gray-600 mt-2">QR Code للفاتورة رقم: ' . $invoice->invoice_number . '</p>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h3 class="font-bold text-blue-800 mb-2">معلومات الفاتورة:</h3>
                        <ul class="text-sm space-y-1">
                            <li><strong>رقم الفاتورة:</strong> ' . $invoice->invoice_number . '</li>
                            <li><strong>اسم الشركة:</strong> ' . $company['name'] . '</li>
                            <li><strong>العميل:</strong> ' . $invoice->customer->name . '</li>
                            <li><strong>المبلغ:</strong> ' . number_format($invoice->total_amount, 0) . ' د.ع</li>
                            <li><strong>المديونية السابقة:</strong> ' . number_format($invoice->previous_balance, 0) . ' د.ع</li>
                            <li><strong>التاريخ:</strong> ' . $invoice->created_at->format('Y-m-d H:i') . '</li>
                        </ul>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-bold text-green-800 mb-2">كيفية الاستخدام:</h3>
                        <ol class="text-sm space-y-1 list-decimal list-inside">
                            <li>استخدم تطبيق مسح QR Code على هاتفك</li>
                            <li>وجه الكاميرا نحو الكود أعلاه</li>
                            <li>ستظهر معلومات الفاتورة كاملة</li>
                            <li>يمكنك التحقق من صحة الفاتورة</li>
                        </ol>
                    </div>

                    <div class="text-center mt-6">
                        <a href="/test-pdf-page" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            العودة للاختبارات
                        </a>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        return response($html);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'QR display test failed',
            'message' => $e->getMessage()
        ], 500);
    }
})->name('qr.display.test');

// Hover Effects Test Page
Route::get('/test-hover-effects', function () {
    $html = '
    <!DOCTYPE html>
    <html dir="rtl" lang="ar">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>اختبار تأثيرات Hover</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="' . asset('css/hover-effects.css') . '">
        <style>
            body { font-family: "Cairo", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        </style>
    </head>
    <body class="bg-gray-100 min-h-screen">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">اختبار تأثيرات Hover باللون البنفسجي #6f42c1</h1>

                <!-- Sidebar Test -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <div class="bg-white rounded-lg p-6 shadow-lg">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">اختبار Sidebar</h2>
                        <div class="space-y-2">
                            <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                                <i class="fas fa-home ml-3"></i>
                                <span>الرئيسية</span>
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                                <i class="fas fa-chart-bar ml-3"></i>
                                <span>التقارير</span>
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">
                                <i class="fas fa-users ml-3"></i>
                                <span>العملاء</span>
                            </a>
                        </div>
                    </div>

                    <!-- Cards Test -->
                    <div class="bg-white rounded-lg p-6 shadow-lg">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">اختبار البطاقات</h2>
                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded border">
                                <h3 class="font-semibold text-gray-800">بطاقة اختبار</h3>
                                <p class="text-gray-600">مرر الماوس هنا لرؤية التأثير</p>
                                <i class="fas fa-star text-yellow-500"></i>
                            </div>
                            <div class="bg-green-50 p-4 rounded border">
                                <h3 class="font-semibold text-gray-800">بطاقة أخرى</h3>
                                <p class="text-gray-600">تأثير hover مختلف</p>
                                <i class="fas fa-heart text-red-500"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons Test -->
                    <div class="bg-white rounded-lg p-6 shadow-lg">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">اختبار الأزرار</h2>
                        <div class="space-y-3">
                            <button class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                زر أزرق
                            </button>
                            <button class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                زر أخضر
                            </button>
                            <button class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                زر أحمر
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Test -->
                <div class="bg-white rounded-lg p-6 shadow-lg mb-8">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">اختبار الجداول</h2>
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-right py-2">الاسم</th>
                                <th class="text-right py-2">البريد الإلكتروني</th>
                                <th class="text-right py-2">الهاتف</th>
                                <th class="text-right py-2">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2">أحمد محمد</td>
                                <td class="py-2">ahmed@example.com</td>
                                <td class="py-2">+964 770 123 4567</td>
                                <td class="py-2"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">نشط</span></td>
                            </tr>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2">فاطمة علي</td>
                                <td class="py-2">fatima@example.com</td>
                                <td class="py-2">+964 771 234 5678</td>
                                <td class="py-2"><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">معلق</span></td>
                            </tr>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2">محمد حسن</td>
                                <td class="py-2">mohammed@example.com</td>
                                <td class="py-2">+964 772 345 6789</td>
                                <td class="py-2"><span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">غير نشط</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Form Test -->
                <div class="bg-white rounded-lg p-6 shadow-lg mb-8">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">اختبار النماذج</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">الاسم</label>
                            <input type="text" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="أدخل الاسم">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">البريد الإلكتروني</label>
                            <input type="email" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="أدخل البريد الإلكتروني">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">النوع</label>
                            <select class="w-full border border-gray-300 rounded px-3 py-2">
                                <option>اختر النوع</option>
                                <option>عميل</option>
                                <option>مورد</option>
                                <option>موظف</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">ملاحظات</label>
                            <textarea class="w-full border border-gray-300 rounded px-3 py-2" rows="3" placeholder="أدخل الملاحظات"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Icons Test -->
                <div class="bg-white rounded-lg p-6 shadow-lg mb-8">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">اختبار الأيقونات</h2>
                    <div class="grid grid-cols-6 md:grid-cols-12 gap-4 text-center">
                        <div class="p-3">
                            <i class="fas fa-home text-2xl text-gray-600"></i>
                            <p class="text-xs mt-1">الرئيسية</p>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-chart-bar text-2xl text-gray-600"></i>
                            <p class="text-xs mt-1">التقارير</p>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-users text-2xl text-gray-600"></i>
                            <p class="text-xs mt-1">العملاء</p>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-box text-2xl text-gray-600"></i>
                            <p class="text-xs mt-1">المنتجات</p>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-file-invoice text-2xl text-gray-600"></i>
                            <p class="text-xs mt-1">الفواتير</p>
                        </div>
                        <div class="p-3">
                            <i class="fas fa-cog text-2xl text-gray-600"></i>
                            <p class="text-xs mt-1">الإعدادات</p>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="text-center">
                    <a href="/dashboard" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition duration-200">
                        العودة للوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>';

    return response($html);
})->name('test.hover.effects');

// PDF Libraries Comparison Page
Route::get('/pdf-comparison', function () {
    return view('pdf-comparison');
})->name('pdf.comparison');

// Simple PDF Stream (display in browser)
Route::get('/stream-pdf', function () {
    try {
        $html = '
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>اختبار PDF</title>
            <style>
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    direction: rtl;
                    text-align: right;
                    padding: 20px;
                }
                .header {
                    background: #3498db;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>نظام MaxCon ERP</h1>
                <p>اختبار PDF مع عرض في المتصفح</p>
            </div>
            <h2>مرحباً بك</h2>
            <p>هذا اختبار لعرض PDF مباشرة في المتصفح.</p>
            <p>التاريخ: ' . now()->format('Y-m-d H:i:s') . '</p>
            <ul>
                <li>العنصر الأول</li>
                <li>العنصر الثاني</li>
                <li>العنصر الثالث</li>
            </ul>
        </body>
        </html>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                  ]);

        // Stream PDF to browser (display inline)
        return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="test.pdf"');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Stream PDF failed',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('stream.pdf');

// Debug PDF
Route::get('/debug-pdf', function () {
    try {
        // Check if PDF facade is available
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            return response()->json(['error' => 'PDF Facade not found']);
        }

        // Simple HTML test
        $html = '<h1>Test PDF</h1><p>This is a test.</p>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);

        return $pdf->download('debug-test.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Debug PDF failed',
            'message' => $e->getMessage(),
            'class_exists' => class_exists('\Barryvdh\DomPDF\Facade\Pdf'),
            'dompdf_exists' => class_exists('\Dompdf\Dompdf')
        ], 500);
    }
})->name('debug.pdf');

// API Routes for Testing
Route::get('/api/customers/search', function (Request $request) {
    $query = $request->get('q', '');
    $filters = $request->get('filters', []);
    $page = $request->get('page', 1);
    $perPage = $request->get('per_page', 10);

    // Sample customers data
    $customers = [
        ['id' => 1, 'text' => 'أحمد محمد علي', 'type' => 'individual', 'status' => 'active'],
        ['id' => 2, 'text' => 'شركة الأدوية المتحدة', 'type' => 'company', 'status' => 'active'],
        ['id' => 3, 'text' => 'مستشفى بغداد التخصصي', 'type' => 'hospital', 'status' => 'active'],
        ['id' => 4, 'text' => 'صيدلية النور', 'type' => 'pharmacy', 'status' => 'active'],
        ['id' => 5, 'text' => 'فاطمة حسن محمود', 'type' => 'individual', 'status' => 'inactive'],
        ['id' => 6, 'text' => 'شركة الصحة الذهبية', 'type' => 'company', 'status' => 'pending'],
        ['id' => 7, 'text' => 'مستشفى الكندي', 'type' => 'hospital', 'status' => 'active'],
        ['id' => 8, 'text' => 'صيدلية الشفاء', 'type' => 'pharmacy', 'status' => 'active'],
    ];

    // Apply search filter
    if ($query) {
        $customers = array_filter($customers, function($customer) use ($query) {
            return stripos($customer['text'], $query) !== false;
        });
    }

    // Apply additional filters
    foreach ($filters as $key => $value) {
        if ($value) {
            $customers = array_filter($customers, function($customer) use ($key, $value) {
                return isset($customer[$key]) && $customer[$key] === $value;
            });
        }
    }

    // Pagination
    $total = count($customers);
    $customers = array_slice($customers, ($page - 1) * $perPage, $perPage);

    return response()->json([
        'results' => array_values($customers),
        'total' => $total,
        'pagination' => [
            'more' => ($page * $perPage) < $total
        ]
    ]);
});

// Authentication Routes
Route::get('/login', function() {
    return view('auth.simple-login');
})->name('login');

Route::get('/test-login', function() {
    return view('auth.test-login');
});

// Simple login routes
Route::get('/simple-login', function() {
    return view('auth.simple-login');
});

Route::post('/simple-login', function(Request $request) {
    // Find user
    $user = \App\Models\User::where('email', $request->email)->first();

    // Check credentials
    if (!$user || !Hash::check($request->password, (string) $user->password)) {
        return back()->with('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }

    // Check if user is active
    if (!$user->is_active) {
        return back()->with('error', 'حسابك غير مفعل. يرجى التواصل مع الإدارة');
    }

    // Login user
    Auth::login($user, $request->boolean('remember'));

    // Redirect to dashboard
    return redirect('/dashboard')->with('success', 'تم تسجيل الدخول بنجاح');
});
Route::post('/login', function(Request $request) {
    // Find user
    $user = \App\Models\User::where('email', $request->email)->first();

    // Check credentials
    if (!$user || !Hash::check($request->password, (string) $user->password)) {
        return back()->with('error', 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }

    // Check if user is active
    if (!$user->is_active) {
        return back()->with('error', 'حسابك غير مفعل. يرجى التواصل مع الإدارة');
    }

    // Login user
    Auth::login($user, $request->boolean('remember'));

    // Redirect to dashboard
    return redirect('/dashboard')->with('success', 'تم تسجيل الدخول بنجاح');
});

// Excel Template Page
Route::get('/excel-template', function() {
    return view('excel-template');
})->name('excel.template.page');

// Excel Template Download
Route::get('/download-excel-template', [App\Http\Controllers\ExcelTemplateController::class, 'generateImportTemplate'])
    ->name('excel.template.download');

// Customer Import Routes
Route::get('/sales/customers/import', [App\Http\Controllers\Sales\CustomerImportController::class, 'showImportForm'])
    ->name('sales.customers.import');
Route::get('/sales/customers/import/template', [App\Http\Controllers\Sales\CustomerImportController::class, 'downloadTemplate'])
    ->name('sales.customers.import.template');
Route::post('/sales/customers/import', [App\Http\Controllers\Sales\CustomerImportController::class, 'import'])
    ->name('sales.customers.import.process');

// Test Master Admin Dashboard
Route::get('/test-master-admin', function() {
    return view('master-admin.dashboard', [
        'stats' => [
            'total_tenants' => 5,
            'active_tenants' => 4,
            'expired_tenants' => 1,
            'pending_tenants' => 2,
            'total_users' => 25,
            'active_users' => 20,
            'monthly_revenue' => 2500000,
            'total_revenue' => 30000000,
            'system_uptime' => '99.9%',
            'storage_usage' => '45%',
            'growth_rate' => 15.5,
            'churn_rate' => 2.1,
        ],
        'recentActivity' => [
            [
                'title' => 'مستأجر جديد',
                'description' => 'تم إضافة صيدلية الشفاء',
                'time' => now()->subMinutes(30),
                'icon' => 'fas fa-building',
                'color' => 'green'
            ],
            [
                'title' => 'مستخدم جديد',
                'description' => 'انضم أحمد محمد للنظام',
                'time' => now()->subHour(),
                'icon' => 'fas fa-user',
                'color' => 'blue'
            ]
        ],
        'systemHealth' => [
            'cpu_usage' => 35,
            'memory_usage' => 55,
            'disk_usage' => 40,
            'active_sessions' => 12,
            'response_time' => 150,
        ],
        'revenueData' => [],
        'tenantGrowth' => [],
        'alerts' => [
            [
                'type' => 'info',
                'title' => 'تحديث النظام',
                'message' => 'يتوفر تحديث جديد للنظام',
                'action' => 'تحديث الآن',
                'url' => '#'
            ]
        ]
    ]);
});

// Debug user role
Route::get('/debug-user-role', function() {
    if (!Auth::check()) {
        return response()->json(['error' => 'Not authenticated']);
    }

    $user = Auth::user();
    return response()->json([
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'is_super_admin' => $user->is_super_admin,
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'has_super_admin_role' => $user->hasRole('super_admin'),
    ]);
});

// Debug login routes
Route::get('/debug-login', function(Request $request) {
    // Handle test queries
    if ($request->has('test')) {
        $test = $request->get('test');

        if ($test === 'user') {
            $user = \App\Models\User::where('email', 'admin@maxcon-erp.com')->first();
            if ($user) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User found',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_active' => $user->is_active,
                        'is_super_admin' => $user->is_super_admin,
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }
        }

        if ($test === 'password') {
            $user = \App\Models\User::where('email', 'admin@maxcon-erp.com')->first();
            $passwordCheck = $user ? Hash::check('MaxCon@2025', (string) $user->password) : false;
            return response()->json([
                'status' => $passwordCheck ? 'success' : 'error',
                'message' => $passwordCheck ? 'Password is correct' : 'Password is incorrect',
                'password_check' => $passwordCheck
            ]);
        }

        if ($test === 'session') {
            return response()->json([
                'status' => 'success',
                'session_id' => session()->getId(),
                'csrf_token' => csrf_token(),
                'auth_check' => Auth::check(),
                'current_user' => Auth::user()
            ]);
        }
    }

    return view('debug-login');
});

Route::post('/debug-login', function(Request $request) {
    Log::info('Debug login attempt', [
        'email' => $request->email,
        'has_password' => !empty($request->password),
        'csrf_token' => $request->_token,
        'session_id' => session()->getId()
    ]);

    // Find user
    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        Log::error('User not found', ['email' => $request->email]);
        return back()->with('error', 'المستخدم غير موجود');
    }

    // Check password
    if (!Hash::check($request->password, (string) $user->password)) {
        Log::error('Password incorrect', ['email' => $request->email]);
        return back()->with('error', 'كلمة المرور غير صحيحة');
    }

    // Check if user is active
    if (!$user->is_active) {
        Log::error('User inactive', ['email' => $request->email]);
        return back()->with('error', 'الحساب غير مفعل');
    }

    // Login user
    Auth::login($user, $request->boolean('remember'));

    Log::info('User logged in successfully', [
        'user_id' => $user->id,
        'email' => $user->email
    ]);

    return redirect('/debug-login')->with('success', 'تم تسجيل الدخول بنجاح!');
});

// CSRF test route
Route::get('/csrf-test', function() {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_started' => session()->isStarted(),
        'session_data' => session()->all()
    ]);
});

// Direct login without CSRF (for testing)
Route::get('/direct-login', function() {
    $user = \App\Models\User::where('email', 'admin@maxcon-erp.com')->first();

    if ($user && $user->is_active) {
        Auth::login($user);
        return redirect('/dashboard')->with('success', 'تم تسجيل الدخول مباشرة');
    }

    return redirect('/debug-login')->with('error', 'فشل في تسجيل الدخول المباشر');
});

// Test login route
Route::post('/test-login', function(Request $request) {
    $user = \App\Models\User::where('email', $request->email)->first();
    if ($user && Hash::check($request->password, (string) $user->password)) {
        Auth::login($user);
        return redirect('/dashboard');
    }
    return back()->withErrors(['email' => 'Invalid credentials']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Two Factor Authentication Routes
Route::middleware(['auth'])->prefix('auth/two-factor')->name('two-factor.')->group(function () {
    Route::get('/', [TwoFactorController::class, 'show'])->name('show');
    Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
    Route::post('/regenerate-recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate-recovery-codes');
    Route::post('/send-code', [TwoFactorController::class, 'sendCode'])->name('send-code');
});

// Two Factor Challenge Routes (Guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.verify');
});

// Public Routes
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Protected Routes (Tenant Users Only)
Route::middleware(['auth'])->group(function () {

    // Dashboard (Tenant Users Only - Super Admin redirected by middleware)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Test Searchable Dropdowns
    Route::get('/test-searchable', function () {
        return view('test-searchable');
    })->name('test.searchable');

    // Test Advanced Searchable Dropdowns
    Route::get('/test-advanced-searchable', function () {
        return view('test-advanced-searchable');
    })->name('test.advanced.searchable');

    // Sales Module Routes
    Route::prefix('sales')->name('sales.')->group(function () {

        // Sales Dashboard
        Route::get('/', [SalesController::class, 'index'])->name('index');
        Route::get('/dashboard', [SalesController::class, 'dashboard'])->name('dashboard');

        // Customers
        Route::resource('customers', CustomerController::class);
        Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
        Route::get('/customers/{customer}/invoices', [CustomerController::class, 'invoices'])->name('customers.invoices');
        Route::get('/customers/{customer}/payments', [CustomerController::class, 'payments'])->name('customers.payments');

        // Sales Representatives Management
        Route::prefix('representatives')->name('sales-reps.')->group(function () {
            Route::get('/', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'index'])->name('index');
            Route::get('/create', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'create'])->name('create');
            Route::post('/', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'store'])->name('store');
            Route::get('/{salesRep}', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'show'])->name('show');
            Route::get('/{salesRep}/edit', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'edit'])->name('edit');
            Route::put('/{salesRep}', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'update'])->name('update');
            Route::delete('/{salesRep}', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'destroy'])->name('destroy');
            Route::get('/{salesRep}/performance', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'performance'])->name('performance');
            Route::get('/{salesRep}/location', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'location'])->name('location');
            Route::post('/bulk-action', [\App\Modules\SalesReps\Http\Controllers\SalesRepController::class, 'bulkAction'])->name('bulk-action');
        });

        // Sales Orders
        Route::resource('orders', SalesOrderController::class);
        Route::post('/orders/{order}/confirm', [SalesOrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('/orders/{order}/cancel', [SalesOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/orders/{order}/invoice', [SalesOrderController::class, 'createInvoice'])->name('orders.invoice');
        Route::get('/orders/{order}/print', [SalesOrderController::class, 'print'])->name('orders.print');

        // Invoices
        Route::get('/invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
        Route::resource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'markAsPaid'])->name('invoices.pay');
        Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

        // Payments
        Route::resource('payments', PaymentController::class);
        Route::get('/payments/create/{invoice}', [PaymentController::class, 'createForInvoice'])->name('payments.create.invoice');
        Route::get('/payments/{payment}/print', [PaymentController::class, 'printReceipt'])->name('payments.print');
        Route::get('/payments/{payment}/pdf', [PaymentController::class, 'downloadPdf'])->name('payments.pdf');
        Route::get('/payments/{payment}/whatsapp-message', [PaymentController::class, 'generateWhatsAppMessage'])->name('payments.whatsapp');

    });

    // Admin Routes (Users, Roles, Permissions)
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin|tenant-admin|super_admin', \App\Http\Middleware\EnsureTenantContext::class])->group(function () {
        // Users Management (Tenant Users)
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Roles Management
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::get('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'permissions'])->name('roles.permissions');
        Route::put('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

        // Permissions Management
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
        Route::post('/permissions/assign-to-role', [\App\Http\Controllers\Admin\PermissionController::class, 'assignToRole'])->name('permissions.assign');
        Route::get('/permissions/by-group', [\App\Http\Controllers\Admin\PermissionController::class, 'getByGroup'])->name('permissions.by-group');
        Route::post('/permissions/bulk-assign', [\App\Http\Controllers\Admin\PermissionController::class, 'bulkAssign'])->name('permissions.bulk-assign');

        // Settings Management
        Route::resource('settings', \App\Http\Controllers\Admin\SettingsController::class);
        Route::post('/settings/update-general', [\App\Http\Controllers\Admin\SettingsController::class, 'updateGeneral'])->name('settings.update-general');
        Route::post('/settings/update-security', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSecurity'])->name('settings.update-security');
    });

    // Inventory Module Routes
    Route::prefix('inventory')->name('inventory.')->group(function () {

        // Inventory Dashboard
        Route::get('/', [InventoryController::class, 'index'])->name('index');

        // Warehouses - Multi-warehouse management
        Route::prefix('warehouses')->name('warehouses.')->group(function () {
            Route::get('/', [WarehouseController::class, 'index'])->name('index');
            Route::get('/create', [WarehouseController::class, 'create'])->name('create');
            Route::post('/', [WarehouseController::class, 'store'])->name('store');
            Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
            Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
            Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
            Route::get('/{warehouse}/inventory', [WarehouseController::class, 'inventory'])->name('inventory');
            Route::get('/{warehouse}/movements', [WarehouseController::class, 'movements'])->name('movements');
        });

        // Inventory Management
        Route::get('/by-warehouse', [InventoryController::class, 'byWarehouse'])->name('by-warehouse');
        Route::get('/by-product', [InventoryController::class, 'byProduct'])->name('by-product');
        Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
        Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/expiring', [InventoryController::class, 'expiring'])->name('expiring');
        Route::post('/adjust', [InventoryController::class, 'adjust'])->name('adjust');

        // Products - Special routes first (before resource routes)
        Route::get('/products/export', [ProductController::class, 'exportProducts'])->name('products.export');
        Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
        Route::get('/products/expiring', [ProductController::class, 'expiring'])->name('products.expiring');
        Route::get('/products/template', [\App\Http\Controllers\ProductImportController::class, 'downloadTemplate'])->name('products.template');
        Route::post('/products/import', [\App\Http\Controllers\ProductImportController::class, 'importProducts'])->name('products.import');

        // Products Resource Routes
        Route::resource('products', ProductController::class);
        Route::get('/products/{product}/stock', [ProductController::class, 'stock'])->name('products.stock');
        Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');

    });

    // Reports Module Routes
    Route::prefix('reports')->name('reports.')->group(function () {

        // Reports Dashboard
        Route::get('/', [ReportsController::class, 'index'])->name('index');

        // Sales Reports
        Route::get('/sales', [ReportsController::class, 'sales'])->name('sales');
        Route::get('/sales/summary', [ReportsController::class, 'salesSummary'])->name('sales.summary');
        Route::get('/sales/detailed', [ReportsController::class, 'salesDetailed'])->name('sales.detailed');
        Route::get('/sales/customers', [ReportsController::class, 'customerSales'])->name('sales.customers');
        Route::get('/sales/products', [ReportsController::class, 'productSales'])->name('sales.products');

        // Inventory Reports
        Route::get('/inventory', [ReportsController::class, 'inventory'])->name('inventory');
        Route::get('/inventory/stock-levels', [ReportsController::class, 'stockLevels'])->name('inventory.stock-levels');
        Route::get('/inventory/movements', [ReportsController::class, 'stockMovements'])->name('inventory.movements');
        Route::get('/inventory/valuation', [ReportsController::class, 'stockValuation'])->name('inventory.valuation');
        Route::get('/inventory/expiring', [ReportsController::class, 'expiringProducts'])->name('inventory.expiring');

        // Financial Reports
        Route::prefix('financial')->name('financial.')->group(function () {
            Route::get('/', [App\Modules\Reports\Controllers\FinancialReportsController::class, 'index'])->name('index');
            Route::get('/profit-loss', [App\Modules\Reports\Controllers\FinancialReportsController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/balance-sheet', [App\Modules\Reports\Controllers\FinancialReportsController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/cash-flow', [App\Modules\Reports\Controllers\FinancialReportsController::class, 'cashFlow'])->name('cash-flow');
            Route::get('/ratios', [App\Modules\Reports\Controllers\FinancialReportsController::class, 'ratios'])->name('ratios');
            Route::get('/budget-vs-actual', [App\Modules\Reports\Controllers\FinancialReportsController::class, 'budgetVsActual'])->name('budget-vs-actual');
        });

    });

    // Suppliers Module Routes
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        // Special routes first
        Route::get('/export', [App\Modules\Suppliers\Controllers\SupplierController::class, 'exportSuppliers'])->name('export');
        Route::get('/template', [\App\Http\Controllers\SupplierImportController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [\App\Http\Controllers\SupplierImportController::class, 'importSuppliers'])->name('import');

        // Resource routes
        Route::get('/', [App\Modules\Suppliers\Controllers\SupplierController::class, 'index'])->name('index');
        Route::get('/create', [App\Modules\Suppliers\Controllers\SupplierController::class, 'create'])->name('create');
        Route::post('/', [App\Modules\Suppliers\Controllers\SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}', [App\Modules\Suppliers\Controllers\SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [App\Modules\Suppliers\Controllers\SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [App\Modules\Suppliers\Controllers\SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [App\Modules\Suppliers\Controllers\SupplierController::class, 'destroy'])->name('destroy');
    });

    // Purchase Orders Routes (separate from suppliers prefix)
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{purchaseOrder}', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'show'])->name('show');
        Route::get('/{purchaseOrder}/print', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'print'])->name('print');
        Route::get('/{purchaseOrder}/edit', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'edit'])->name('edit');
        Route::put('/{purchaseOrder}', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'update'])->name('update');
        Route::delete('/{purchaseOrder}', [App\Modules\Suppliers\Controllers\PurchaseOrderController::class, 'destroy'])->name('destroy');
    });

    // Accounting Module Routes
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', [App\Modules\Accounting\Controllers\AccountController::class, 'index'])->name('dashboard');
        Route::resource('accounts', App\Modules\Accounting\Controllers\AccountController::class);
        Route::resource('transactions', App\Modules\Accounting\Controllers\TransactionController::class);
        Route::get('/chart-of-accounts', [App\Modules\Accounting\Controllers\AccountController::class, 'index'])->name('chart-of-accounts.index');
        Route::get('/journal-entries', [App\Modules\Accounting\Controllers\TransactionController::class, 'index'])->name('journal-entries.index');
        Route::get('/financial-reports', [App\Modules\Accounting\Controllers\ReportsController::class, 'index'])->name('financial-reports.index');
        Route::get('/reports/trial-balance', [App\Modules\Accounting\Controllers\ReportsController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('/reports/income-statement', [App\Modules\Accounting\Controllers\ReportsController::class, 'incomeStatement'])->name('reports.income-statement');
        Route::get('/reports/balance-sheet', [App\Modules\Accounting\Controllers\ReportsController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('/accounts/{account}/report', [App\Modules\Accounting\Controllers\AccountController::class, 'accountReport'])->name('accounts.report');
        Route::get('/accounts/{account}/transactions', [App\Modules\Accounting\Controllers\AccountController::class, 'accountTransactions'])->name('accounts.transactions');
    });

    // HR Module Routes
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/', function() {
            // HR Dashboard with statistics
            $stats = [
                'total_employees' => \App\Modules\HR\Models\Employee::count(),
                'active_employees' => \App\Modules\HR\Models\Employee::active()->count(),
                'total_departments' => \App\Modules\HR\Models\Department::count(),
                'attendance_rate' => 85.5, // Placeholder
            ];

            $todayStats = [
                'present' => \App\Modules\HR\Models\Attendance::whereDate('date', today())->where('status', 'present')->count(),
                'late' => \App\Modules\HR\Models\Attendance::whereDate('date', today())->where('status', 'late')->count(),
                'absent' => \App\Modules\HR\Models\Attendance::whereDate('date', today())->where('status', 'absent')->count(),
            ];

            $recentEmployees = \App\Modules\HR\Models\Employee::with('department')
                                ->latest()
                                ->take(5)
                                ->get();

            return view('hr.dashboard', compact('stats', 'todayStats', 'recentEmployees'));
        })->name('dashboard');
        Route::resource('employees', App\Modules\HR\Controllers\EmployeeController::class);
        Route::get('/employees/template/download', [App\Modules\HR\Controllers\EmployeeController::class, 'downloadTemplate'])->name('employees.template');
        Route::post('/employees/import', [App\Modules\HR\Controllers\EmployeeController::class, 'import'])->name('employees.import');
        Route::resource('departments', App\Modules\HR\Controllers\DepartmentController::class);
        Route::post('/departments/{department}/toggle-status', [App\Modules\HR\Controllers\DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');
        Route::resource('attendance', App\Modules\HR\Controllers\AttendanceController::class);
        Route::resource('payroll', App\Modules\HR\Controllers\PayrollController::class);
        Route::post('/payroll/preview', [App\Modules\HR\Controllers\PayrollController::class, 'previewBulkPayroll'])->name('payroll.preview');
        Route::post('/payroll/bulk-generate', [App\Modules\HR\Controllers\PayrollController::class, 'bulkGenerate'])->name('payroll.bulk-generate');
    });

    // Medical Reps Module Routes
    Route::prefix('medical-reps')->name('medical-reps.')->group(function () {
        Route::get('/', [App\Modules\MedicalReps\Controllers\MedicalRepController::class, 'index'])->name('index');
        Route::get('/territories', [App\Modules\MedicalReps\Controllers\MedicalRepController::class, 'territories'])->name('territories.index');
        Route::get('/visits', [App\Modules\MedicalReps\Controllers\MedicalRepController::class, 'visits'])->name('visits.index');
        Route::get('/commissions', [App\Modules\MedicalReps\Controllers\MedicalRepController::class, 'commissions'])->name('commissions.index');
    });

    // Analytics Module Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [App\Modules\Analytics\Controllers\AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/sales-prediction', [App\Modules\Analytics\Controllers\AnalyticsController::class, 'salesPrediction'])->name('sales-prediction');
        Route::get('/business-intelligence', [App\Modules\Analytics\Controllers\AnalyticsController::class, 'businessIntelligence'])->name('business-intelligence');
    });

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');

    // User Preferences
    Route::get('/preferences', [App\Http\Controllers\UserPreferenceController::class, 'index'])->name('preferences.index');

    // Admin Routes
    Route::middleware(['role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/security', [SecurityController::class, 'index'])->name('security.index');

        // User management
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        Route::post('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Role management
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);

        // Settings management
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/system-info', [App\Http\Controllers\Admin\SettingsController::class, 'systemInfo'])->name('settings.system-info');
        Route::post('/settings/clear-cache', [App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/settings/create-backup', [App\Http\Controllers\Admin\SettingsController::class, 'createBackup'])->name('settings.create-backup');
    });

    // Master Admin Routes (Completely Separate SaaS Management)
    Route::middleware(['auth', 'role:super-admin|super_admin'])->prefix('master-admin')->name('master-admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\MasterAdmin\DashboardController::class, 'index'])->name('dashboard');

        // Tenant Management
        Route::resource('tenants', App\Http\Controllers\MasterAdmin\TenantController::class);
        Route::post('/tenants/{tenant}/toggle-status', [App\Http\Controllers\MasterAdmin\TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
        Route::post('/tenants/{tenant}/extend-license', [App\Http\Controllers\MasterAdmin\TenantController::class, 'extendLicense'])->name('tenants.extend-license');
        Route::get('/tenants-pending', [App\Http\Controllers\MasterAdmin\TenantController::class, 'pending'])->name('tenants.pending');
        Route::get('/tenants-expired', [App\Http\Controllers\MasterAdmin\TenantController::class, 'expired'])->name('tenants.expired');

        // System Management
        Route::get('/system/settings', function() { return view('master-admin.system.settings'); })->name('system.settings');
        Route::get('/system/monitoring', function() { return view('master-admin.system.monitoring'); })->name('system.monitoring');
        Route::get('/system/backups', function() { return view('master-admin.system.backups'); })->name('system.backups');
        Route::get('/system/logs', function() { return view('master-admin.system.logs'); })->name('system.logs');

        // Billing & Subscriptions
        Route::get('/billing/plans', function() { return view('master-admin.billing.plans'); })->name('billing.plans');
        Route::get('/billing/invoices', function() { return view('master-admin.billing.invoices'); })->name('billing.invoices');
        Route::get('/billing/payments', function() { return view('master-admin.billing.payments'); })->name('billing.payments');

        // Reports & Analytics
        Route::get('/reports/overview', function() { return view('master-admin.reports.overview'); })->name('reports.overview');
        Route::get('/reports/revenue', function() { return view('master-admin.reports.revenue'); })->name('reports.revenue');
        Route::get('/reports/usage', function() { return view('master-admin.reports.usage'); })->name('reports.usage');
    });

    // Legacy Super Admin Routes (Redirect to master-admin)
    Route::middleware(['role:super-admin|super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', function() {
            return redirect()->route('master-admin.dashboard');
        })->name('dashboard');

        // Redirect all tenant routes to master-admin
        Route::get('/tenants', function() {
            return redirect()->route('master-admin.tenants.index');
        })->name('tenants.index');

        Route::get('/tenants/create', function() {
            return redirect()->route('master-admin.tenants.create');
        })->name('tenants.create');

        Route::get('/tenants/{tenant}', function($tenant) {
            return redirect()->route('master-admin.tenants.show', $tenant);
        })->name('tenants.show');

        Route::get('/tenants/{tenant}/edit', function($tenant) {
            return redirect()->route('master-admin.tenants.edit', $tenant);
        })->name('tenants.edit');

        Route::post('/tenants', function() {
            return redirect()->route('master-admin.tenants.index');
        })->name('tenants.store');

        Route::put('/tenants/{tenant}', function($tenant) {
            return redirect()->route('master-admin.tenants.show', $tenant);
        })->name('tenants.update');

        Route::delete('/tenants/{tenant}', function($tenant) {
            return redirect()->route('master-admin.tenants.index');
        })->name('tenants.destroy');
    });

});
