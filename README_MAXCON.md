# 🚀 MaxCon SaaS - نظام إدارة الموارد المؤسسية متعدد المستأجرين

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Arabic](https://img.shields.io/badge/Language-Arabic%20RTL-orange.svg)](README.md)
[![Cloudways](https://img.shields.io/badge/Hosting-Cloudways%20Ready-brightgreen.svg)](https://cloudways.com)

## 📖 نظرة عامة

MaxCon SaaS هو نظام إدارة الموارد المؤسسية (ERP) شامل ومتعدد المستأجرين مصمم خصيصاً للسوق العراقي. يوفر النظام حلولاً متكاملة لإدارة المبيعات، المخزون، الموارد البشرية، المالية، والشؤون التنظيمية الصيدلانية.

## ✨ المميزات الرئيسية

### 🏢 النظام الأساسي
- **متعدد المستأجرين:** عزل كامل للبيانات بين العملاء
- **لوحة تحكم المدير الرئيسي:** إدارة شاملة للمستأجرين
- **هيكل معياري:** باستخدام nWidart/laravel-modules
- **نظام الأذونات:** إدارة متقدمة للأدوار والصلاحيات

### 🌍 التوطين العربي
- **دعم RTL كامل:** واجهة مُحسّنة للغة العربية
- **التقويم الهجري والميلادي:** مرونة في التواريخ
- **العملة العراقية:** دعم الدينار العراقي (IQD)
- **المناطق الزمنية:** توقيت بغداد/العراق

### 📊 الوحدات المتاحة
- **💰 المبيعات:** إدارة العملاء، الفواتير، والمدفوعات
- **📦 المخزون:** إدارة المنتجات، المستودعات، والحركات
- **👥 الموارد البشرية:** إدارة الموظفين، الحضور، والرواتب
- **💼 المالية:** المحاسبة والتقارير المالية
- **📈 التقارير:** تقارير شاملة وذكية
- **🏥 الشؤون التنظيمية:** للامتثال الصيدلاني

### 🔒 الأمان والحماية
- **المصادقة الثنائية:** حماية إضافية للحسابات
- **تشفير البيانات:** حماية شاملة للمعلومات الحساسة
- **سجلات الأمان:** مراقبة جميع العمليات
- **النسخ الاحتياطي التلقائي:** حماية البيانات

### 🎯 المميزات المتقدمة
- **رموز QR للفواتير:** معلومات الفاتورة في رمز QR
- **تكامل WhatsApp:** إرسال إشعارات الدفع
- **استيراد/تصدير Excel:** بدعم اللغة العربية
- **قوائم منسدلة ذكية:** بحث وتصفية متقدم

## 🛠️ التقنيات المستخدمة

- **Laravel 11:** إطار العمل الأساسي
- **PHP 8.2+:** لغة البرمجة
- **MySQL 8.0+:** قاعدة البيانات
- **Redis:** التخزين المؤقت والجلسات
- **Bootstrap 5:** واجهة المستخدم
- **Alpine.js:** التفاعل الديناميكي

## 📦 الحزم المستخدمة

- `nwidart/laravel-modules` - النظام المعياري
- `spatie/laravel-permission` - إدارة الأذونات
- `barryvdh/laravel-dompdf` - توليد PDF مع دعم RTL
- `maatwebsite/excel` - استيراد/تصدير Excel
- `simplesoftwareio/simple-qrcode` - رموز QR

## 🚀 النشر على Cloudways

### المتطلبات الأساسية
- **PHP:** 8.2 أو أحدث
- **MySQL:** 8.0 أو أحدث
- **Redis:** للتخزين المؤقت
- **SSL Certificate:** للأمان
- **Memory:** 2GB RAM كحد أدنى

### خطوات النشر السريع

1. **إنشاء خادم على Cloudways:**
```bash
# اختر المزود: DigitalOcean (مُوصى به)
# الحجم: 2GB RAM أو أكثر
# المنطقة: Frankfurt أو London
```

2. **استنساخ المشروع:**
```bash
git clone https://github.com/miiiso1983/MaxCon-SaaS.git
cd MaxCon-SaaS
```

3. **تشغيل سكريبت النشر:**
```bash
chmod +x deploy-cloudways.sh
./deploy-cloudways.sh
```

4. **تكوين البيئة:**
```bash
cp .env.cloudways .env
# قم بتحديث معلومات قاعدة البيانات والدومين
```

### 📚 أدلة مفصلة
- [دليل النشر الكامل](CLOUDWAYS_DEPLOYMENT_GUIDE.md)
- [إعدادات الخادم](cloudways-server-config.md)
- [دليل استيراد العملاء](CUSTOMER_IMPORT_GUIDE.md)
- [دليل استيراد Excel](EXCEL_IMPORT_GUIDE.md)

## 🔧 التثبيت المحلي

### 1. استنساخ المشروع
```bash
git clone https://github.com/miiiso1983/MaxCon-SaaS.git
cd MaxCon-SaaS
```

### 2. تثبيت التبعيات
```bash
composer install
npm install && npm run build
```

### 3. إعداد البيئة
```bash
cp .env.example .env
php artisan key:generate
```

### 4. إعداد قاعدة البيانات
```bash
# قم بتحديث معلومات قاعدة البيانات في .env
php artisan migrate --seed
```

### 5. إنشاء رابط التخزين
```bash
php artisan storage:link
```

### 6. تشغيل الخادم
```bash
php artisan serve
```

## 👤 الحسابات الافتراضية

### Super Admin
- **البريد الإلكتروني:** admin@maxcon.com
- **كلمة المرور:** password

### Tenant Admin
- **البريد الإلكتروني:** tenant@example.com
- **كلمة المرور:** password

## 📊 هيكل المشروع

```
MaxCon-SaaS/
├── app/
│   ├── Modules/           # الوحدات المعيارية
│   ├── Http/Controllers/  # المتحكمات
│   ├── Models/           # النماذج
│   └── Services/         # الخدمات
├── Modules/              # وحدات nWidart
│   ├── BackupManagement/ # إدارة النسخ الاحتياطي
│   └── ...
├── resources/
│   ├── views/            # القوالب
│   ├── lang/             # ملفات الترجمة
│   └── js/               # ملفات JavaScript
├── database/
│   ├── migrations/       # المايجريشن
│   └── seeders/          # السيدرز
└── docs/                 # الوثائق
```

## 🔐 الأمان

### إعدادات الأمان المُطبقة
- تشفير البيانات الحساسة
- حماية CSRF
- تنظيف المدخلات
- رؤوس الأمان HTTP
- حماية من XSS و SQL Injection

### أفضل الممارسات
- استخدم كلمات مرور قوية
- فعّل المصادقة الثنائية
- راقب سجلات الأمان
- حدّث النظام بانتظام

## 📈 الأداء

### تحسينات مُطبقة
- تخزين مؤقت متقدم مع Redis
- ضغط الملفات (Gzip)
- تحسين قواعد البيانات
- تحسين الصور والأصول

### مراقبة الأداء
- مراقبة استخدام الذاكرة
- مراقبة استجابة قاعدة البيانات
- تتبع الأخطاء والاستثناءات

## 🆘 الدعم الفني

### المشاكل الشائعة
- [استكشاف أخطاء المصادقة](docs/AUTHENTICATION_FIX.md)
- [إصلاح مشاكل المستأجرين](docs/TENANT_CONTEXT_FIX.md)
- [حل مشاكل التقويم](docs/GREGORIAN_CALENDAR_FIX.md)

### طلب المساعدة
- **GitHub Issues:** [إنشاء مشكلة جديدة](https://github.com/miiiso1983/MaxCon-SaaS/issues)
- **البريد الإلكتروني:** support@maxcon.com
- **الوثائق:** [دليل المستخدم الكامل](docs/)

## 🤝 المساهمة

نرحب بمساهماتكم! يرجى قراءة [دليل المساهمة](CONTRIBUTING.md) قبل البدء.

### خطوات المساهمة
1. Fork المشروع
2. إنشاء فرع للميزة الجديدة
3. Commit التغييرات
4. Push إلى الفرع
5. إنشاء Pull Request

## 📄 الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE).

## 🙏 شكر وتقدير

- **Laravel Team** - إطار العمل الرائع
- **Spatie** - حزم Laravel المفيدة
- **nWidart** - نظام الوحدات المعيارية
- **المجتمع العربي** - الدعم والتشجيع

## 📞 معلومات الاتصال

- **الموقع الإلكتروني:** https://maxcon.com
- **البريد الإلكتروني:** info@maxcon.com
- **GitHub:** https://github.com/miiiso1983/MaxCon-SaaS
- **LinkedIn:** [MaxCon Solutions](https://linkedin.com/company/maxcon)

---

<div align="center">
  <p>صُنع بـ ❤️ في العراق</p>
  <p>© 2024 MaxCon Solutions. جميع الحقوق محفوظة.</p>
</div>
