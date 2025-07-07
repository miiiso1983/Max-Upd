<?php
// تعطيل جميع الأخطاء
error_reporting(0);
ini_set('display_errors', 0);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - نظام إدارة الموارد المؤسسية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .hero-section {
            padding: 100px 0;
            color: white;
            text-align: center;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .status-badge {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin: 20px 0;
        }
        .logo {
            font-size: 4rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="logo">MaxCon SaaS</div>
                    <h2 class="mb-4">نظام إدارة الموارد المؤسسية متعدد المستأجرين</h2>
                    <p class="lead">حل شامل ومتطور لإدارة جميع عمليات الشركة</p>
                    <div class="status-badge">
                        <i class="fas fa-check-circle"></i> النظام يعمل بشكل صحيح
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h4>إدارة المبيعات</h4>
                        <p>نظام شامل لإدارة العملاء والفواتير والمدفوعات مع دعم رموز QR والتكامل مع WhatsApp</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                        <h4>إدارة المخزون</h4>
                        <p>تتبع دقيق للمنتجات والمستودعات مع نظام تنبيهات ذكي وإدارة متعددة المواقع</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h4>الموارد البشرية</h4>
                        <p>إدارة شاملة للموظفين والحضور والرواتب مع نظام تقييم الأداء</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-calculator fa-3x text-warning mb-3"></i>
                        <h4>المحاسبة والمالية</h4>
                        <p>نظام محاسبي متكامل مع تقارير مالية شاملة ولوحات تحكم تفاعلية</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-file-medical fa-3x text-danger mb-3"></i>
                        <h4>الشؤون التنظيمية</h4>
                        <p>نظام خاص للامتثال الصيدلاني وإدارة التراخيص والفحوصات</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-chart-bar fa-3x text-secondary mb-3"></i>
                        <h4>التقارير والتحليلات</h4>
                        <p>تقارير ذكية ولوحات تحكم تفاعلية مع إمكانيات تصدير متقدمة</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Info -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="text-center mb-4">المميزات التقنية</h3>
                    <ul class="list-group">
                        <li class="list-group-item"><i class="fas fa-check text-success"></i> Laravel 11 - أحدث إصدار</li>
                        <li class="list-group-item"><i class="fas fa-check text-success"></i> PHP 8.2+ - أداء عالي</li>
                        <li class="list-group-item"><i class="fas fa-check text-success"></i> MySQL 8.0 - قاعدة بيانات قوية</li>
                        <li class="list-group-item"><i class="fas fa-check text-success"></i> Redis - تخزين مؤقت سريع</li>
                        <li class="list-group-item"><i class="fas fa-check text-success"></i> Bootstrap 5 - واجهة حديثة</li>
                        <li class="list-group-item"><i class="fas fa-check text-success"></i> نظام معياري متقدم</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3 class="text-center mb-4">الأمان والحماية</h3>
                    <ul class="list-group">
                        <li class="list-group-item"><i class="fas fa-shield-alt text-primary"></i> تشفير البيانات</li>
                        <li class="list-group-item"><i class="fas fa-shield-alt text-primary"></i> مصادقة ثنائية</li>
                        <li class="list-group-item"><i class="fas fa-shield-alt text-primary"></i> نسخ احتياطي تلقائي</li>
                        <li class="list-group-item"><i class="fas fa-shield-alt text-primary"></i> سجلات أمان شاملة</li>
                        <li class="list-group-item"><i class="fas fa-shield-alt text-primary"></i> حماية من الهجمات</li>
                        <li class="list-group-item"><i class="fas fa-shield-alt text-primary"></i> عزل البيانات</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- System Status -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4><i class="fas fa-server"></i> حالة النظام</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>معلومات الخادم:</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                                        <li><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></li>
                                        <li><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></li>
                                        <li><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5>حالة الملفات:</h5>
                                    <ul class="list-unstyled">
                                        <?php
                                        $files = [
                                            '../.env' => 'ملف البيئة',
                                            '../vendor/autoload.php' => 'تبعيات Composer',
                                            '../artisan' => 'Laravel Artisan',
                                            '../composer.json' => 'ملف Composer'
                                        ];
                                        
                                        foreach ($files as $file => $name) {
                                            $exists = file_exists($file);
                                            $icon = $exists ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                                            $status = $exists ? 'موجود' : 'مفقود';
                                            echo "<li><i class='$icon'></i> $name: $status</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h3>جاهز للبدء؟</h3>
            <p class="lead">النظام جاهز للاستخدام بمجرد إكمال الإعداد</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-dark">خطوات الإعداد المتبقية:</h5>
                            <ol class="text-start text-dark">
                                <li>تحديث إعدادات قاعدة البيانات في .env</li>
                                <li>تشغيل: <code>php artisan migrate --force</code></li>
                                <li>تشغيل: <code>php artisan db:seed --force</code></li>
                                <li>إنشاء حساب المدير الأول</li>
                            </ol>
                            <button class="btn btn-success" onclick="checkLaravel()">
                                <i class="fas fa-sync"></i> فحص حالة Laravel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white text-center">
        <div class="container">
            <p>&copy; 2024 MaxCon Solutions. جميع الحقوق محفوظة.</p>
            <p>مصمم خصيصاً للسوق العراقي | دعم فني 24/7</p>
            <p><small>للدعم الفني: support@maxcon.com | GitHub: MaxCon-SaaS</small></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkLaravel() {
            // محاولة الوصول لـ Laravel
            fetch('index.php')
                .then(response => {
                    if (response.ok && !response.url.includes('index.working.php')) {
                        alert('✅ Laravel يعمل بشكل صحيح! سيتم إعادة التوجيه...');
                        window.location.href = 'index.php';
                    } else {
                        alert('⚠️ Laravel لا يزال قيد الإعداد. يرجى إكمال خطوات الإعداد.');
                    }
                })
                .catch(error => {
                    alert('❌ Laravel غير جاهز بعد. يرجى إكمال الإعداد.');
                });
        }

        // فحص تلقائي كل دقيقة
        setInterval(function() {
            fetch('index.php')
                .then(response => {
                    if (response.ok && !response.url.includes('index.working.php')) {
                        // Laravel يعمل، إعادة توجيه تلقائي
                        window.location.href = 'index.php';
                    }
                })
                .catch(error => {
                    console.log('Laravel still not ready');
                });
        }, 60000);

        // تأثيرات تفاعلية
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
