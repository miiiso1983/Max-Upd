# ⚙️ إعدادات خادم Cloudways لـ MaxCon SaaS

## 🖥️ مواصفات الخادم المُوصى بها

### للاستخدام الخفيف (1-10 مستأجرين):
- **RAM:** 2GB
- **CPU:** 1 Core
- **Storage:** 50GB SSD
- **Bandwidth:** 2TB

### للاستخدام المتوسط (10-50 مستأجر):
- **RAM:** 4GB
- **CPU:** 2 Cores
- **Storage:** 100GB SSD
- **Bandwidth:** 3TB

### للاستخدام الكثيف (50+ مستأجر):
- **RAM:** 8GB+
- **CPU:** 4+ Cores
- **Storage:** 200GB+ SSD
- **Bandwidth:** 5TB+

## 🔧 إعدادات PHP

### في لوحة تحكم Cloudways > Server Management > Settings & Packages

```ini
# PHP Settings
PHP Version = 8.2 أو أحدث
max_execution_time = 300
memory_limit = 512M
upload_max_filesize = 100M
post_max_size = 100M
max_input_vars = 3000
max_file_uploads = 50

# OPcache Settings
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1

# Session Settings
session.gc_maxlifetime = 86400
session.cookie_lifetime = 86400
```

## 🗄️ إعدادات MySQL

### في Database Manager:

```sql
-- إعدادات الأداء
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

-- إعدادات الاتصالات
max_connections = 200
wait_timeout = 600
interactive_timeout = 600

-- إعدادات الذاكرة
key_buffer_size = 256M
sort_buffer_size = 4M
read_buffer_size = 2M
read_rnd_buffer_size = 8M
myisam_sort_buffer_size = 64M
table_open_cache = 4000
```

## 🚀 إعدادات Redis

### في Redis Manager:

```conf
# الذاكرة
maxmemory 256mb
maxmemory-policy allkeys-lru

# الاستمرارية
save 900 1
save 300 10
save 60 10000

# الشبكة
timeout 300
tcp-keepalive 60

# الأمان
requirepass your_redis_password
```

## 🌐 إعدادات Nginx

### ملف التكوين المُخصص:

```nginx
# في Application Settings > Nginx Config

server {
    listen 80;
    listen 443 ssl http2;
    server_name your-domain.com *.your-domain.com;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Root Directory
    root /applications/maxcon-saas/public_html/public;
    index index.php index.html index.htm;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript application/json;
    
    # File Upload Size
    client_max_body_size 100M;
    
    # Laravel Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }
    
    # Static Files Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt|tar|gz)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Security - Hide sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(vendor|storage|bootstrap|config|database|resources|routes|tests)/ {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

## 📊 مراقبة الأداء

### في Monitoring:

```bash
# تفعيل المراقبة
- Server Monitoring: مُفعّل
- Application Monitoring: مُفعّل
- Database Monitoring: مُفعّل
- Redis Monitoring: مُفعّل

# تنبيهات
- CPU Usage > 80%
- Memory Usage > 85%
- Disk Usage > 90%
- Database Connections > 150
```

## 🔐 إعدادات الأمان

### Firewall Rules:

```bash
# المنافذ المسموحة
Port 22 (SSH) - IP محدد فقط
Port 80 (HTTP) - الكل
Port 443 (HTTPS) - الكل
Port 3306 (MySQL) - محلي فقط
Port 6379 (Redis) - محلي فقط

# حظر المنافذ الأخرى
Default Policy: DENY
```

### SSL/TLS Configuration:

```bash
# Let's Encrypt
- Auto-renewal: مُفعّل
- Force HTTPS: مُفعّل
- HSTS: مُفعّل
- Certificate Transparency: مُفعّل
```

## 📧 إعدادات البريد الإلكتروني

### SMTP Configuration:

```env
# Gmail SMTP (مُوصى به)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# أو استخدام SendGrid
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

## 🗄️ إعداد النسخ الاحتياطي

### Automated Backups:

```bash
# في Backup Manager
- Frequency: يومياً في 2:00 AM
- Retention: 30 يوم
- Include: Database + Files
- Compression: مُفعّل
- Encryption: مُفعّل
```

### External Backup (AWS S3):

```bash
# تثبيت AWS CLI
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
sudo ./aws/install

# إعداد AWS
aws configure
AWS Access Key ID: your-access-key
AWS Secret Access Key: your-secret-key
Default region name: eu-west-1
Default output format: json
```

## 🔄 إعداد Cron Jobs

### في Cron Job Manager:

```bash
# Laravel Scheduler
* * * * * cd /applications/maxcon-saas/public_html && php artisan schedule:run >> /dev/null 2>&1

# Backup Job
0 2 * * * cd /applications/maxcon-saas/public_html && php artisan backup:run

# Log Cleanup
0 3 * * 0 cd /applications/maxcon-saas/public_html && php artisan log:clear

# Cache Cleanup
0 4 * * * cd /applications/maxcon-saas/public_html && php artisan cache:clear

# Queue Worker (إذا كان مطلوباً)
* * * * * cd /applications/maxcon-saas/public_html && php artisan queue:work --stop-when-empty
```

## 📈 تحسين الأداء

### Database Optimization:

```sql
-- تحسين الجداول أسبوعياً
OPTIMIZE TABLE users, tenants, products, invoices, payments;

-- فحص سلامة قاعدة البيانات
CHECK TABLE users, tenants, products;

-- إعادة بناء الفهارس
ANALYZE TABLE users, tenants, products;
```

### Application Optimization:

```bash
# تحسين Composer
composer dump-autoload --optimize

# تحسين Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# تنظيف الملفات المؤقتة
php artisan cache:clear
php artisan view:clear
```

## 🚨 استكشاف الأخطاء

### مراقبة السجلات:

```bash
# سجلات Laravel
tail -f storage/logs/laravel.log

# سجلات Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# سجلات PHP
tail -f /var/log/php8.2-fpm.log

# سجلات MySQL
tail -f /var/log/mysql/error.log
```

### أوامر التشخيص:

```bash
# فحص حالة الخدمات
systemctl status nginx
systemctl status php8.2-fpm
systemctl status mysql
systemctl status redis

# فحص استخدام الموارد
htop
iotop
nethogs

# فحص الاتصالات
netstat -tulpn
ss -tulpn
```

## ✅ قائمة التحقق النهائية

- [ ] PHP 8.2+ مُثبت ومُكوّن
- [ ] MySQL 8.0+ مُثبت ومُكوّن
- [ ] Redis مُفعّل ومُكوّن
- [ ] Nginx مُكوّن بشكل صحيح
- [ ] SSL Certificate مُثبت
- [ ] Firewall مُكوّن
- [ ] النسخ الاحتياطي مُجدول
- [ ] Cron Jobs مُضاف
- [ ] المراقبة مُفعّلة
- [ ] السجلات تعمل بشكل صحيح
- [ ] الأداء محسّن

🎯 **الخادم جاهز لاستضافة MaxCon SaaS!**
