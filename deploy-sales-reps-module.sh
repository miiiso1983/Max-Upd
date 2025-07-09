#!/bin/bash

# 🚀 Sales Representatives Module Deployment Script
# This script deploys the Sales Representatives Management Module to production

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to backup database
backup_database() {
    print_status "Creating database backup..."
    
    if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
        print_error "Database credentials not found in .env file"
        exit 1
    fi
    
    BACKUP_FILE="backup_sales_reps_$(date +%Y%m%d_%H%M%S).sql"
    
    if command_exists mysqldump; then
        mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE"
        print_success "Database backup created: $BACKUP_FILE"
    else
        print_warning "mysqldump not found. Skipping database backup."
    fi
}

# Function to run migrations
run_migrations() {
    print_status "Running Sales Representatives module migrations..."
    
    # Array of migration files in order
    migrations=(
        "database/migrations/2025_07_09_120000_create_sales_representatives_table.php"
        "database/migrations/2025_07_09_120001_create_territories_table.php"
        "database/migrations/2025_07_09_120002_create_rep_territory_assignments_table.php"
        "database/migrations/2025_07_09_120003_create_rep_customer_assignments_table.php"
        "database/migrations/2025_07_09_120004_create_customer_visits_table.php"
        "database/migrations/2025_07_09_120005_create_rep_tasks_table.php"
        "database/migrations/2025_07_09_120006_create_rep_performance_metrics_table.php"
        "database/migrations/2025_07_09_120007_create_rep_location_tracking_table.php"
        "database/migrations/2025_07_09_120008_add_sales_rep_fields_to_existing_tables.php"
    )
    
    # Run each migration individually
    for migration in "${migrations[@]}"; do
        if [ -f "$migration" ]; then
            print_status "Running migration: $(basename $migration)"
            php artisan migrate --path="$migration" --force
        else
            print_warning "Migration file not found: $migration"
        fi
    done
    
    print_success "All migrations completed successfully"
}

# Function to seed sample data
seed_sample_data() {
    print_status "Seeding sample data..."
    
    if [ -f "database/seeders/SalesRepresentativeSeeder.php" ]; then
        php artisan db:seed --class=SalesRepresentativeSeeder --force
        print_success "Sample data seeded successfully"
    else
        print_warning "SalesRepresentativeSeeder not found. Skipping sample data."
    fi
}

# Function to optimize application
optimize_application() {
    print_status "Optimizing application for production..."
    
    # Clear all caches
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Clear permission cache if spatie/laravel-permission is installed
    if php artisan list | grep -q "permission:cache-reset"; then
        php artisan permission:cache-reset
    fi
    
    # Rebuild optimized caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Optimize composer autoloader
    if command_exists composer; then
        composer install --optimize-autoloader --no-dev --no-interaction
    fi
    
    print_success "Application optimized successfully"
}

# Function to set permissions
set_permissions() {
    print_status "Setting proper file permissions..."
    
    # Set directory permissions
    chmod -R 755 storage bootstrap/cache 2>/dev/null || true
    chmod -R 775 storage/logs storage/framework 2>/dev/null || true
    
    # Create storage link if it doesn't exist
    if [ ! -L "public/storage" ]; then
        php artisan storage:link
    fi
    
    print_success "File permissions set successfully"
}

# Function to verify deployment
verify_deployment() {
    print_status "Verifying deployment..."
    
    # Check if tables exist
    print_status "Checking database tables..."
    
    # Test database connection
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection successful';" 2>/dev/null; then
        print_success "Database connection verified"
    else
        print_error "Database connection failed"
        return 1
    fi
    
    # Check if sales representatives table exists
    if php artisan tinker --execute="Schema::hasTable('sales_representatives') ? print('✓') : print('✗');" 2>/dev/null | grep -q "✓"; then
        print_success "Sales representatives table exists"
    else
        print_error "Sales representatives table not found"
        return 1
    fi
    
    # Check if sample data exists
    REP_COUNT=$(php artisan tinker --execute="echo App\Modules\SalesReps\Models\SalesRepresentative::count();" 2>/dev/null | tail -1)
    if [ "$REP_COUNT" -gt 0 ] 2>/dev/null; then
        print_success "Sample data found: $REP_COUNT sales representatives"
    else
        print_warning "No sample data found"
    fi
    
    print_success "Deployment verification completed"
}

# Main deployment function
main() {
    print_status "🚀 Starting Sales Representatives Module Deployment"
    print_status "=================================================="
    
    # Check if we're in a Laravel project
    if [ ! -f "artisan" ]; then
        print_error "This doesn't appear to be a Laravel project (artisan file not found)"
        exit 1
    fi
    
    # Load environment variables
    if [ -f ".env" ]; then
        export $(grep -v '^#' .env | xargs)
        print_success "Environment variables loaded"
    else
        print_error ".env file not found"
        exit 1
    fi
    
    # Check PHP version
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_status "PHP Version: $PHP_VERSION"
    
    # Backup database (optional, comment out if not needed)
    read -p "Do you want to backup the database before deployment? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        backup_database
    fi
    
    # Run deployment steps
    run_migrations
    seed_sample_data
    optimize_application
    set_permissions
    verify_deployment
    
    print_success "🎉 Sales Representatives Module deployment completed successfully!"
    print_status "=================================================="
    print_status "Next steps:"
    print_status "1. Test the web dashboard at: ${APP_URL}/representatives"
    print_status "2. Test API endpoints at: ${APP_URL}/api/sales-reps"
    print_status "3. Configure mobile app with production URLs"
    print_status "4. Train users on the new functionality"
    print_status "5. Monitor application logs for any issues"
}

# Run main function
main "$@"
