<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>المصادقة الثنائية - MaxCon ERP</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo and Header -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                <i class="fas fa-shield-alt text-purple-600 text-3xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-white">المصادقة الثنائية</h2>
            <p class="mt-2 text-sm text-purple-100">أدخل رمز المصادقة للمتابعة</p>
        </div>

        <!-- Two Factor Challenge Form -->
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900 text-center">تأكيد الهوية</h3>
                <p class="text-gray-600 text-center mt-2">
                    @switch($user->two_factor_method)
                        @case('app')
                            أدخل الرمز من تطبيق المصادقة
                            @break
                        @case('sms')
                            أدخل الرمز المرسل إلى {{ substr($user->two_factor_phone, 0, 3) }}****{{ substr($user->two_factor_phone, -3) }}
                            @break
                        @case('email')
                            أدخل الرمز المرسل إلى بريدك الإلكتروني
                            @break
                    @endswitch
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-6">
                @csrf

                <!-- Authentication Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-key ml-2"></i>
                        رمز المصادقة
                    </label>
                    <input id="code" 
                           name="code" 
                           type="text" 
                           maxlength="8"
                           required 
                           autofocus
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center text-lg tracking-widest font-mono @error('code') border-red-500 @enderror"
                           placeholder="000000">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 text-center">
                        أدخل الرمز المكون من 6 أرقام أو رمز الاسترداد
                    </p>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-200">
                        <span class="absolute right-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-check text-purple-500 group-hover:text-purple-400"></i>
                        </span>
                        تأكيد
                    </button>
                </div>
            </form>

            <!-- Alternative Options -->
            <div class="mt-6 space-y-4">
                @if($user->two_factor_method === 'sms' || $user->two_factor_method === 'email')
                <div class="text-center">
                    <button onclick="resendCode()" class="text-sm text-purple-600 hover:text-purple-500 font-medium">
                        <i class="fas fa-redo ml-1"></i>
                        إعادة إرسال الرمز
                    </button>
                </div>
                @endif

                <div class="text-center">
                    <button onclick="showRecoveryForm()" class="text-sm text-gray-600 hover:text-gray-500">
                        استخدام رمز الاسترداد
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-500">
                        <i class="fas fa-arrow-right ml-1"></i>
                        العودة لتسجيل الدخول
                    </a>
                </div>
            </div>
        </div>

        <!-- Recovery Code Form (Hidden) -->
        <div id="recovery-form" class="hidden bg-white rounded-lg shadow-xl p-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900 text-center">رمز الاسترداد</h3>
                <p class="text-gray-600 text-center mt-2">أدخل أحد رموز الاسترداد الخاصة بك</p>
            </div>

            <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="recovery-code" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-life-ring ml-2"></i>
                        رمز الاسترداد
                    </label>
                    <input id="recovery-code" 
                           name="code" 
                           type="text" 
                           maxlength="8"
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center text-lg tracking-widest font-mono"
                           placeholder="XXXXXXXX">
                    <p class="mt-1 text-xs text-gray-500 text-center">
                        الرمز مكون من 8 أحرف وأرقام
                    </p>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-lg font-medium">
                        استخدام رمز الاسترداد
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <button onclick="hideRecoveryForm()" class="text-sm text-gray-600 hover:text-gray-500">
                    العودة للرمز العادي
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-sm text-purple-100">
                © {{ date('Y') }} MaxCon ERP. جميع الحقوق محفوظة.
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus on code input
            document.getElementById('code').focus();
            
            // Auto-format code input
            const codeInput = document.getElementById('code');
            codeInput.addEventListener('input', function(e) {
                // Remove non-numeric characters for regular codes
                if (this.value.length <= 6) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                }
                
                // Auto-submit when 6 digits are entered
                if (this.value.length === 6 && /^\d{6}$/.test(this.value)) {
                    this.form.submit();
                }
            });
            
            // Handle form submission
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitButton = this.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري التحقق...';
                });
            });
        });

        function showRecoveryForm() {
            document.querySelector('.bg-white:not(#recovery-form)').classList.add('hidden');
            document.getElementById('recovery-form').classList.remove('hidden');
            document.getElementById('recovery-code').focus();
        }

        function hideRecoveryForm() {
            document.getElementById('recovery-form').classList.add('hidden');
            document.querySelector('.bg-white:not(#recovery-form)').classList.remove('hidden');
            document.getElementById('code').focus();
        }

        function resendCode() {
            const button = event.target;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i>جاري الإرسال...';
            
            fetch('/auth/two-factor/send-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم إرسال الرمز بنجاح');
                } else {
                    alert('فشل في إرسال الرمز: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء إرسال الرمز');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-redo ml-1"></i>إعادة إرسال الرمز';
            });
        }

        // Auto-logout after 10 minutes of inactivity
        let inactivityTimer;
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                alert('انتهت مهلة الجلسة. سيتم إعادة توجيهك لصفحة تسجيل الدخول.');
                window.location.href = '{{ route("login") }}';
            }, 10 * 60 * 1000); // 10 minutes
        }
        
        // Reset timer on user activity
        document.addEventListener('click', resetInactivityTimer);
        document.addEventListener('keypress', resetInactivityTimer);
        resetInactivityTimer();
    </script>
</body>
</html>
