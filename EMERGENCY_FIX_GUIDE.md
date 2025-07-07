# 🚨 دليل الإصلاح الطارئ - MaxCon SaaS على Cloudways

## ❌ الأخطاء الحالية:
- `Target class [env] does not exist`
- `Call to undefined function highlight_file()`
- `HTTP 500 Internal Server Error`

## 🔧 الحل الفوري (5 دقائق)

### الخطوة 1: الوصول إلى SSH
```bash
# في Cloudways Panel
# اذهب إلى Server Management > SSH Access
# انسخ SSH command وشغله في Terminal
```

### الخطوة 2: الانتقال إلى مجلد التطبيق
```bash
cd /applications/your-app-name/public_html
```

### الخطوة 3: تشغيل الإصلاح الطارئ
```bash
# تشغيل سكريبت الإصلاح
php emergency-fix.php
```

### الخطوة 4: تثبيت التبعيات
```bash
# تثبيت Composer dependencies
composer install --no-dev --optimize-autoloader
```

### الخطوة 5: تحديث إعدادات قاعدة البيانات
```bash
# تحرير ملف .env
nano .env

# تحديث هذه القيم:
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_username  
DB_PASSWORD=your_actual_password
DB_HOST=127.0.0.1
```

### الخطوة 6: إعداد قاعدة البيانات
```bash
# تشغيل المايجريشن
php artisan migrate --force

# إنشاء رابط التخزين
php artisan storage:link
```

### الخطوة 7: اختبار التطبيق
```bash
# اختبار Laravel
php artisan about

# إذا نجح، اختبر في المتصفح
# إذا فشل، استخدم الملف البديل
```

## 🔄 إذا استمرت المشاكل

### استخدام الملف البديل:
```bash
# نسخ الملف الآمن
cp public/index.safe.php public/index.php
```

### فحص PHP Extensions في Cloudways:
1. اذهب إلى **Server Management**
2. **Settings & Packages**
3. تأكد من تفعيل:
   - ✅ php-gd
   - ✅ php-xml
   - ✅ php-mbstring
   - ✅ php-curl
   - ✅ php-zip

### فحص إعدادات PHP:
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
```

## 🔍 تشخيص المشاكل

### فحص السجلات:
```bash
# سجلات Laravel
tail -f storage/logs/laravel.log

# سجلات PHP
tail -f /var/log/php8.2-fpm.log

# سجلات Nginx
tail -f /var/log/nginx/error.log
```

### اختبار PHP:
```bash
# اختبار PHP الأساسي
php -v

# اختبار Extensions
php -m | grep -E "(gd|xml|mbstring)"

# اختبار highlight_file
php -r "if(function_exists('highlight_file')) echo 'OK'; else echo 'MISSING';"
```

## 📞 إذا لم تنجح الحلول

### معلومات للدعم الفني:
```bash
# جمع معلومات النظام
echo "=== System Info ===" > debug.txt
php -v >> debug.txt
echo "=== Extensions ===" >> debug.txt
php -m >> debug.txt
echo "=== Laravel ===" >> debug.txt
php artisan about >> debug.txt 2>&1
echo "=== Permissions ===" >> debug.txt
ls -la storage/ >> debug.txt
ls -la bootstrap/cache/ >> debug.txt
```

### الاتصال بالدعم:
- **GitHub Issues:** https://github.com/miiiso1983/MaxCon-SaaS/issues
- **البريد الإلكتروني:** support@maxcon.com
- **أرفق ملف:** debug.txt

## ✅ التحقق من نجاح الإصلاح

### علامات النجاح:
- ✅ `php artisan about` يعمل بدون أخطاء
- ✅ الموقع يفتح في المتصفح
- ✅ لا توجد أخطاء 500
- ✅ يمكن الوصول لصفحة تسجيل الدخول

### اختبار شامل:
```bash
# اختبار الأوامر الأساسية
php artisan route:list
php artisan config:show app
php artisan migrate:status
```

## 🎯 النتيجة المتوقعة

بعد تطبيق هذه الخطوات:
- ✅ **لا مزيد من أخطاء `Target class [env] does not exist`**
- ✅ **لا مزيد من أخطاء `highlight_file() undefined`**  
- ✅ **التطبيق يعمل بسلاسة**
- ✅ **يمكن الوصول لجميع الصفحات**

## ⏱️ الوقت المتوقع: 5-10 دقائق

🚀 **MaxCon SaaS سيعمل بشكل طبيعي بعد هذا الإصلاح!**
