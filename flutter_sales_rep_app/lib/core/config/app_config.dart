class AppConfig {
  // 🌐 API Configuration
  static const String apiBaseUrl = 'https://phpstack-1486247-5676575.cloudwaysapps.com/api';
  static const String webBaseUrl = 'https://phpstack-1486247-5676575.cloudwaysapps.com';
  
  // 📱 App Information
  static const String appName = 'MaxCon Sales Rep';
  static const String appVersion = '1.0.0';
  static const int buildNumber = 1;
  
  // 🔧 Environment Configuration
  static const bool isProduction = true;
  static const bool enableLogging = !isProduction;
  static const bool enableCrashReporting = isProduction;
  
  // 🔐 Security Configuration
  static const int tokenExpirationHours = 24;
  static const int refreshTokenExpirationDays = 30;
  static const bool enableBiometricAuth = true;
  
  // 📍 Location Configuration
  static const double locationAccuracyThreshold = 10.0; // meters
  static const int locationUpdateIntervalSeconds = 30;
  static const double visitProximityThreshold = 100.0; // meters
  
  // 💾 Storage Configuration
  static const int maxOfflineDataDays = 7;
  static const int maxCacheSize = 100; // MB
  static const bool enableOfflineMode = true;
  
  // 📷 Media Configuration
  static const int maxImageSize = 2; // MB
  static const int imageQuality = 80; // 0-100
  static const bool enableImageCompression = true;
  
  // 🔄 Sync Configuration
  static const int syncIntervalMinutes = 15;
  static const int maxRetryAttempts = 3;
  static const int retryDelaySeconds = 5;
  
  // 🎨 UI Configuration
  static const bool enableDarkMode = false;
  static const String defaultLanguage = 'ar';
  static const bool enableRTL = true;
  
  // 📊 Analytics Configuration
  static const bool enableAnalytics = isProduction;
  static const String analyticsKey = 'your-analytics-key';
  
  // 🔔 Notification Configuration
  static const bool enablePushNotifications = true;
  static const String fcmSenderId = 'your-fcm-sender-id';
  
  // 🗺️ Maps Configuration
  static const String googleMapsApiKey = 'your-google-maps-api-key';
  static const double defaultMapZoom = 15.0;
  
  // 📱 Device Configuration
  static const List<String> supportedLanguages = ['ar', 'en'];
  static const String defaultCurrency = 'IQD';
  static const String defaultCountry = 'IQ';
  
  // 🔗 Deep Links
  static const String deepLinkScheme = 'maxcon';
  static const String deepLinkHost = 'salesrep';
  
  // 📋 Feature Flags
  static const bool enableVisitPhotos = true;
  static const bool enableOfflineOrders = true;
  static const bool enableLocationTracking = true;
  static const bool enablePerformanceMetrics = true;
  static const bool enableTaskManagement = true;
  static const bool enableCustomerNotes = true;
  
  // 🎯 Business Rules
  static const int maxVisitsPerDay = 20;
  static const int maxOrderAmount = 1000000; // IQD
  static const double maxDiscountPercentage = 10.0;
  static const int maxTasksPerRep = 50;
  
  // 📞 Support Configuration
  static const String supportEmail = 'support@maxcon-erp.com';
  static const String supportPhone = '+964-xxx-xxx-xxxx';
  static const String supportUrl = 'https://your-domain.com/support';
  
  // 🔄 Update Configuration
  static const bool enableAutoUpdate = true;
  static const String updateUrl = 'https://your-domain.com/app-updates';
  static const bool forceUpdateEnabled = false;
  
  // 🏢 Company Information
  static const String companyName = 'MaxCon ERP';
  static const String companyWebsite = 'https://maxcon-erp.com';
  static const String companyLogo = 'assets/images/company_logo.png';
  
  // 📱 Platform Specific
  static const String androidPackageName = 'com.maxcon.salesrep';
  static const String iosAppId = 'your-ios-app-id';
  static const String iosBundleId = 'com.maxcon.salesrep';
  
  // 🔐 Encryption Keys (These should be stored securely)
  static const String encryptionKey = 'your-encryption-key-32-chars';
  static const String apiKey = 'your-api-key';
  
  // 📊 Performance Monitoring
  static const bool enablePerformanceMonitoring = isProduction;
  static const int performanceThresholdMs = 1000;
  
  // 🎨 Theme Configuration
  static const String primaryColor = '#6f42c1';
  static const String secondaryColor = '#28a745';
  static const String accentColor = '#ffc107';
  static const String errorColor = '#dc3545';
  
  // 📱 App Store Configuration
  static const String playStoreUrl = 'https://play.google.com/store/apps/details?id=$androidPackageName';
  static const String appStoreUrl = 'https://apps.apple.com/app/id$iosAppId';
  
  // 🔄 API Endpoints
  static String get loginEndpoint => '$apiBaseUrl/mobile/login';
  static String get refreshEndpoint => '$apiBaseUrl/mobile/refresh';
  static String get profileEndpoint => '$apiBaseUrl/mobile/profile';
  static String get visitsEndpoint => '$apiBaseUrl/visits';
  static String get tasksEndpoint => '$apiBaseUrl/my-tasks';
  static String get customersEndpoint => '$apiBaseUrl/customers';
  static String get ordersEndpoint => '$apiBaseUrl/orders';
  static String get syncEndpoint => '$apiBaseUrl/sync';
  static String get locationEndpoint => '$apiBaseUrl/location';
  static String get offlineDataEndpoint => '$apiBaseUrl/mobile/offline-data';
  
  // 🔧 Development Configuration (only for development)
  static const bool enableDebugMode = !isProduction;
  static const bool enableMockData = false;
  static const bool skipAuthentication = false;
  static const bool enableTestMode = false;
  
  // 📋 Validation Rules
  static const int minPasswordLength = 8;
  static const int maxUsernameLength = 50;
  static const int maxNoteLength = 500;
  static const int maxFileSize = 10; // MB
  
  // 🕒 Timeout Configuration
  static const int connectionTimeoutSeconds = 30;
  static const int receiveTimeoutSeconds = 30;
  static const int sendTimeoutSeconds = 30;
  
  // 📱 Device Permissions
  static const List<String> requiredPermissions = [
    'camera',
    'location',
    'storage',
    'phone',
    'microphone',
  ];
  
  // 🎯 Default Values
  static const String defaultProfileImage = 'assets/images/default_profile.png';
  static const String defaultCompanyLogo = 'assets/images/default_company.png';
  static const String noImagePlaceholder = 'assets/images/no_image.png';
  
  // 📊 Pagination
  static const int defaultPageSize = 20;
  static const int maxPageSize = 100;
  
  // 🔄 Cache Configuration
  static const int imageCacheMaxAge = 7; // days
  static const int dataCacheMaxAge = 1; // days
  static const int apiCacheMaxAge = 5; // minutes
  
  // 🌐 Localization
  static const Map<String, String> supportedLocales = {
    'ar': 'العربية',
    'en': 'English',
  };
  
  // 📱 App Metadata
  static const String appDescription = 'MaxCon ERP Sales Representative Mobile Application';
  static const String appKeywords = 'sales, erp, crm, mobile, field, representatives';
  static const String appCategory = 'Business';
  
  // 🔐 Security Headers
  static const Map<String, String> defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'App-Version': appVersion,
  };
  
  // 🎯 Feature Availability by Role
  static const Map<String, List<String>> roleFeatures = {
    'sales_representative': [
      'view_visits',
      'create_visits',
      'view_customers',
      'create_orders',
      'collect_payments',
      'view_tasks',
      'update_location',
    ],
    'sales_manager': [
      'view_all_visits',
      'view_all_representatives',
      'assign_tasks',
      'view_reports',
      'manage_territories',
    ],
  };
}
