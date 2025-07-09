#!/bin/bash

# =============================================================================
# MaxCon SaaS - Quick Update Deployment Script
# =============================================================================

echo "🚀 نشر التحديثات الجديدة على Cloudways..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[✅]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[⚠️]${NC} $1"
}

print_error() {
    echo -e "${RED}[❌]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[📋]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "هذا السكريبت يجب تشغيله من مجلد Laravel الرئيسي"
    exit 1
fi

print_step "بدء عملية النشر..."

# Step 1: Clear all caches
print_step "1. مسح الكاش..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
print_status "تم مسح جميع ملفات الكاش"

# Step 2: Update composer dependencies (if needed)
print_step "2. تحديث التبعيات..."
if [ -f "composer.lock" ]; then
    composer install --optimize-autoloader --no-dev
    print_status "تم تحديث تبعيات Composer"
else
    print_warning "ملف composer.lock غير موجود، سيتم تخطي تحديث التبعيات"
fi

# Step 3: Build assets (if npm is available)
print_step "3. بناء الأصول..."
if command -v npm &> /dev/null; then
    if [ -f "package.json" ]; then
        npm install --production
        npm run build
        print_status "تم بناء الأصول بنجاح"
    else
        print_warning "ملف package.json غير موجود"
    fi
else
    print_warning "npm غير متوفر، سيتم تخطي بناء الأصول"
fi

# Step 4: Run migrations (if any new ones)
print_step "4. تشغيل المايجريشن الجديدة..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    print_status "تم تشغيل المايجريشن بنجاح"
else
    print_warning "لا توجد مايجريشن جديدة أو حدث خطأ"
fi

# Step 5: Optimize for production
print_step "5. تحسين الأداء للإنتاج..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_status "تم تحسين الأداء"

# Step 6: Set proper permissions
print_step "6. تعيين الأذونات..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache
print_status "تم تعيين الأذونات"

# Step 7: Create storage link if not exists
print_step "7. فحص رابط التخزين..."
if [ ! -L public/storage ]; then
    php artisan storage:link
    print_status "تم إنشاء رابط التخزين"
else
    print_status "رابط التخزين موجود"
fi

echo
echo "=============================================="
echo "🎉 تم نشر التحديثات بنجاح!"
echo "=============================================="
echo
echo "📋 ما تم تنفيذه:"
echo "• مسح جميع ملفات الكاش"
echo "• تحديث التبعيات"
echo "• بناء الأصول"
echo "• تشغيل المايجريشن"
echo "• تحسين الأداء"
echo "• تعيين الأذونات"
echo "• فحص رابط التخزين"
echo
echo "🔧 الخطوات التالية:"
echo "1. اختبر الموقع: $(grep APP_URL .env 2>/dev/null | cut -d '=' -f2 || echo 'تحقق من رابط الموقع')"
echo "2. تحقق من سجلات الأخطاء إذا واجهت مشاكل"
echo "3. اختبر جميع الوظائف المحدثة"
echo
echo "✅ النشر مكتمل!"
