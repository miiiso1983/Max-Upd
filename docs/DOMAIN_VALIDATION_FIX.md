# إصلاح مشكلة تنسيق النطاق في إنشاء المستأجرين
## Domain Validation Fix for Tenant Creation

### 🔍 **المشكلة الأصلية / Original Problem**

كان النظام يعرض الخطأ التالي عند إنشاء مستأجر جديد:
```
خطأ في إنشاء المستأجر: The domain field format is invalid
```

**السبب:** قاعدة التحقق من صحة النطاق كانت صارمة جداً وتتطلب:
- أحرف صغيرة فقط (a-z)
- أرقام (0-9)
- شرطات (-)
- لا تسمح بأحرف كبيرة أو مسافات أو رموز خاصة

### ✅ **الحل المطبق / Solution Implemented**

#### **1. تحديث قواعد التحقق / Updated Validation Rules**

```php
'domain' => [
    'required',
    'string',
    'max:255',
    'unique:tenants,domain',
    'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$|^[a-zA-Z0-9]$/',
    function ($attribute, $value, $fail) {
        // Custom validation logic
    }
],
```

**التحسينات:**
- ✅ يقبل الأحرف الكبيرة والصغيرة
- ✅ يمنع الشرطات في البداية أو النهاية
- ✅ يمنع الشرطات المتتالية
- ✅ يمنع الكلمات المحجوزة
- ✅ تحويل تلقائي للأحرف الصغيرة

#### **2. رسائل خطأ مخصصة / Custom Error Messages**

```php
[
    'domain.regex' => 'The domain field must contain only letters, numbers, and hyphens. / يجب أن يحتوي حقل النطاق على أحرف وأرقام وشرطات فقط.',
    'domain.unique' => 'This domain is already taken. / هذا النطاق مستخدم بالفعل.',
    'domain.required' => 'The domain field is required. / حقل النطاق مطلوب.',
]
```

#### **3. دوال مساعدة جديدة / New Helper Functions**

##### **أ. توليد نطاق من اسم الشركة**
```php
POST /api/domain-management/generate-domain
{
    "company_name": "شركة الأدوية العراقية"
}

Response:
{
    "suggested_domain": "shrkh-aladwyh-alaraqyh",
    "is_available": true
}
```

**المميزات:**
- تحويل النص العربي إلى أحرف إنجليزية
- إزالة الرموز الخاصة
- ضمان التنسيق الصحيح
- فحص التوفر التلقائي
- إضافة أرقام عند التكرار

##### **ب. فحص توفر النطاق**
```php
POST /api/domain-management/check-domain
{
    "domain": "new-pharmacy-2025"
}

Response:
{
    "domain": "new-pharmacy-2025",
    "is_available": true,
    "is_valid_format": true,
    "is_reserved": false,
    "message": "Domain is available! / النطاق متاح!"
}
```

### 📋 **قواعد النطاق الجديدة / New Domain Rules**

#### **✅ مسموح / Allowed:**
- `pharmacy-baghdad` ✅
- `medical-center` ✅
- `clinic123` ✅
- `hospital-2025` ✅
- `a` ✅ (حرف واحد)

#### **❌ غير مسموح / Not Allowed:**
- `-pharmacy` ❌ (يبدأ بشرطة)
- `pharmacy-` ❌ (ينتهي بشرطة)
- `pharmacy--center` ❌ (شرطات متتالية)
- `pharmacy center` ❌ (مسافات)
- `pharmacy@center` ❌ (رموز خاصة)
- `www` ❌ (كلمة محجوزة)
- `admin` ❌ (كلمة محجوزة)

#### **🔄 تحويل تلقائي / Auto Conversion:**
- `Pharmacy-Center` → `pharmacy-center`
- `MEDICAL_CLINIC` → `medical-clinic`
- `شركة الأدوية` → `shrkh-aladwyh`

### 🧪 **اختبار الحل / Testing the Solution**

#### **1. اختبار توليد النطاق**
```bash
curl -X POST "http://localhost:8000/api/domain-management/generate-domain" \
  -H "Content-Type: application/json" \
  -d '{"company_name": "شركة الأدوية العراقية"}'
```

#### **2. اختبار فحص النطاق**
```bash
curl -X POST "http://localhost:8000/api/domain-management/check-domain" \
  -H "Content-Type: application/json" \
  -d '{"domain": "test-pharmacy"}'
```

#### **3. اختبار إنشاء مستأجر**
```bash
curl -X POST "http://localhost:8000/api/super-admin/tenants" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "صيدلية بغداد",
    "domain": "baghdad-pharmacy",
    "company_name": "صيدلية بغداد للأدوية",
    "company_type": "pharmacy",
    "contact_person": "أحمد محمد",
    "email": "info@baghdad-pharmacy.com",
    "phone": "+964-1-234-5678",
    "address": "شارع الرشيد، بغداد",
    "city": "بغداد",
    "governorate": "Baghdad",
    "max_users": 10
  }'
```

### 🔧 **الملفات المحدثة / Updated Files**

1. **`app/Http/Controllers/SuperAdmin/TenantController.php`**
   - تحديث قواعد التحقق
   - إضافة دوال توليد وفحص النطاق
   - رسائل خطأ مخصصة

2. **`routes/api.php`**
   - إضافة طرق إدارة النطاق
   - طرق عامة للاختبار

3. **`docs/DOMAIN_VALIDATION_FIX.md`**
   - توثيق شامل للحل

### 🎯 **النتائج / Results**

- ✅ **حل المشكلة الأصلية:** لا مزيد من أخطاء تنسيق النطاق
- ✅ **تحسين تجربة المستخدم:** رسائل خطأ واضحة بالعربية والإنجليزية
- ✅ **أدوات مساعدة:** توليد وفحص النطاقات تلقائياً
- ✅ **دعم اللغة العربية:** تحويل النصوص العربية لنطاقات صالحة
- ✅ **مرونة أكبر:** قبول تنسيقات متنوعة مع ضمان الأمان

### 📞 **للدعم / For Support**

إذا واجهت أي مشاكل أخرى في إنشاء المستأجرين، يرجى التحقق من:

1. **تنسيق النطاق:** استخدم الأحرف والأرقام والشرطات فقط
2. **توفر النطاق:** تأكد من عدم استخدام النطاق مسبقاً
3. **الكلمات المحجوزة:** تجنب استخدام www, admin, api, إلخ
4. **استخدم أدوات المساعدة:** جرب دوال توليد وفحص النطاق

---

**تاريخ التحديث:** 2025-07-05  
**الإصدار:** 1.0  
**المطور:** MaxCon ERP Team
