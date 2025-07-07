#!/bin/bash

# =============================================================================
# MaxCon SaaS - Cloudways Emergency Fix Script
# =============================================================================

echo "🚨 إصلاح مشاكل Cloudways..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Step 1: Check and create .env file
print_step "1. فحص ملف .env..."

if [ ! -f .env ]; then
    if [ -f .env.cloudways ]; then
        cp .env.cloudways .env
        print_status "تم نسخ .env من .env.cloudways"
    elif [ -f .env.example ]; then
        cp .env.example .env
        print_status "تم نسخ .env من .env.example"
    else
        print_error "لا يوجد ملف .env أو .env.example!"
        exit 1
    fi
else
    print_status "ملف .env موجود"
fi

# Step 2: Generate APP_KEY if missing
print_step "2. فحص APP_KEY..."

if ! grep -q "APP_KEY=base64:" .env; then
    print_status "توليد APP_KEY..."
    php artisan key:generate --force
    if [ $? -eq 0 ]; then
        print_status "تم توليد APP_KEY بنجاح"
    else
        print_error "فشل في توليد APP_KEY"
        # Manual key generation
        APP_KEY=$(openssl rand -base64 32)
        sed -i "s/APP_KEY=.*/APP_KEY=base64:$APP_KEY/" .env
        print_status "تم توليد APP_KEY يدوياً"
    fi
else
    print_status "APP_KEY موجود"
fi

# Step 3: Set proper environment
print_step "3. تعيين بيئة الإنتاج..."

sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
print_status "تم تعيين بيئة الإنتاج"

# Step 4: Clear all caches
print_step "4. تنظيف الذاكرة المؤقتة..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

print_status "تم تنظيف جميع الذاكرة المؤقتة"

# Step 5: Set proper permissions
print_step "5. تعيين الأذونات..."

chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework

print_status "تم تعيين الأذونات"

# Step 6: Optimize for production
print_step "6. تحسين للإنتاج..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "تم تحسين التطبيق للإنتاج"

# Step 7: Create storage link
print_step "7. إنشاء رابط التخزين..."

if [ ! -L public/storage ]; then
    php artisan storage:link
    print_status "تم إنشاء رابط التخزين"
else
    print_status "رابط التخزين موجود"
fi

# Step 8: Test application
print_step "8. اختبار التطبيق..."

php artisan about > /dev/null 2>&1
if [ $? -eq 0 ]; then
    print_status "التطبيق يعمل بشكل صحيح ✓"
else
    print_warning "قد تكون هناك مشاكل في التطبيق"
fi

echo
echo "=============================================="
echo "🎉 تم إصلاح مشاكل Cloudways!"
echo "=============================================="
echo
echo "📋 الخطوات التالية:"
echo "1. تحقق من إعدادات قاعدة البيانات في .env"
echo "2. قم بتشغيل: php artisan migrate --force"
echo "3. اختبر الموقع في المتصفح"
echo
echo "🔧 إذا استمرت المشاكل:"
echo "1. تحقق من PHP extensions في Cloudways"
echo "2. تأكد من إعدادات قاعدة البيانات"
echo "3. راجع سجلات الأخطاء"
echo
echo "✅ الإصلاح مكتمل!"
