#!/bin/bash

# =============================================================================
# MaxCon SaaS - Cloudways Deployment Script
# =============================================================================

echo "🚀 بدء نشر MaxCon SaaS على Cloudways..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
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

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_error "لا تشغل هذا السكريبت كـ root"
    exit 1
fi

# Step 1: Environment Setup
print_step "1. إعداد البيئة..."

# Copy environment file
if [ ! -f .env ]; then
    if [ -f .env.cloudways ]; then
        cp .env.cloudways .env
        print_status "تم نسخ ملف .env من .env.cloudways"
    else
        print_error "ملف .env.cloudways غير موجود!"
        exit 1
    fi
else
    print_warning "ملف .env موجود بالفعل، سيتم تخطي النسخ"
fi

# Step 2: Install Dependencies
print_step "2. تثبيت التبعيات..."

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    print_error "Composer غير مثبت!"
    exit 1
fi

# Install PHP dependencies
print_status "تثبيت تبعيات PHP..."
composer install --optimize-autoloader --no-dev

if [ $? -ne 0 ]; then
    print_error "فشل في تثبيت تبعيات PHP"
    exit 1
fi

# Check if npm is installed
if command -v npm &> /dev/null; then
    print_status "تثبيت تبعيات JavaScript..."
    npm install
    npm run build
    
    if [ $? -ne 0 ]; then
        print_warning "فشل في بناء الأصول، لكن يمكن المتابعة"
    fi
else
    print_warning "npm غير مثبت، سيتم تخطي بناء الأصول"
fi

# Step 3: Generate Application Key
print_step "3. توليد مفتاح التطبيق..."

if grep -q "APP_KEY=base64:" .env; then
    print_warning "مفتاح التطبيق موجود بالفعل"
else
    php artisan key:generate --force
    print_status "تم توليد مفتاح التطبيق"
fi

# Step 4: Set Permissions
print_step "4. تعيين الأذونات..."

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache

print_status "تم تعيين أذونات الملفات"

# Step 5: Database Setup
print_step "5. إعداد قاعدة البيانات..."

# Check database connection
php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    print_status "الاتصال بقاعدة البيانات ناجح"
    
    # Run migrations
    print_status "تشغيل المايجريشن..."
    php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        print_status "تم تشغيل المايجريشن بنجاح"
        
        # Run seeders
        print_status "تشغيل السيدرز..."
        php artisan db:seed --force
        
        if [ $? -eq 0 ]; then
            print_status "تم تشغيل السيدرز بنجاح"
        else
            print_warning "فشل في تشغيل السيدرز، يمكن تشغيلها لاحقاً"
        fi
    else
        print_error "فشل في تشغيل المايجريشن"
        exit 1
    fi
else
    print_error "فشل في الاتصال بقاعدة البيانات. تحقق من إعدادات .env"
    exit 1
fi

# Step 6: Storage Link
print_step "6. إنشاء رابط التخزين..."

if [ ! -L public/storage ]; then
    php artisan storage:link
    print_status "تم إنشاء رابط التخزين"
else
    print_warning "رابط التخزين موجود بالفعل"
fi

# Step 7: Cache Optimization
print_step "7. تحسين الذاكرة المؤقتة..."

# Clear all caches first
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "تم تحسين الذاكرة المؤقتة"

# Step 8: Create Admin User
print_step "8. إنشاء مستخدم المدير..."

read -p "هل تريد إنشاء مستخدم مدير؟ (y/n): " create_admin

if [ "$create_admin" = "y" ] || [ "$create_admin" = "Y" ]; then
    read -p "اسم المدير: " admin_name
    read -p "بريد المدير الإلكتروني: " admin_email
    read -s -p "كلمة مرور المدير: " admin_password
    echo
    
    php artisan tinker --execute="
    \$user = App\Models\User::create([
        'name' => '$admin_name',
        'email' => '$admin_email',
        'password' => Hash::make('$admin_password'),
        'email_verified_at' => now(),
        'is_active' => true
    ]);
    \$user->assignRole('super_admin');
    echo 'تم إنشاء مستخدم المدير بنجاح';
    "
    
    print_status "تم إنشاء مستخدم المدير"
fi

# Step 9: Security Check
print_step "9. فحص الأمان..."

# Check if APP_DEBUG is false
if grep -q "APP_DEBUG=false" .env; then
    print_status "وضع التطوير مُعطّل ✓"
else
    print_warning "تحذير: وضع التطوير مُفعّل! قم بتعيين APP_DEBUG=false"
fi

# Check if APP_ENV is production
if grep -q "APP_ENV=production" .env; then
    print_status "بيئة الإنتاج مُفعّلة ✓"
else
    print_warning "تحذير: البيئة ليست production! قم بتعيين APP_ENV=production"
fi

# Step 10: Final Checks
print_step "10. الفحوصات النهائية..."

# Check if application is accessible
if php artisan route:list > /dev/null 2>&1; then
    print_status "التطبيق يعمل بشكل صحيح ✓"
else
    print_error "مشكلة في التطبيق!"
    exit 1
fi

# Display important information
echo
echo "=============================================="
echo "🎉 تم نشر MaxCon SaaS بنجاح!"
echo "=============================================="
echo
echo "📋 معلومات مهمة:"
echo "• رابط التطبيق: $(grep APP_URL .env | cut -d '=' -f2)"
echo "• بيئة التشغيل: $(grep APP_ENV .env | cut -d '=' -f2)"
echo "• قاعدة البيانات: $(grep DB_DATABASE .env | cut -d '=' -f2)"
echo
echo "🔧 الخطوات التالية:"
echo "1. تحقق من إعدادات DNS"
echo "2. فعّل SSL Certificate"
echo "3. اختبر جميع الوظائف"
echo "4. إعداد النسخ الاحتياطي"
echo "5. مراقبة السجلات"
echo
echo "📚 للمزيد من المعلومات، راجع:"
echo "• CLOUDWAYS_DEPLOYMENT_GUIDE.md"
echo "• https://github.com/miiiso1983/MaxCon-SaaS"
echo
echo "✅ النشر مكتمل!"

# Create deployment log
echo "$(date): MaxCon SaaS deployed successfully" >> deployment.log

exit 0
