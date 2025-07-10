# 🏢 MaxCon ERP - نظام تخطيط موارد المؤسسات

<p align="center">
<img src="https://img.shields.io/badge/Laravel-10-red" alt="Laravel 10">
<img src="https://img.shields.io/badge/Flutter-3.10+-blue" alt="Flutter 3.10+">
<img src="https://img.shields.io/badge/PHP-8.1+-purple" alt="PHP 8.1+">
<img src="https://img.shields.io/badge/License-MIT-green" alt="MIT License">
</p>

## 🎯 نظرة عامة

نظام ERP شامل متعدد المستأجرين مع تطبيق مندوبي المبيعات المحمول، مصمم خصيصاً للسوق العراقي.

### الميزات الرئيسية:
- **🏢 نظام متعدد المستأجرين**: إدارة عدة شركات من منصة واحدة
- **📱 تطبيق محمول**: تطبيق Flutter لمندوبي المبيعات مع اختبار API
- **🌐 واجهة ويب**: لوحة تحكم شاملة للإدارة
- **🔄 مزامنة البيانات**: عمل أوفلاين مع مزامنة تلقائية
- **🇮🇶 دعم العربية**: واجهة RTL كاملة للسوق العراقي
- **💰 نظام الاستحصال**: مع إرسال WhatsApp تلقائي

## 🚀 التثبيت والإعداد

### متطلبات النظام
- PHP 8.1+
- Composer
- Node.js & NPM
- MySQL 8.0+
- Flutter 3.10+ (للتطبيق المحمول)

### خطوات التثبيت

1. **استنساخ المشروع**
```bash
git clone https://github.com/miiiso1983/Max-Upd.git
cd Max-Upd
```

2. **تثبيت التبعيات**
```bash
composer install
npm install
```

3. **إعداد البيئة**
```bash
cp .env.example .env
php artisan key:generate
```

4. **إعداد قاعدة البيانات**
```bash
# تحديث .env بمعلومات قاعدة البيانات
php artisan migrate
php artisan db:seed
```

5. **تشغيل الخادم**
```bash
php artisan serve
npm run dev
```

### بيانات الدخول الافتراضية
```
البريد الإلكتروني: admin@maxcon-erp.com
كلمة المرور: MaxCon@2025
```

## 📱 تطبيق مندوبي المبيعات

### إعداد التطبيق المحمول

1. **الانتقال لمجلد التطبيق**
```bash
cd flutter_sales_rep_app
```

2. **تثبيت التبعيات**
```bash
flutter pub get
```

3. **تشغيل التطبيق**
```bash
# للويب
flutter run -d chrome --web-port=8080

# للأندرويد
flutter run -d android

# للـ iOS
flutter run -d ios
```

### ميزات التطبيق المحمول
- 🔐 **تسجيل دخول آمن** مع بيانات تجريبية
- 🌐 **اختبار API** مباشر من التطبيق
- 👥 **إدارة العملاء** مع بيانات وهمية
- 📍 **تتبع الزيارات** مع محاكاة GPS
- ✅ **إدارة المهام** التفاعلية
- 📊 **تقارير الأداء** المرئية
- 💰 **نظام الاستحصال** مع WhatsApp
- 🔄 **عمل أوفلاين** مع مزامنة

### اختبار التطبيق
- افتح التطبيق على: `http://localhost:8080`
- استخدم البيانات: `admin@maxcon-erp.com` / `MaxCon@2025`
- اضغط "اختبار الاتصال بـ API" للتحقق من الاتصال

## 🌐 API Documentation

### نقاط النهاية الرئيسية

#### اختبار الاتصال
```
GET /api/test/sales-reps - اختبار وحدة مندوبي المبيعات
```

#### المصادقة
```
POST /api/mobile/login - تسجيل الدخول
POST /api/mobile/refresh - تحديث الرمز المميز
POST /api/mobile/logout - تسجيل الخروج
```

#### مندوبي المبيعات
```
GET /api/sales-reps - قائمة المندوبين
POST /api/sales-reps - إضافة مندوب جديد
GET /api/sales-reps/{id} - تفاصيل المندوب
PUT /api/sales-reps/{id} - تحديث المندوب
```

## 🤝 المساهمة

نرحب بمساهماتكم! يرجى اتباع الخطوات التالية:

1. Fork المشروع
2. إنشاء فرع للميزة الجديدة (`git checkout -b feature/AmazingFeature`)
3. Commit التغييرات (`git commit -m 'Add some AmazingFeature'`)
4. Push للفرع (`git push origin feature/AmazingFeature`)
5. فتح Pull Request

## 📞 الدعم والتواصل

- **البريد الإلكتروني**: support@maxcon-erp.com
- **الموقع**: https://maxcon-erp.com
- **GitHub Issues**: لتقارير الأخطاء والاقتراحات

## 📄 الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

---

**تم تطوير هذا النظام بـ ❤️ للسوق العراقي**
