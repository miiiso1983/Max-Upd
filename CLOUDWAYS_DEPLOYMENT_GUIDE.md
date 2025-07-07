# 🚀 دليل نشر MaxCon SaaS على Cloudways

## 📋 المتطلبات الأساسية

### 1. إعداد الخادم على Cloudways
- **نوع الخادم:** DigitalOcean أو AWS أو Google Cloud
- **حجم الخادم:** 2GB RAM كحد أدنى (4GB مُوصى به)
- **PHP Version:** 8.2 أو أحدث
- **MySQL Version:** 8.0 أو أحدث
- **Redis:** مُفعّل
- **SSL Certificate:** مُفعّل

### 2. الإضافات المطلوبة
```bash
# PHP Extensions المطلوبة
- php-gd
- php-imagick
- php-zip
- php-xml
- php-mbstring
- php-curl
- php-mysql
- php-redis
- php-intl
```

## 🔧 خطوات النشر

### الخطوة 1: إنشاء التطبيق على Cloudways

1. **إنشاء خادم جديد:**
   - اختر المزود (DigitalOcean مُوصى به)
   - حدد الحجم: 2GB RAM أو أكثر
   - اختر المنطقة الأقرب للعراق (Frankfurt أو London)

2. **إنشاء تطبيق Laravel:**
   - اختر Laravel من قائمة التطبيقات
   - حدد اسم التطبيق: `maxcon-saas`
   - انتظر حتى اكتمال الإعداد

### الخطوة 2: رفع الكود

1. **استخدام Git Deploy:**
```bash
# في لوحة تحكم Cloudways
Git Repository: https://github.com/miiiso1983/MaxCon-SaaS.git
Branch: main
```

2. **أو رفع الملفات يدوياً:**
```bash
# ضغط المشروع وتحميله عبر File Manager
zip -r maxcon-saas.zip . -x "*.git*" "node_modules/*" "vendor/*"
```

### الخطوة 3: إعداد قاعدة البيانات

1. **إنشاء قواعد البيانات:**
```sql
-- قاعدة البيانات الرئيسية
CREATE DATABASE maxcon_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- قاعدة بيانات Landlord (للمستأجرين)
CREATE DATABASE maxcon_landlord CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **إنشاء مستخدم قاعدة البيانات:**
```sql
CREATE USER 'maxcon_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON maxcon_saas.* TO 'maxcon_user'@'localhost';
GRANT ALL PRIVILEGES ON maxcon_landlord.* TO 'maxcon_user'@'localhost';
FLUSH PRIVILEGES;
```

### الخطوة 4: تكوين ملف .env

1. **نسخ ملف البيئة:**
```bash
cp .env.cloudways .env
```

2. **تحديث المتغيرات المطلوبة:**
```bash
# تحديث معلومات قاعدة البيانات
DB_DATABASE=maxcon_saas
DB_USERNAME=maxcon_user
DB_PASSWORD=your_actual_password

# تحديث رابط التطبيق
APP_URL=https://your-domain.com

# توليد مفتاح التطبيق
php artisan key:generate
```

### الخطوة 5: تثبيت التبعيات

```bash
# تثبيت Composer dependencies
composer install --optimize-autoloader --no-dev

# تثبيت NPM dependencies
npm install
npm run build
```

### الخطوة 6: تشغيل المايجريشن والسيدرز

```bash
# تشغيل المايجريشن
php artisan migrate --force

# تشغيل السيدرز
php artisan db:seed --force

# إنشاء رابط التخزين
php artisan storage:link

# تحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔐 إعدادات الأمان

### 1. SSL Certificate
```bash
# في لوحة تحكم Cloudways
SSL Certificate > Let's Encrypt > Install
```

### 2. Firewall Rules
```bash
# السماح فقط للمنافذ المطلوبة
Port 80 (HTTP)
Port 443 (HTTPS)
Port 22 (SSH)
```

### 3. Security Headers
```apache
# في ملف .htaccess
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

## 📧 إعداد البريد الإلكتروني

### استخدام Gmail SMTP:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### إنشاء App Password لـ Gmail:
1. اذهب إلى Google Account Settings
2. Security > 2-Step Verification
3. App passwords > Generate new password
4. استخدم كلمة المرور المُولدة في MAIL_PASSWORD

## 🗄️ إعداد النسخ الاحتياطي

### 1. إعداد AWS S3:
```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=maxcon-backups
```

### 2. جدولة النسخ الاحتياطي:
```bash
# إضافة إلى crontab
0 2 * * * cd /applications/your-app && php artisan backup:run
```

## 🚀 تحسين الأداء

### 1. Redis Configuration:
```bash
# في لوحة تحكم Cloudways
Redis > Enable
Max Memory: 256MB
```

### 2. PHP Settings:
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
```

### 3. OPcache Settings:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

## 🔍 المراقبة والصيانة

### 1. Log Monitoring:
```bash
# مراقبة السجلات
tail -f storage/logs/laravel.log
```

### 2. Performance Monitoring:
```bash
# فحص استخدام الذاكرة
free -h

# فحص استخدام القرص
df -h
```

### 3. Database Maintenance:
```sql
-- تحسين الجداول
OPTIMIZE TABLE users, tenants, products;

-- فحص سلامة قاعدة البيانات
CHECK TABLE users, tenants, products;
```

## 🆘 استكشاف الأخطاء

### مشاكل شائعة وحلولها:

1. **خطأ 500:**
```bash
# فحص السجلات
tail -f storage/logs/laravel.log
# فحص أذونات الملفات
chmod -R 755 storage bootstrap/cache
```

2. **مشاكل قاعدة البيانات:**
```bash
# إعادة تشغيل المايجريشن
php artisan migrate:fresh --seed --force
```

3. **مشاكل الذاكرة:**
```bash
# زيادة memory_limit في php.ini
memory_limit = 1024M
```

## 📞 الدعم الفني

- **Cloudways Support:** متاح 24/7
- **Laravel Documentation:** https://laravel.com/docs
- **MaxCon SaaS Repository:** https://github.com/miiiso1983/MaxCon-SaaS

## ✅ قائمة التحقق النهائية

- [ ] الخادم يعمل بشكل صحيح
- [ ] قواعد البيانات تم إنشاؤها
- [ ] ملف .env تم تكوينه
- [ ] المايجريشن تم تشغيلها
- [ ] SSL Certificate مُفعّل
- [ ] البريد الإلكتروني يعمل
- [ ] النسخ الاحتياطي مُجدول
- [ ] المراقبة مُفعّلة
- [ ] الأداء محسّن

🎉 **تهانينا! MaxCon SaaS جاهز للعمل على Cloudways**
