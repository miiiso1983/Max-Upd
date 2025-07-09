# 🚀 Sales Representatives Module - Production Deployment Guide

## 📋 **Pre-Deployment Checklist**

### 🔧 **1. Server Environment Setup**

#### Database Configuration
```bash
# Update .env file with production database credentials
DB_CONNECTION=mysql
DB_HOST=your_production_host
DB_PORT=3306
DB_DATABASE=your_production_database
DB_USERNAME=your_production_user
DB_PASSWORD=your_secure_password
```

#### Required PHP Extensions
```bash
# Ensure these extensions are installed
php -m | grep -E "(pdo_mysql|mbstring|openssl|tokenizer|xml|ctype|json|bcmath|gd)"
```

### 🗄️ **2. Database Migration Commands**

#### Backup Current Database
```bash
# Create backup before migration
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

#### Run Sales Representatives Migrations
```bash
# Navigate to project directory
cd /path/to/your/project

# Run specific migrations for Sales Representatives module
php artisan migrate --path=database/migrations/2025_07_09_120000_create_sales_representatives_table.php
php artisan migrate --path=database/migrations/2025_07_09_120001_create_territories_table.php
php artisan migrate --path=database/migrations/2025_07_09_120002_create_rep_territory_assignments_table.php
php artisan migrate --path=database/migrations/2025_07_09_120003_create_rep_customer_assignments_table.php
php artisan migrate --path=database/migrations/2025_07_09_120004_create_customer_visits_table.php
php artisan migrate --path=database/migrations/2025_07_09_120005_create_rep_tasks_table.php
php artisan migrate --path=database/migrations/2025_07_09_120006_create_rep_performance_metrics_table.php
php artisan migrate --path=database/migrations/2025_07_09_120007_create_rep_location_tracking_table.php
php artisan migrate --path=database/migrations/2025_07_09_120008_add_sales_rep_fields_to_existing_tables.php

# Or run all pending migrations
php artisan migrate --force
```

### 🌱 **3. Seed Sample Data**

```bash
# Seed sales representatives sample data
php artisan db:seed --class=SalesRepresentativeSeeder --force

# Verify seeding was successful
php artisan tinker
>>> App\Modules\SalesReps\Models\SalesRepresentative::count()
>>> App\Modules\SalesReps\Models\Territory::count()
```

### 🔧 **4. Cache and Optimization**

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan permission:cache-reset

# Rebuild optimized caches for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework
```

### 📱 **5. Mobile App Configuration**

#### Update API Base URL
```dart
// flutter_sales_rep_app/lib/core/config/app_config.dart
class AppConfig {
  static const String apiBaseUrl = 'https://your-production-domain.com/api';
  static const String appVersion = '1.0.0';
  static const bool isProduction = true;
}
```

#### Build Mobile App
```bash
cd flutter_sales_rep_app

# Get dependencies
flutter pub get

# Build for Android
flutter build apk --release

# Build for iOS (on macOS)
flutter build ios --release
```

## 🧪 **Testing & Verification**

### 🔍 **1. Database Verification**
```sql
-- Check if all tables were created
SHOW TABLES LIKE '%sales%';
SHOW TABLES LIKE '%rep%';
SHOW TABLES LIKE '%territory%';
SHOW TABLES LIKE '%visit%';

-- Verify sample data
SELECT COUNT(*) FROM sales_representatives;
SELECT COUNT(*) FROM territories;
SELECT COUNT(*) FROM rep_territory_assignments;
```

### 🌐 **2. Web Interface Testing**
```bash
# Test routes are accessible
curl -I https://your-domain.com/representatives
curl -I https://your-domain.com/dashboard

# Check for any errors in logs
tail -f storage/logs/laravel.log
```

### 📱 **3. API Endpoint Testing**
```bash
# Test authentication endpoint
curl -X POST https://your-domain.com/api/mobile/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test sales reps endpoint
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-domain.com/api/sales-reps
```

## 🔐 **Security Configuration**

### 🛡️ **1. Environment Security**
```bash
# Set secure APP_KEY
php artisan key:generate --force

# Update JWT secret
php artisan jwt:secret --force

# Set proper environment
APP_ENV=production
APP_DEBUG=false
```

### 🔒 **2. Database Security**
```sql
-- Create dedicated database user for the application
CREATE USER 'maxcon_app'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON maxcon_database.* TO 'maxcon_app'@'localhost';
FLUSH PRIVILEGES;
```

### 🌐 **3. Web Server Configuration**

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your/project/public;
    
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 📊 **Performance Monitoring**

### 🔍 **1. Database Performance**
```sql
-- Monitor slow queries
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check table sizes
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'your_database_name'
ORDER BY (data_length + index_length) DESC;
```

### 📈 **2. Application Monitoring**
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Monitor system resources
htop
df -h
free -m
```

## 🚨 **Rollback Plan**

### 🔄 **If Issues Occur:**

#### 1. Database Rollback
```bash
# Restore from backup
mysql -u username -p database_name < backup_file.sql
```

#### 2. Code Rollback
```bash
# Revert to previous commit
git log --oneline -10
git checkout PREVIOUS_COMMIT_HASH

# Clear caches
php artisan config:clear
php artisan cache:clear
```

#### 3. Emergency Contacts
- **Technical Lead**: [Your Contact]
- **Database Admin**: [Your Contact]
- **DevOps Engineer**: [Your Contact]

## ✅ **Post-Deployment Verification**

### 🎯 **Success Criteria:**
- [ ] All migrations completed successfully
- [ ] Sample data seeded properly
- [ ] Web dashboard accessible
- [ ] API endpoints responding
- [ ] Mobile app connects successfully
- [ ] No errors in application logs
- [ ] Performance metrics within acceptable range

### 📋 **User Acceptance Testing:**
- [ ] Sales manager can create representatives
- [ ] Representatives can be assigned territories
- [ ] Visit tracking works correctly
- [ ] Mobile app syncs data properly
- [ ] Performance reports generate correctly

## 📞 **Support & Maintenance**

### 🔧 **Regular Maintenance Tasks:**
```bash
# Weekly tasks
php artisan queue:restart
php artisan cache:clear

# Monthly tasks
php artisan telescope:prune
composer update --no-dev

# Backup schedule
0 2 * * * mysqldump -u user -p database > backup_$(date +\%Y\%m\%d).sql
```

---

## 🎉 **Deployment Complete!**

Once all steps are completed successfully, the Sales Representatives Management Module will be fully operational in production with:

- ✅ Complete database schema
- ✅ Web dashboard functionality
- ✅ Mobile app integration
- ✅ Performance monitoring
- ✅ Security measures
- ✅ Backup procedures

**The system is now ready for live use by your sales team!** 🚀
