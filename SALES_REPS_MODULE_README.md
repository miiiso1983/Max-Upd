# 🧠 Sales Representatives Management Module

## 📋 Overview

The Sales Representatives Management Module is a comprehensive solution for managing sales teams, tracking performance, and enabling mobile field operations. This module integrates seamlessly with the existing MaxCon ERP system and provides both web-based management tools and a Flutter mobile application for field representatives.

## ✨ Features

### 🖥️ Web Dashboard Features
- **Complete CRUD Operations** for sales representatives
- **Territory Management** with GPS boundaries
- **Customer Assignment** and relationship tracking
- **Performance Analytics** with KPI dashboards
- **Real-time Location Tracking** with map visualization
- **Task Management** and assignment system
- **Bulk Operations** for efficient management
- **Excel Import/Export** functionality
- **Responsive Design** for all devices

### 📱 Mobile App Features
- **Offline Capabilities** with data synchronization
- **GPS Visit Tracking** with location verification
- **Customer Visit Management** with check-in/check-out
- **Order Creation** during visits
- **Payment Collection** with receipt generation
- **Task Management** with status updates
- **Photo Capture** and document upload
- **Route Optimization** suggestions
- **Arabic/RTL Language Support**

## 🗄️ Database Schema

### Core Tables
1. **`sales_representatives`** - Main representative data
2. **`territories`** - Geographic territory definitions
3. **`rep_territory_assignments`** - Territory assignments
4. **`rep_customer_assignments`** - Customer assignments
5. **`customer_visits`** - Visit tracking and details
6. **`rep_tasks`** - Task management
7. **`rep_performance_metrics`** - Performance data
8. **`rep_location_tracking`** - GPS tracking data

### Extended Tables
- **`customers`** - Added GPS coordinates and visit preferences
- **`sales_orders`** - Added sales rep tracking and commission
- **`invoices`** - Added commission tracking
- **`payments`** - Added collection tracking

## 🚀 Installation & Setup

### 1. Database Migration
```bash
# Run the migrations
php artisan migrate

# Seed sample data
php artisan db:seed --class=SalesRepresentativeSeeder
```

### 2. Permissions Setup
The seeder automatically creates the following roles and permissions:
- **Sales Representative Role**: Limited permissions for field operations
- **Sales Manager Role**: Full management permissions

### 3. API Routes
All API routes are automatically registered via `routes/api_sales_reps.php`

### 4. Web Routes
Web routes are integrated into the main application under `/representatives`

## 📱 Mobile App Setup

### Prerequisites
- Flutter 3.10.0 or higher
- Dart SDK 3.0.0 or higher
- Android Studio / Xcode for device testing

### Installation
```bash
cd flutter_sales_rep_app
flutter pub get
flutter run
```

### Configuration
Update `lib/core/config/app_config.dart` with your API endpoints:
```dart
class AppConfig {
  static const String apiBaseUrl = 'https://your-domain.com/api';
  static const String appVersion = '1.0.0';
}
```

## 🔧 API Endpoints

### Authentication
- `POST /api/mobile/login` - Sales rep login
- `POST /api/mobile/refresh` - Token refresh
- `POST /api/mobile/logout` - Logout

### Sales Representatives
- `GET /api/sales-reps` - List representatives
- `POST /api/sales-reps` - Create representative
- `GET /api/sales-reps/{id}` - Get representative details
- `PUT /api/sales-reps/{id}` - Update representative
- `DELETE /api/sales-reps/{id}` - Delete representative

### Visits
- `GET /api/visits` - List visits
- `POST /api/visits` - Create visit
- `POST /api/visits/{id}/check-in` - Check in to visit
- `POST /api/visits/{id}/check-out` - Check out from visit
- `GET /api/visits-today` - Today's visits
- `POST /api/visits-sync` - Sync offline visits

### Tasks
- `GET /api/my-tasks` - Get assigned tasks
- `PUT /api/tasks/{id}` - Update task status
- `POST /api/tasks/{id}/complete` - Complete task

### Location Tracking
- `POST /api/sales-reps/{id}/location` - Update location
- `GET /api/reports/location-history` - Location history

## 🎯 Usage Examples

### Creating a Sales Representative
```php
use App\Modules\SalesReps\Services\SalesRepService;

$salesRepService = new SalesRepService();
$salesRep = $salesRepService->create([
    'name' => 'Ahmed Al-Mahmoud',
    'name_ar' => 'أحمد المحمود',
    'email' => 'ahmed@example.com',
    'phone' => '+964-770-123-4567',
    'governorate' => 'بغداد',
    'monthly_target' => 5000000.00,
    'territory_ids' => [1, 2],
    'customer_ids' => [10, 11, 12],
]);
```

### Mobile API Login
```dart
final apiService = ApiService.instance;
final response = await apiService.login(
  email: 'ahmed@example.com',
  password: 'password123',
  deviceId: 'device_unique_id',
);

if (response.success) {
  final token = response.data['access_token'];
  await StorageService.instance.saveAccessToken(token);
}
```

### Creating a Visit
```dart
final visitData = {
  'customer_id': 123,
  'visit_date': DateTime.now().toIso8601String(),
  'visit_type': 'scheduled',
  'visit_purpose': 'Product presentation and order collection',
};

final response = await apiService.createVisit(visitData);
```

## 📊 Performance Metrics

The system automatically tracks:
- **Visit Completion Rate**
- **Order Conversion Rate**
- **Collection Efficiency**
- **Territory Coverage**
- **Target Achievement**
- **Customer Satisfaction**

## 🔐 Security Features

- **Role-based Access Control** using Spatie Laravel Permission
- **API Authentication** with Laravel Sanctum
- **Location Verification** for visit authenticity
- **Offline Data Encryption** in mobile app
- **Multi-tenant Data Isolation**

## 🌍 Localization

- **Arabic (RTL) Support** throughout the application
- **Iraqi Dinar Currency** formatting
- **Local Date/Time** formats
- **Arabic Number** formatting

## 🧪 Testing

### Backend Testing
```bash
# Run feature tests
php artisan test --filter SalesRepresentative

# Run API tests
php artisan test tests/Feature/Api/SalesRepTest.php
```

### Mobile App Testing
```bash
cd flutter_sales_rep_app
flutter test
```

## 📈 Reporting & Analytics

### Available Reports
- **Performance Summary** - KPI overview
- **Visit Reports** - Detailed visit analysis
- **Sales Reports** - Revenue and order tracking
- **Territory Coverage** - Geographic analysis
- **Commission Reports** - Earnings tracking

### Export Formats
- **Excel** with multiple sheets
- **PDF** with Arabic support
- **CSV** for data analysis

## 🔄 Data Synchronization

The mobile app supports:
- **Offline Operation** with local SQLite storage
- **Automatic Sync** when connection is restored
- **Conflict Resolution** for concurrent edits
- **Incremental Updates** for efficiency

## 🛠️ Troubleshooting

### Common Issues

1. **Migration Errors**
   ```bash
   php artisan migrate:rollback
   php artisan migrate
   ```

2. **Permission Issues**
   ```bash
   php artisan permission:cache-reset
   php artisan db:seed --class=SalesRepresentativeSeeder
   ```

3. **Mobile App Build Issues**
   ```bash
   flutter clean
   flutter pub get
   flutter run
   ```

## 📞 Support

For technical support or feature requests, please contact the development team or create an issue in the project repository.

## 🔄 Version History

- **v1.0.0** - Initial release with core functionality
- **v1.1.0** - Enhanced mobile app features (planned)
- **v1.2.0** - Advanced analytics and reporting (planned)

---

**Built with ❤️ for MaxCon ERP System**
