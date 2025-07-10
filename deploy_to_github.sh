#!/bin/bash

echo "🚀 رفع مشروع MaxCon ERP على GitHub..."

# التحقق من وجود Git
if ! command -v git &> /dev/null; then
    echo "❌ Git غير مثبت. يرجى تثبيت Git أولاً."
    exit 1
fi

# إعداد Git إذا لم يكن معداً
echo "📝 إعداد Git..."
git config --global user.name "miiiso1983" 2>/dev/null || true
git config --global user.email "mustafaalrawan@gmail.com" 2>/dev/null || true

# تهيئة Git repository إذا لم يكن موجوداً
if [ ! -d ".git" ]; then
    echo "🔧 تهيئة Git repository..."
    git init
fi

# إضافة remote origin إذا لم يكن موجوداً
if ! git remote get-url origin &> /dev/null; then
    echo "🔗 إضافة remote repository..."
    git remote add origin https://github.com/miiiso1983/Max-Upd.git
fi

# إضافة جميع الملفات
echo "📁 إضافة الملفات..."
git add .

# التحقق من وجود تغييرات
if git diff --staged --quiet; then
    echo "ℹ️ لا توجد تغييرات جديدة للرفع."
else
    # إنشاء commit
    echo "💾 إنشاء commit..."
    git commit -m "🎉 MaxCon ERP: نظام شامل مع تطبيق مندوبي المبيعات

✨ الميزات الجديدة:
- 🏢 نظام ERP متعدد المستأجرين
- 📱 تطبيق Flutter لمندوبي المبيعات
- 🌐 اختبار API مباشر من التطبيق
- 💰 نظام الاستحصال مع WhatsApp
- 🇮🇶 دعم كامل للغة العربية RTL
- 🔄 عمل أوفلاين مع مزامنة تلقائية

🛠 التقنيات:
- Laravel 10 + MySQL
- Flutter 3.10+ 
- Tailwind CSS + Alpine.js
- API RESTful شامل

📱 التطبيق المحمول:
- تسجيل دخول آمن
- اختبار اتصال API
- إدارة العملاء والزيارات
- نظام المهام التفاعلي
- تقارير الأداء المرئية

🎯 مصمم خصيصاً للسوق العراقي"

    # رفع على GitHub
    echo "⬆️ رفع على GitHub..."
    git push -u origin main

    if [ $? -eq 0 ]; then
        echo ""
        echo "🎉 تم رفع المشروع بنجاح على GitHub!"
        echo ""
        echo "🔗 رابط المشروع:"
        echo "   https://github.com/miiiso1983/Max-Upd"
        echo ""
        echo "📱 لتجربة التطبيق المحمول:"
        echo "   cd flutter_sales_rep_app"
        echo "   flutter run -d chrome --web-port=8080"
        echo ""
        echo "🌐 لتشغيل الخادم:"
        echo "   php artisan serve"
        echo ""
        echo "✅ بيانات الدخول:"
        echo "   البريد: admin@maxcon-erp.com"
        echo "   كلمة المرور: MaxCon@2025"
        echo ""
    else
        echo "❌ فشل في رفع المشروع. يرجى التحقق من الاتصال بالإنترنت والصلاحيات."
    fi
fi

echo "✨ انتهى!"
