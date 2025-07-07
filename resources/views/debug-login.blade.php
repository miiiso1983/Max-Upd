<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>اختبار تسجيل الدخول</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }
        button:hover {
            background: #005a87;
        }
        .debug {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 اختبار تسجيل الدخول</h1>
        
        <div class="debug">
            <strong>معلومات التشخيص:</strong><br>
            الوقت الحالي: {{ now() }}<br>
            Laravel Version: {{ app()->version() }}<br>
            PHP Version: {{ phpversion() }}<br>
            Session ID: {{ session()->getId() }}<br>
            CSRF Token: {{ csrf_token() }}
        </div>
        
        @if(session('error'))
            <div class="error">
                ❌ {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="success">
                ✅ {{ session('success') }}
            </div>
        @endif
        
        @if(auth()->check())
            <div class="success">
                ✅ أنت مسجل دخول بالفعل!<br>
                المستخدم: {{ auth()->user()->name }}<br>
                البريد الإلكتروني: {{ auth()->user()->email }}<br>
                Super Admin: {{ auth()->user()->is_super_admin ? 'نعم' : 'لا' }}
                <br><br>
                <a href="/dashboard" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    🏠 الذهاب إلى لوحة التحكم
                </a>
                <br><br>
                <a href="/logout" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    🚪 تسجيل الخروج
                </a>
            </div>
        @else
            <form method="POST" action="/debug-login" onsubmit="return confirmSubmit()">
                @csrf
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="admin@maxcon-erp.com" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور:</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           value="MaxCon@2025" 
                           required>
                </div>
                
                <button type="submit">🔐 تسجيل الدخول</button>
            </form>
            
            <div class="debug" style="margin-top: 20px;">
                <strong>اختبارات سريعة:</strong><br>
                <a href="/debug-login?test=user" style="color: #007cba;">اختبار وجود المستخدم</a><br>
                <a href="/debug-login?test=password" style="color: #007cba;">اختبار كلمة المرور</a><br>
                <a href="/debug-login?test=session" style="color: #007cba;">اختبار الجلسة</a>
            </div>
        @endif
    </div>
    
    <script>
        function confirmSubmit() {
            console.log('Form is being submitted...');
            return confirm('هل تريد تسجيل الدخول؟');
        }
        
        // Log any JavaScript errors
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
        });
        
        console.log('Debug page loaded successfully');
    </script>
</body>
</html>
