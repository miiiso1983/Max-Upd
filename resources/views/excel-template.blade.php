<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحميل قالب Excel لاستيراد البيانات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .card-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 2rem;
            text-align: center;
        }
        .feature-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #4CAF50;
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .download-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
            color: white;
        }
        .sheet-info {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
            border: 1px solid #e9ecef;
        }
        .required-field {
            background: #fff3cd;
            color: #856404;
            padding: 0.25rem 0.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            margin: 0.2rem;
            display: inline-block;
        }
        .icon-large {
            font-size: 3rem;
            color: #4CAF50;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-file-excel icon-large"></i>
                        <h1 class="mb-0">قالب Excel لاستيراد البيانات</h1>
                        <p class="mb-0 mt-2">نظام ERP صيدلاني - MaxCon</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- Description -->
                        <div class="text-center mb-4">
                            <h3 class="text-primary">📥 تحميل قالب استيراد البيانات</h3>
                            <p class="text-muted">ملف Excel شامل يحتوي على 4 أوراق منفصلة لاستيراد جميع البيانات الأساسية لنظام ERP الصيدلاني</p>
                        </div>

                        <!-- Features -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <h5><i class="fas fa-users text-primary"></i> ورقة العملاء</h5>
                                    <p class="mb-2">استيراد بيانات العملاء الكاملة</p>
                                    <div>
                                        <span class="required-field">اسم العميل*</span>
                                        <span class="required-field">نوع العميل*</span>
                                        <span class="required-field">الحالة*</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <h5><i class="fas fa-pills text-success"></i> ورقة المنتجات</h5>
                                    <p class="mb-2">استيراد بيانات الأدوية والمنتجات</p>
                                    <div>
                                        <span class="required-field">اسم المنتج*</span>
                                        <span class="required-field">رمز المنتج*</span>
                                        <span class="required-field">الأسعار*</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <h5><i class="fas fa-user-tie text-info"></i> ورقة المستخدمين</h5>
                                    <p class="mb-2">استيراد بيانات الموظفين والمستخدمين</p>
                                    <div>
                                        <span class="required-field">الاسم*</span>
                                        <span class="required-field">البريد الإلكتروني*</span>
                                        <span class="required-field">الدور*</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <h5><i class="fas fa-building text-warning"></i> ورقة الشركات</h5>
                                    <p class="mb-2">استيراد بيانات الموردين والشركات</p>
                                    <div>
                                        <span class="required-field">اسم الشركة*</span>
                                        <span class="required-field">نوع الشركة*</span>
                                        <span class="required-field">الحالة*</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> تعليمات الاستخدام:</h5>
                            <ul class="mb-0">
                                <li><strong>الحقول الإجبارية:</strong> مميزة باللون الأصفر في الملف</li>
                                <li><strong>القوائم المنسدلة:</strong> تحتوي على خيارات محددة مسبقاً</li>
                                <li><strong>التواريخ:</strong> يجب أن تكون بصيغة YYYY-MM-DD</li>
                                <li><strong>البيانات النموذجية:</strong> موجودة في أول صفين كمثال</li>
                                <li><strong>التحقق التلقائي:</strong> يمنع إدخال بيانات خاطئة</li>
                            </ul>
                        </div>

                        <!-- Sheets Info -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-center mb-3">📋 محتويات الملف</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="sheet-info text-center">
                                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                            <h6>العملاء</h6>
                                            <small>21 عمود</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="sheet-info text-center">
                                            <i class="fas fa-pills fa-2x text-success mb-2"></i>
                                            <h6>المنتجات</h6>
                                            <small>20 عمود</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="sheet-info text-center">
                                            <i class="fas fa-user-tie fa-2x text-info mb-2"></i>
                                            <h6>المستخدمين</h6>
                                            <small>12 عمود</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="sheet-info text-center">
                                            <i class="fas fa-building fa-2x text-warning mb-2"></i>
                                            <h6>الشركات</h6>
                                            <small>30 عمود</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Download Button -->
                        <div class="text-center">
                            <a href="{{ route('excel.template.download') }}" class="btn download-btn">
                                <i class="fas fa-download me-2"></i>
                                تحميل قالب Excel
                            </a>
                            <p class="text-muted mt-3">
                                <small>
                                    <i class="fas fa-file-excel text-success"></i>
                                    حجم الملف: ~50 KB | تاريخ الإنشاء: {{ date('Y/m/d') }}
                                </small>
                            </p>
                        </div>

                        <!-- Additional Info -->
                        <div class="alert alert-success mt-4">
                            <h6><i class="fas fa-lightbulb"></i> نصائح مهمة:</h6>
                            <ul class="mb-0 small">
                                <li>احفظ نسخة احتياطية من بياناتك قبل الاستيراد</li>
                                <li>تأكد من صحة البيانات قبل رفع الملف</li>
                                <li>استخدم البيانات النموذجية كدليل للتنسيق الصحيح</li>
                                <li>لا تحذف أو تعدل أسماء الأعمدة في الصف الأول</li>
                                <li>يمكنك إضافة عدد غير محدود من الصفوف</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
