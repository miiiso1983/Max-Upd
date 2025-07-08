# 🚀 دليل نشر MaxCon SaaS على Hostinger

## 📋 **الخطوات المطلوبة:**

### **1️⃣ تثبيت المكتبات عبر SSH**

#### **الوصول إلى SSH:**
1. ادخل إلى **hPanel**
2. اذهب إلى **Advanced → SSH Access**
3. فعّل **SSH Access** إذا لم يكن مفعلاً
4. استخدم **Terminal** أو **PuTTY** للاتصال

#### **الأوامر المطلوبة:**
```bash
# 1. الانتقال إلى مجلد الموقع
cd public_html

# 2. التحقق من وجود الملفات
ls -la

# 3. تثبيت المكتبات
composer install --no-dev --optimize-autoloader

# 4. إنشاء مفتاح التطبيق
php artisan key:generate

# 5. تحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. إعداد الصلاحيات
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

### **2️⃣ إعداد قاعدة البيانات في hPanel**

#### **إنشاء قاعدة البيانات:**
1. في **hPanel → Databases → MySQL Databases**
2. انقر على **"Create Database"**
3. اسم قاعدة البيانات: `maxcon_saas`
4. انقر **"Create"**

#### **إنشاء مستخدم:**
1. في نفس الصفحة → **MySQL Users**
2. انقر على **"Create User"**
3. اسم المستخدم: `maxcon_user`
4. اختر كلمة مرور قوية
5. انقر **"Create"**

#### **ربط المستخدم بقاعدة البيانات:**
1. في **MySQL Databases → Add User to Database**
2. اختر المستخدم: `maxcon_user`
3. اختر قاعدة البيانات: `maxcon_saas`
4. اختر **All Privileges**
5. انقر **"Add"**

---

### **3️⃣ تحديث ملف .env**

بعد إنشاء قاعدة البيانات، قم بتحديث الأسطر التالية في ملف `.env`:

```env
DB_DATABASE=maxcon_saas
DB_USERNAME=maxcon_user
DB_PASSWORD=كلمة_المرور_التي_اخترتها

TENANT_DB_USERNAME=maxcon_user
TENANT_DB_PASSWORD=كلمة_المرور_التي_اخترتها
```

---

### **4️⃣ تشغيل المايجريشن**

```bash
# في SSH Terminal
php artisan migrate --force
```

---

### **5️⃣ اختبار النظام**

#### **الروابط للاختبار:**
- **الصفحة الرئيسية:** https://red-mouse-794847.hostingersite.com
- **صفحة الاختبار:** https://red-mouse-794847.hostingersite.com/test.php

---

## ⚠️ **ملاحظات مهمة:**

1. **استبدل `YOUR_ACTUAL_PASSWORD_HERE`** بكلمة المرور الفعلية
2. **احفظ معلومات قاعدة البيانات** في مكان آمن
3. **تأكد من تفعيل SSL** في Hostinger
4. **قم بعمل نسخة احتياطية** من الملفات

---

## 🔧 **استكشاف الأخطاء:**

### **خطأ Composer:**
```bash
# إذا لم يعمل composer
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev
```

### **خطأ الصلاحيات:**
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
```

### **خطأ قاعدة البيانات:**
- تأكد من صحة اسم قاعدة البيانات
- تأكد من صحة اسم المستخدم وكلمة المرور
- تأكد من ربط المستخدم بقاعدة البيانات

---

## 📞 **الدعم:**

إذا واجهت أي مشاكل، تأكد من:
1. **تفعيل PHP 8.1+** في Hostinger
2. **تفعيل Extensions المطلوبة**
3. **التحقق من error logs** في hPanel

---

**🎉 بعد إكمال هذه الخطوات، سيكون MaxCon SaaS جاهزاً للاستخدام!**
