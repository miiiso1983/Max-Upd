# 🔧 حل مشكلة Terminal في VS Code

## ❌ المشكلة
```
The terminal process failed to launch: A native exception occurred during launch (posix_spawnp failed.).
```

## 🎯 الحلول المجربة

### **1. إعادة تشغيل VS Code**
```bash
# أغلق VS Code تماماً
# أعد فتحه من جديد
```

### **2. تغيير Terminal الافتراضي**
1. اذهب إلى `VS Code Settings` (Cmd + ,)
2. ابحث عن `terminal.integrated.defaultProfile.osx`
3. غيره إلى:
   - `bash`
   - أو `zsh`
   - أو `/bin/bash`

### **3. إعادة تعيين Terminal Settings**
```json
// في settings.json
{
  "terminal.integrated.defaultProfile.osx": "bash",
  "terminal.integrated.shell.osx": "/bin/bash",
  "terminal.integrated.profiles.osx": {
    "bash": {
      "path": "/bin/bash"
    },
    "zsh": {
      "path": "/bin/zsh"
    }
  }
}
```

### **4. استخدام Terminal خارجي**
```bash
# افتح Terminal من macOS مباشرة
# اذهب إلى مجلد المشروع
cd "/Users/mustafaaljaf/Documents/augment-projects/MaxCon SaaS"

# تشغيل الأوامر المطلوبة
git init
git remote add origin https://github.com/miiiso1983/Max-Upd.git
git add .
git commit -m "🎉 MaxCon ERP: نظام شامل مع تطبيق مندوبي المبيعات"
git push -u origin main
```

### **5. تشغيل Flutter خارجياً**
```bash
# في Terminal خارجي
cd "/Users/mustafaaljaf/Documents/augment-projects/MaxCon SaaS/flutter_sales_rep_app"
flutter run -d chrome --web-port=8080
```

### **6. إعادة تثبيت VS Code**
```bash
# إذا لم تنجح الحلول السابقة
# احذف VS Code وأعد تثبيته
```

## 🚀 البدائل المتاحة

### **استخدام GitHub Desktop**
1. حمل GitHub Desktop
2. اربطه بحسابك
3. اختر المجلد المحلي
4. ارفع المشروع بسهولة

### **استخدام Terminal المدمج في macOS**
```bash
# افتح Finder
# اذهب إلى مجلد المشروع
# انقر بالزر الأيمن
# اختر "New Terminal at Folder"
```

### **استخدام Git GUI**
```bash
# تثبيت Git GUI
brew install git-gui

# تشغيله في مجلد المشروع
git gui
```

## ✅ التحقق من نجاح الحل

### **اختبار Terminal**
```bash
# جرب هذا الأمر
echo "Hello World"

# إذا عمل، فالمشكلة محلولة
```

### **اختبار Git**
```bash
git --version
# يجب أن يظهر رقم الإصدار
```

### **اختبار Flutter**
```bash
flutter doctor
# يجب أن يظهر حالة Flutter
```

## 🎯 الخطوات التالية

### **1. رفع المشروع على GitHub**
```bash
cd "/Users/mustafaaljaf/Documents/augment-projects/MaxCon SaaS"
git init
git remote add origin https://github.com/miiiso1983/Max-Upd.git
git add .
git commit -m "🎉 MaxCon ERP: نظام شامل مع تطبيق مندوبي المبيعات"
git push -u origin main
```

### **2. تشغيل التطبيق**
```bash
# Laravel
php artisan serve

# Flutter
cd flutter_sales_rep_app
flutter run -d chrome --web-port=8080
```

### **3. اختبار API**
- افتح التطبيق على: http://localhost:8080
- اضغط "اختبار الاتصال بـ API"
- يجب أن تظهر رسالة نجاح

## 📞 إذا استمرت المشكلة

### **معلومات النظام المطلوبة:**
```bash
# تحقق من إصدار macOS
sw_vers

# تحقق من PATH
echo $PATH

# تحقق من Shell
echo $SHELL

# تحقق من صلاحيات المجلد
ls -la "/Users/mustafaaljaf/Documents/augment-projects/MaxCon SaaS"
```

### **إعادة تعيين VS Code**
```bash
# احذف إعدادات VS Code
rm -rf ~/Library/Application\ Support/Code/User/settings.json

# أعد تشغيل VS Code
```

---

**💡 نصيحة: استخدم Terminal خارجي كحل مؤقت حتى يتم إصلاح VS Code**
