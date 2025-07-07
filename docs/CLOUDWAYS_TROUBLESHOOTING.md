# 🚨 استكشاف أخطاء Cloudways وحلولها

## ❌ الأخطاء الشائعة وحلولها

### 1. `Target class [env] does not exist`

#### السبب:
- ملف `.env` مفقود أو تالف
- مشكلة في تحميل التكوين
- مشاكل في الأذونات

#### الحل:
```bash
# 1. إنشاء ملف .env
cp .env.production .env

# 2. توليد مفتاح التطبيق
php artisan key:generate --force

# 3. تنظيف الذاكرة المؤقتة
php artisan config:clear
php artisan cache:clear

# 4. تعيين الأذونات
chmod 644 .env
chmod -R 755 storage bootstrap/cache
```

### 2. `Call to undefined function highlight_file()`

#### السبب:
- PHP extension مفقود
- إعدادات PHP غير صحيحة

#### الحل في Cloudways:
1. **اذهب إلى Server Management**
2. **Settings & Packages**
3. **تأكد من تفعيل:**
   - `php-gd`
   - `php-xml`
   - `php-mbstring`
   - `php-curl`
   - `php-zip`

### 3. `HTTP 500 Internal Server Error`

#### الحل السريع:
```bash
# تشغيل سكريبت الإصلاح
chmod +x cloudways-fix.sh
./cloudways-fix.sh
```

## 🔧 خطوات الإصلاح المفصلة

### الخطوة 1: فحص ملف .env
```bash
# التحقق من وجود الملف
ls -la .env

# إذا لم يكن موجوداً
cp .env.production .env

# تحديث الإعدادات
nano .env
```

### الخطوة 2: إعدادات قاعدة البيانات
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_username
DB_PASSWORD=your_actual_password
```

### الخطوة 3: تنظيف شامل
```bash
# تنظيف جميع الذاكرة المؤقتة
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# إعادة بناء الذاكرة المؤقتة
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### الخطوة 4: الأذونات
```bash
# أذونات الملفات
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 .env

# أذونات المجلدات
chmod -R 775 storage/logs
chmod -R 775 storage/framework
```

### الخطوة 5: اختبار التطبيق
```bash
# اختبار Laravel
php artisan about

# اختبار قاعدة البيانات
php artisan tinker
>>> DB::connection()->getPdo();
```

## 🔍 تشخيص المشاكل

### فحص السجلات:
```bash
# سجلات Laravel
tail -f storage/logs/laravel.log

# سجلات PHP (في Cloudways)
tail -f /var/log/php8.2-fpm.log

# سجلات Nginx
tail -f /var/log/nginx/error.log
```

### فحص PHP:
```bash
# معلومات PHP
php -v
php -m | grep -E "(gd|xml|mbstring|curl|zip)"

# اختبار PHP
php -r "echo 'PHP يعمل بشكل صحيح';"
```

### فحص قاعدة البيانات:
```bash
# اختبار الاتصال
mysql -h 127.0.0.1 -u username -p database_name

# في Laravel
php artisan migrate:status
```

## ⚙️ إعدادات Cloudways المطلوبة

### PHP Settings:
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
max_input_vars = 3000
```

### PHP Extensions:
- ✅ php-gd
- ✅ php-xml
- ✅ php-mbstring
- ✅ php-curl
- ✅ php-zip
- ✅ php-mysql
- ✅ php-intl

### Nginx Configuration:
```nginx
# في Application Settings
client_max_body_size 100M;
fastcgi_read_timeout 300;
```

## 🚀 نشر صحيح على Cloudways

### 1. رفع الملفات:
```bash
# استخدام Git Deploy أو File Manager
# تأكد من رفع جميع الملفات عدا:
# - .git/
# - node_modules/
# - vendor/ (سيتم تثبيتها)
```

### 2. تثبيت التبعيات:
```bash
# في SSH Terminal
composer install --no-dev --optimize-autoloader
```

### 3. إعداد البيئة:
```bash
cp .env.production .env
php artisan key:generate --force
```

### 4. قاعدة البيانات:
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. التحسين:
```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📞 الحصول على المساعدة

### معلومات مفيدة للدعم:
```bash
# معلومات النظام
php artisan about

# معلومات PHP
php -v && php -m

# معلومات Laravel
cat composer.json | grep laravel

# حالة قاعدة البيانات
php artisan migrate:status
```

### أوامر التشخيص:
```bash
# فحص شامل
php artisan inspire
php artisan route:list
php artisan config:show app
```

## ✅ قائمة التحقق

- [ ] ملف .env موجود ومُكوّن
- [ ] APP_KEY مُولد
- [ ] إعدادات قاعدة البيانات صحيحة
- [ ] PHP Extensions مُثبتة
- [ ] الأذونات صحيحة
- [ ] الذاكرة المؤقتة مُحسّنة
- [ ] التطبيق يعمل بدون أخطاء

🎯 **بعد تطبيق هذه الحلول، يجب أن يعمل MaxCon SaaS بشكل صحيح على Cloudways!**
