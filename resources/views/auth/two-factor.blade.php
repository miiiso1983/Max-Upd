@extends('layouts.app')

@section('title', 'المصادقة الثنائية - MaxCon ERP')
@section('page-title', 'المصادقة الثنائية')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">المصادقة الثنائية</h1>
                <p class="text-gray-600">حماية إضافية لحسابك</p>
            </div>
            <div class="flex items-center">
                @if($user->hasTwoFactorAuthenticationEnabled())
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        <i class="fas fa-shield-alt ml-1"></i>
                        مفعل
                    </span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        <i class="fas fa-shield-alt ml-1"></i>
                        غير مفعل
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if(!$user->hasTwoFactorAuthenticationEnabled())
    <!-- Enable Two Factor Authentication -->
    <div class="bg-white rounded-lg p-6 card-shadow">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">تفعيل المصادقة الثنائية</h2>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                </div>
                <div class="mr-3">
                    <h3 class="text-sm font-medium text-blue-800">ما هي المصادقة الثنائية؟</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        المصادقة الثنائية تضيف طبقة حماية إضافية لحسابك. بعد إدخال كلمة المرور، ستحتاج إلى إدخال رمز من تطبيق المصادقة أو رسالة نصية.
                    </p>
                </div>
            </div>
        </div>

        <form id="enable-2fa-form" class="space-y-6">
            @csrf
            
            <!-- Method Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">اختر طريقة المصادقة</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <input type="radio" name="method" value="app" id="method-app" class="sr-only" checked>
                        <label for="method-app" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors method-option">
                            <i class="fas fa-mobile-alt text-2xl text-blue-600 mb-2"></i>
                            <span class="font-medium text-gray-900">تطبيق المصادقة</span>
                            <span class="text-sm text-gray-500 text-center">Google Authenticator أو Authy</span>
                        </label>
                    </div>
                    
                    <div class="relative">
                        <input type="radio" name="method" value="sms" id="method-sms" class="sr-only">
                        <label for="method-sms" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors method-option">
                            <i class="fas fa-sms text-2xl text-green-600 mb-2"></i>
                            <span class="font-medium text-gray-900">رسالة نصية</span>
                            <span class="text-sm text-gray-500 text-center">إرسال الرمز عبر SMS</span>
                        </label>
                    </div>
                    
                    <div class="relative">
                        <input type="radio" name="method" value="email" id="method-email" class="sr-only">
                        <label for="method-email" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors method-option">
                            <i class="fas fa-envelope text-2xl text-purple-600 mb-2"></i>
                            <span class="font-medium text-gray-900">البريد الإلكتروني</span>
                            <span class="text-sm text-gray-500 text-center">إرسال الرمز عبر الإيميل</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Phone Number (for SMS) -->
            <div id="phone-field" class="hidden">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                <input type="tel" name="phone" id="phone" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="+964 XXX XXX XXXX">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-shield-alt ml-2"></i>
                    تفعيل المصادقة الثنائية
                </button>
            </div>
        </form>
    </div>
    @else
    <!-- Two Factor Authentication Enabled -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- QR Code and Setup -->
        @if($user->two_factor_method === 'app' && !$user->two_factor_confirmed_at)
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">إعداد تطبيق المصادقة</h2>
            
            <div class="text-center mb-6">
                <div class="inline-block p-4 bg-white border border-gray-200 rounded-lg">
                    {!! $qrCode !!}
                </div>
                <p class="text-sm text-gray-600 mt-2">امسح هذا الرمز بتطبيق المصادقة</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h3 class="font-medium text-gray-900 mb-2">خطوات الإعداد:</h3>
                <ol class="text-sm text-gray-600 space-y-1">
                    <li>1. حمل تطبيق Google Authenticator أو Authy</li>
                    <li>2. امسح الرمز أعلاه بالتطبيق</li>
                    <li>3. أدخل الرمز المكون من 6 أرقام أدناه</li>
                </ol>
            </div>
            
            <form id="confirm-2fa-form">
                @csrf
                <div class="mb-4">
                    <label for="confirmation-code" class="block text-sm font-medium text-gray-700 mb-1">رمز التأكيد</label>
                    <input type="text" name="code" id="confirmation-code" maxlength="6"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-lg tracking-widest"
                           placeholder="000000">
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">
                    تأكيد الإعداد
                </button>
            </form>
        </div>
        @endif

        <!-- Recovery Codes -->
        @if($user->two_factor_confirmed_at)
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">رموز الاسترداد</h2>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    </div>
                    <div class="mr-3">
                        <p class="text-sm text-yellow-700">
                            احفظ هذه الرموز في مكان آمن. يمكنك استخدامها للدخول إذا فقدت جهازك.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mb-4">
                @foreach($recoveryCodes as $code)
                <div class="bg-gray-100 p-2 rounded text-center font-mono text-sm">
                    {{ $code }}
                </div>
                @endforeach
            </div>
            
            <div class="flex space-x-2 space-x-reverse">
                <button onclick="downloadRecoveryCodes()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm">
                    <i class="fas fa-download ml-1"></i>
                    تحميل
                </button>
                <button onclick="printRecoveryCodes()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg text-sm">
                    <i class="fas fa-print ml-1"></i>
                    طباعة
                </button>
                <button onclick="regenerateRecoveryCodes()" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-2 rounded-lg text-sm">
                    <i class="fas fa-sync ml-1"></i>
                    إنشاء جديد
                </button>
            </div>
        </div>
        @endif

        <!-- Settings and Disable -->
        @if($user->two_factor_confirmed_at)
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">إعدادات المصادقة الثنائية</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <span class="font-medium text-green-800">الحالة</span>
                        <p class="text-sm text-green-600">مفعل ومؤكد</p>
                    </div>
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-800">الطريقة</span>
                        <p class="text-sm text-gray-600">
                            @switch($user->two_factor_method)
                                @case('app')
                                    تطبيق المصادقة
                                    @break
                                @case('sms')
                                    رسالة نصية ({{ $user->two_factor_phone }})
                                    @break
                                @case('email')
                                    البريد الإلكتروني
                                    @break
                            @endswitch
                        </p>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                    <button onclick="showDisableModal()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg">
                        <i class="fas fa-times ml-2"></i>
                        إلغاء تفعيل المصادقة الثنائية
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

<!-- Disable 2FA Modal -->
<div id="disable2faModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">إلغاء تفعيل المصادقة الثنائية</h3>
                <button onclick="closeDisableModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-red-700">
                    تحذير: إلغاء تفعيل المصادقة الثنائية سيقلل من أمان حسابك.
                </p>
            </div>
            
            <form id="disable-2fa-form" class="space-y-4">
                @csrf
                <div>
                    <label for="disable-password" class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
                    <input type="password" name="password" id="disable-password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                
                <div>
                    <label for="disable-code" class="block text-sm font-medium text-gray-700 mb-1">رمز المصادقة أو رمز الاسترداد</label>
                    <input type="text" name="code" id="disable-code" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
                
                <div class="flex justify-end space-x-2 space-x-reverse">
                    <button type="button" onclick="closeDisableModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        إلغاء التفعيل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle method selection
    const methodOptions = document.querySelectorAll('input[name="method"]');
    const phoneField = document.getElementById('phone-field');
    
    methodOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Update visual selection
            document.querySelectorAll('.method-option').forEach(label => {
                label.classList.remove('border-blue-500', 'bg-blue-50');
                label.classList.add('border-gray-200');
            });
            
            this.nextElementSibling.classList.remove('border-gray-200');
            this.nextElementSibling.classList.add('border-blue-500', 'bg-blue-50');
            
            // Show/hide phone field
            if (this.value === 'sms') {
                phoneField.classList.remove('hidden');
                document.getElementById('phone').required = true;
            } else {
                phoneField.classList.add('hidden');
                document.getElementById('phone').required = false;
            }
        });
    });
    
    // Initialize first option
    document.getElementById('method-app').dispatchEvent(new Event('change'));
    
    // Handle enable 2FA form
    document.getElementById('enable-2fa-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري التفعيل...';
        
        fetch('/auth/two-factor/enable', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MaxCon.showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                MaxCon.showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            MaxCon.showNotification('حدث خطأ أثناء تفعيل المصادقة الثنائية', 'error');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-shield-alt ml-2"></i>تفعيل المصادقة الثنائية';
        });
    });
    
    // Handle confirm 2FA form
    const confirmForm = document.getElementById('confirm-2fa-form');
    if (confirmForm) {
        confirmForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.innerHTML = 'جاري التأكيد...';
            
            fetch('/auth/two-factor/confirm', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    MaxCon.showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    MaxCon.showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                MaxCon.showNotification('حدث خطأ أثناء تأكيد المصادقة الثنائية', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'تأكيد الإعداد';
            });
        });
    }
    
    // Handle disable 2FA form
    const disableForm = document.getElementById('disable-2fa-form');
    if (disableForm) {
        disableForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            
            submitButton.disabled = true;
            submitButton.innerHTML = 'جاري الإلغاء...';
            
            fetch('/auth/two-factor/disable', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    MaxCon.showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    MaxCon.showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                MaxCon.showNotification('حدث خطأ أثناء إلغاء المصادقة الثنائية', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'إلغاء التفعيل';
                closeDisableModal();
            });
        });
    }
});

function showDisableModal() {
    document.getElementById('disable2faModal').classList.remove('hidden');
}

function closeDisableModal() {
    document.getElementById('disable2faModal').classList.add('hidden');
    document.getElementById('disable-2fa-form').reset();
}

function downloadRecoveryCodes() {
    const codes = @json($recoveryCodes ?? []);
    const content = 'رموز الاسترداد - MaxCon ERP\n\n' + codes.join('\n');
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'maxcon-recovery-codes.txt';
    a.click();
    window.URL.revokeObjectURL(url);
}

function printRecoveryCodes() {
    const codes = @json($recoveryCodes ?? []);
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>رموز الاسترداد - MaxCon ERP</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .codes { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
                    .code { padding: 10px; border: 1px solid #ccc; text-align: center; font-family: monospace; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>رموز الاسترداد</h1>
                    <p>MaxCon ERP - ${new Date().toLocaleDateString('ar-IQ')}</p>
                </div>
                <div class="codes">
                    ${codes.map(code => `<div class="code">${code}</div>`).join('')}
                </div>
                <p style="margin-top: 30px; font-size: 12px; color: #666;">
                    احفظ هذه الرموز في مكان آمن. كل رمز يمكن استخدامه مرة واحدة فقط.
                </p>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function regenerateRecoveryCodes() {
    const code = prompt('أدخل رمز المصادقة الثنائية لإنشاء رموز جديدة:');
    if (!code) return;
    
    fetch('/auth/two-factor/regenerate-recovery-codes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            MaxCon.showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            MaxCon.showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        MaxCon.showNotification('حدث خطأ أثناء إنشاء الرموز الجديدة', 'error');
    });
}
</script>
@endpush
