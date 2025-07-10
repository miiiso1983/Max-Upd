<?php
/**
 * صفحة تسجيل دخول مؤقتة لـ MaxCon SaaS
 */

session_start();

// بيانات تسجيل الدخول الافتراضية
$default_email = 'admin@maxcon-erp.com';
$default_password = 'MaxCon@2025';

$error = '';
$success = '';

// معالجة تسجيل الدخول
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email === $default_email && $password === $default_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = 'مدير النظام';
        $success = 'تم تسجيل الدخول بنجاح!';
        
        // إعادة توجيه إلى صفحة المندوبين
        header('Location: representatives-temp.php');
        exit;
    } else {
        $error = 'بيانات تسجيل الدخول غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - MaxCon SaaS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        
        .logo {
            text-align: center;
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            background: #667eea;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .default-credentials {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
        }
        
        .default-credentials h4 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        
        .default-credentials p {
            color: #333;
            margin: 5px 0;
        }
        
        .default-credentials code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">MaxCon SaaS</div>
        <div class="subtitle">تسجيل الدخول إلى النظام</div>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $default_email); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>" required>
            </div>
            
            <button type="submit" class="btn">🔐 تسجيل الدخول</button>
        </form>
        
        <div class="default-credentials">
            <h4>🔑 بيانات تسجيل الدخول الافتراضية</h4>
            <p><strong>البريد الإلكتروني:</strong> <code><?php echo $default_email; ?></code></p>
            <p><strong>كلمة المرور:</strong> <code><?php echo $default_password; ?></code></p>
            <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                يمكنك استخدام هذه البيانات للدخول إلى النظام
            </p>
        </div>
        
        <div class="links">
            <a href="index.bypass.php">🏠 الصفحة الرئيسية</a>
            <a href="test-db.php">🔍 اختبار قاعدة البيانات</a>
        </div>
    </div>
</body>
</html>
