# القائمة المنسدلة مع البحث والتصفية
## Filtered Searchable Select Component

مكون متقدم للقوائم المنسدلة مع إمكانيات البحث والتصفية المتقدمة، مصمم خصيصاً لنظام MaxCon ERP SaaS.

## 🌟 الميزات الرئيسية

### ✅ البحث والتصفية
- **بحث فوري**: البحث في الخيارات أثناء الكتابة
- **تصفية متعددة**: تصفية حسب معايير متعددة
- **تمييز النتائج**: تمييز النص المطابق في نتائج البحث
- **عداد النتائج**: عرض عدد النتائج المتاحة

### ✅ دعم AJAX
- **تحميل ديناميكي**: تحميل البيانات من الخادم
- **تخزين مؤقت**: تخزين النتائج لتحسين الأداء
- **مؤشر التحميل**: عرض حالة التحميل للمستخدم
- **معالجة الأخطاء**: التعامل مع أخطاء الشبكة

### ✅ واجهة المستخدم
- **تصميم متجاوب**: يعمل على جميع الأجهزة
- **دعم RTL**: مصمم للغة العربية
- **أنيميشن سلس**: تأثيرات بصرية محسنة
- **إمكانية الوصول**: دعم قارئ الشاشة

### ✅ وظائف متقدمة
- **اختيار متعدد**: إمكانية اختيار عدة عناصر
- **إنشاء جديد**: إضافة خيارات جديدة
- **تجميع الخيارات**: تنظيم الخيارات في مجموعات
- **API برمجي**: التحكم عبر JavaScript

## 📋 متطلبات النظام

- Laravel 10+
- jQuery 3.6+
- Select2 4.1+
- Tailwind CSS 3+

## 🚀 التثبيت والإعداد

### 1. إضافة الملفات المطلوبة

```bash
# نسخ ملفات المكون
cp resources/views/components/filtered-searchable-select.blade.php /path/to/your/project/
cp public/css/filtered-searchable-select.css /path/to/your/project/
cp public/js/filtered-searchable-select.js /path/to/your/project/
```

### 2. تضمين الملفات في Layout

```html
<!-- في head -->
<link href="{{ asset('css/filtered-searchable-select.css') }}" rel="stylesheet">

<!-- قبل إغلاق body -->
<script src="{{ asset('js/filtered-searchable-select.js') }}"></script>
```

## 📖 طرق الاستخدام

### 1. الاستخدام الأساسي

```blade
<x-filtered-searchable-select 
    name="product_id" 
    placeholder="اختر المنتج..."
    :options="[
        '1' => 'منتج أول',
        '2' => 'منتج ثاني',
        '3' => 'منتج ثالث'
    ]"
/>
```

### 2. مع التصفية

```blade
<x-filtered-searchable-select 
    name="medicine" 
    placeholder="اختر الدواء..."
    :showFilters="true"
    :filterBy="[
        'category' => [
            'label' => 'الفئة',
            'options' => [
                'antibiotic' => 'مضاد حيوي',
                'painkiller' => 'مسكن ألم'
            ]
        ]
    ]"
    :options="[
        'med1' => [
            'text' => 'دواء أول',
            'data' => ['category' => 'antibiotic']
        ]
    ]"
/>
```

### 3. مع AJAX

```blade
<x-filtered-searchable-select 
    name="customer_id" 
    placeholder="ابحث عن عميل..."
    ajaxUrl="/api/customers/search"
    :minimumInputLength="2"
    :allowCreate="true"
/>
```

### 4. اختيار متعدد مع تجميع

```blade
<x-filtered-searchable-select 
    name="specialties[]" 
    :multiple="true"
    :groupBy="'department'"
    :options="[
        [
            'value' => 'cardiology',
            'label' => 'أمراض القلب',
            'department' => 'الطب الباطني'
        ]
    ]"
/>
```

## ⚙️ المعاملات المتاحة

| المعامل | النوع | الافتراضي | الوصف |
|---------|------|----------|-------|
| `name` | string | - | اسم الحقل |
| `placeholder` | string | 'اختر من القائمة...' | النص التوضيحي |
| `searchPlaceholder` | string | 'ابحث أو اكتب للتصفية...' | نص البحث |
| `options` | array | [] | خيارات القائمة |
| `selected` | mixed | null | القيمة المختارة |
| `multiple` | boolean | false | اختيار متعدد |
| `allowClear` | boolean | true | السماح بالمسح |
| `allowCreate` | boolean | false | السماح بالإنشاء |
| `showFilters` | boolean | false | عرض التصفية |
| `filterBy` | array | [] | معايير التصفية |
| `ajaxUrl` | string | null | رابط AJAX |
| `minimumInputLength` | integer | 0 | أقل عدد أحرف للبحث |
| `maxResults` | integer | 50 | أقصى عدد نتائج |
| `groupBy` | string | null | تجميع حسب |

## 🔧 API البرمجي

### JavaScript API

```javascript
// إعادة التهيئة
FilteredSearchableSelect.reinitialize();

// تحديث الخيارات
FilteredSearchableSelect.updateOptions('#select-id', {
    'value1': 'نص أول',
    'value2': 'نص ثاني'
});

// تطبيق تصفية
FilteredSearchableSelect.setFilters('#select-id', {
    category: 'antibiotic'
});

// مسح التصفية
FilteredSearchableSelect.clearFilters('#select-id');

// الحصول على القيمة
const value = FilteredSearchableSelect.getValue('#select-id');

// تحديد قيمة
FilteredSearchableSelect.setValue('#select-id', 'new-value');

// مسح الاختيار
FilteredSearchableSelect.clear('#select-id');
```

### الأحداث المخصصة

```javascript
// عند الاختيار
$('.filtered-select-wrapper').on('filtered-select:select', function(e, data) {
    console.log('تم اختيار:', data);
});

// عند إلغاء الاختيار
$('.filtered-select-wrapper').on('filtered-select:unselect', function(e, data) {
    console.log('تم إلغاء اختيار:', data);
});

// عند المسح
$('.filtered-select-wrapper').on('filtered-select:clear', function(e) {
    console.log('تم مسح الاختيار');
});
```

## 🌐 AJAX API

### تنسيق الطلب

```javascript
{
    "q": "نص البحث",
    "page": 1,
    "per_page": 10,
    "filters": {
        "category": "antibiotic",
        "status": "active"
    }
}
```

### تنسيق الاستجابة

```javascript
{
    "results": [
        {
            "id": "1",
            "text": "نص الخيار"
        }
    ],
    "total": 100,
    "pagination": {
        "more": true
    }
}
```

## 🎨 التخصيص

### تخصيص الألوان

```css
:root {
    --primary-color: #3b82f6;
    --secondary-color: #8b5cf6;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
}
```

### تخصيص الخطوط

```css
.filtered-select-wrapper {
    font-family: 'Cairo', 'Segoe UI', sans-serif;
}
```

## 🔍 استكشاف الأخطاء

### مشاكل شائعة

1. **Select2 غير محمل**
   ```javascript
   // تأكد من تحميل Select2 قبل المكون
   if (typeof $.fn.select2 === 'undefined') {
       console.error('Select2 library not loaded');
   }
   ```

2. **AJAX لا يعمل**
   ```javascript
   // تحقق من صحة URL
   console.log('AJAX URL:', $select.data('ajax-url'));
   ```

3. **التصفية لا تعمل**
   ```javascript
   // تأكد من وجود data attributes
   console.log('Filter data:', $(option).data());
   ```

## 📱 الدعم والتوافق

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ iOS Safari 14+
- ✅ Android Chrome 90+

## 📄 الترخيص

هذا المكون جزء من نظام MaxCon ERP SaaS ومرخص للاستخدام الداخلي فقط.

## 🤝 المساهمة

للمساهمة في تطوير المكون:

1. Fork المشروع
2. إنشاء branch جديد
3. إضافة التحسينات
4. إرسال Pull Request

## 📞 الدعم الفني

للحصول على الدعم الفني:
- البريد الإلكتروني: support@maxcon-erp.com
- الهاتف: +964-XXX-XXXX
- الموقع: https://maxcon-erp.com
