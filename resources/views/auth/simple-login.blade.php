<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - MaxCon ERP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            margin: 20px;
        }

        @media (min-width: 768px) {
            .login-container {
                padding: 40px;
                margin: 0;
            }
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .brand-name {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin: 0 0 8px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            font-size: 14px;
            color: #666;
            margin: 0;
            font-weight: normal;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 22px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="email"], 
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus, 
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-left: 8px;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background: #5a6fd8;
        }
        
        button:active {
            transform: translateY(1px);
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        
        .success {
            background: #efe;
            color: #3c3;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #cfc;
        }
        
        .credentials {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        
        .credentials strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <h1 class="brand-name">MaxCon</h1>
            <p class="brand-subtitle">نظام إدارة الموارد المؤسسية</p>
        </div>
        <h2>🔐 تسجيل الدخول</h2>
        
        @if(session('error'))
            <div class="error">
                {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif
        
        <form method="POST" action="/login">
            @csrf
            
            <div class="form-group">
                <label for="email">📧 البريد الإلكتروني</label>
                <input type="email"
                       id="email"
                       name="email"
                       value=""
                       required
                       autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">🔑 كلمة المرور</label>
                <input type="password"
                       id="password"
                       name="password"
                       value=""
                       required
                       autocomplete="current-password">
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">تذكرني</label>
            </div>
            
            <button type="submit">🚀 تسجيل الدخول</button>
        </form>
        

    </div>
</body>
</html>
