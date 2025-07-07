<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - MaxCon ERP</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
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
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #5a6fd8;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>تسجيل الدخول (اختبار)</h1>
        
        @if(session('errors'))
            <div class="error">
                @foreach(session('errors')->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="/test-login" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="admin@maxcon-erp.com" required>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" value="MaxCon@2025" required>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> تذكرني
                </label>
            </div>

            <button type="submit" id="loginBtn">تسجيل الدخول</button>
        </form>

        <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.textContent = 'جاري تسجيل الدخول...';

            const formData = new FormData(this);

            fetch('/test-login', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.href = '/dashboard';
                } else {
                    throw new Error('Login failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.textContent = 'تسجيل الدخول';
                alert('حدث خطأ في تسجيل الدخول');
            });
        });
        </script>
        
        <p style="margin-top: 20px; text-align: center; font-size: 14px; color: #666;">
            البريد الإلكتروني: admin@maxcon-erp.com<br>
            كلمة المرور: MaxCon@2025
        </p>
    </div>
</body>
</html>
