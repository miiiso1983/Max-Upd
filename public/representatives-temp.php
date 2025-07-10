<?php
/**
 * صفحة مندوبي المبيعات المؤقتة لـ MaxCon SaaS
 */

session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login-temp.php');
    exit;
}

// بيانات تجريبية لمندوبي المبيعات
$sales_reps = [
    [
        'id' => 1,
        'name' => 'أحمد محمد علي',
        'email' => 'ahmed@maxcon.com',
        'phone' => '07901234567',
        'territory' => 'بغداد - الكرخ',
        'monthly_target' => 50000,
        'current_sales' => 35000,
        'status' => 'نشط',
        'join_date' => '2024-01-15'
    ],
    [
        'id' => 2,
        'name' => 'فاطمة حسن محمود',
        'email' => 'fatima@maxcon.com',
        'phone' => '07912345678',
        'territory' => 'بغداد - الرصافة',
        'monthly_target' => 45000,
        'current_sales' => 42000,
        'status' => 'نشط',
        'join_date' => '2024-02-01'
    ],
    [
        'id' => 3,
        'name' => 'محمد عبد الله',
        'email' => 'mohammed@maxcon.com',
        'phone' => '07923456789',
        'territory' => 'البصرة',
        'monthly_target' => 40000,
        'current_sales' => 28000,
        'status' => 'نشط',
        'join_date' => '2024-01-20'
    ],
    [
        'id' => 4,
        'name' => 'زينب أحمد',
        'email' => 'zainab@maxcon.com',
        'phone' => '07934567890',
        'territory' => 'أربيل',
        'monthly_target' => 35000,
        'current_sales' => 31000,
        'status' => 'نشط',
        'join_date' => '2024-03-01'
    ],
    [
        'id' => 5,
        'name' => 'علي حسين',
        'email' => 'ali@maxcon.com',
        'phone' => '07945678901',
        'territory' => 'النجف',
        'monthly_target' => 30000,
        'current_sales' => 18000,
        'status' => 'معلق',
        'join_date' => '2024-02-15'
    ]
];

// حساب الإحصائيات
$total_reps = count($sales_reps);
$active_reps = count(array_filter($sales_reps, function($rep) { return $rep['status'] === 'نشط'; }));
$total_target = array_sum(array_column($sales_reps, 'monthly_target'));
$total_sales = array_sum(array_column($sales_reps, 'current_sales'));
$achievement_percentage = $total_target > 0 ? round(($total_sales / $total_target) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مندوبو المبيعات - MaxCon SaaS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .logo {
            font-size: 1.8em;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1em;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            margin: 30px 0;
        }
        
        .table-header {
            background: #667eea;
            color: white;
            padding: 20px;
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .status.suspended {
            background: #fff3cd;
            color: #856404;
        }
        
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin: 5px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .btn.secondary {
            background: #6c757d;
        }
        
        .btn.secondary:hover {
            background: #5a6268;
        }
        
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
        
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">MaxCon SaaS - مندوبو المبيعات</div>
                <div class="user-info">
                    <span>مرحباً، <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="login-temp.php?logout=1" class="btn secondary">تسجيل الخروج</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $total_reps; ?></div>
                <div class="stat-label">إجمالي المندوبين</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $active_reps; ?></div>
                <div class="stat-label">المندوبين النشطين</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-number"><?php echo number_format($total_target); ?></div>
                <div class="stat-label">الهدف الشهري (دينار)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number"><?php echo number_format($total_sales); ?></div>
                <div class="stat-label">المبيعات الحالية (دينار)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $achievement_percentage; ?>%</div>
                <div class="stat-label">نسبة الإنجاز</div>
            </div>
        </div>
        
        <div class="table-container">
            <div class="table-header">
                📋 قائمة مندوبي المبيعات
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>المنطقة</th>
                        <th>الهدف الشهري</th>
                        <th>المبيعات الحالية</th>
                        <th>نسبة الإنجاز</th>
                        <th>الحالة</th>
                        <th>تاريخ الانضمام</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales_reps as $rep): ?>
                        <?php 
                        $achievement = $rep['monthly_target'] > 0 ? round(($rep['current_sales'] / $rep['monthly_target']) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($rep['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($rep['email']); ?></td>
                            <td><?php echo htmlspecialchars($rep['phone']); ?></td>
                            <td><?php echo htmlspecialchars($rep['territory']); ?></td>
                            <td><?php echo number_format($rep['monthly_target']); ?> د.ع</td>
                            <td><?php echo number_format($rep['current_sales']); ?> د.ع</td>
                            <td>
                                <div><?php echo $achievement; ?>%</div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min($achievement, 100); ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="status <?php echo $rep['status'] === 'نشط' ? 'active' : 'suspended'; ?>">
                                    <?php echo htmlspecialchars($rep['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($rep['join_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="actions">
            <a href="#" class="btn">➕ إضافة مندوب جديد</a>
            <a href="#" class="btn">📊 تقرير المبيعات</a>
            <a href="#" class="btn">📱 تطبيق الموبايل</a>
            <a href="index.bypass.php" class="btn secondary">🏠 الصفحة الرئيسية</a>
        </div>
    </div>
    
    <div class="footer">
        <p><strong>MaxCon SaaS</strong> - نظام إدارة الموارد المؤسسية</p>
        <p>مصمم خصيصاً للسوق العراقي | جميع الحقوق محفوظة © 2024</p>
    </div>
</body>
</html>

<?php
// معالجة تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login-temp.php');
    exit;
}
?>
