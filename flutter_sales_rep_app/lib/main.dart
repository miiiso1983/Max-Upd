import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';
import 'package:hive_flutter/hive_flutter.dart';

import 'core/config/app_config.dart';
import 'core/services/api_service.dart';
import 'core/services/auth_service.dart';
import 'core/services/location_service.dart';
import 'core/services/storage_service.dart';
import 'core/services/sync_service.dart';
import 'core/providers/auth_provider.dart';
import 'core/providers/visit_provider.dart';
import 'core/providers/customer_provider.dart';
import 'core/providers/task_provider.dart';
import 'core/providers/order_provider.dart';
import 'core/providers/sync_provider.dart';
import 'core/theme/app_theme.dart';
import 'core/utils/app_localizations.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/dashboard/screens/dashboard_screen.dart';
import 'features/splash/screens/splash_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Hive for local storage
  await Hive.initFlutter();
  
  // Initialize services
  await _initializeServices();
  
  // Set preferred orientations
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  
  runApp(const MaxConSalesRepApp());
}

Future<void> _initializeServices() async {
  // Initialize storage service
  await StorageService.instance.init();
  
  // Initialize API service
  ApiService.instance.init();
  
  // Initialize location service
  await LocationService.instance.init();
}

class MaxConSalesRepApp extends StatelessWidget {
  const MaxConSalesRepApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => VisitProvider()),
        ChangeNotifierProvider(create: (_) => CustomerProvider()),
        ChangeNotifierProvider(create: (_) => TaskProvider()),
        ChangeNotifierProvider(create: (_) => OrderProvider()),
        ChangeNotifierProvider(create: (_) => SyncProvider()),
      ],
      child: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          return MaterialApp(
            title: 'MaxCon Sales Rep',
            debugShowCheckedModeBanner: false,
            
            // Theme configuration
            theme: AppTheme.lightTheme,
            darkTheme: AppTheme.darkTheme,
            themeMode: ThemeMode.light,
            
            // Localization
            locale: const Locale('ar', 'IQ'),
            supportedLocales: const [
              Locale('ar', 'IQ'),
              Locale('en', 'US'),
            ],
            localizationsDelegates: const [
              AppLocalizations.delegate,
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            
            // Navigation
            home: const SplashScreen(),
            routes: {
              '/login': (context) => const LoginScreen(),
              '/dashboard': (context) => const DashboardScreen(),
            },
            
            // Route generation
            onGenerateRoute: (settings) {
              return _generateRoute(settings);
            },
            
            // Builder for RTL support
            builder: (context, child) {
              return Directionality(
                textDirection: TextDirection.rtl,
                child: child!,
              );
            },
          );
        },
      ),
    );
  }

  Route<dynamic>? _generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case '/':
        return MaterialPageRoute(builder: (_) => const SplashScreen());
      case '/login':
        return MaterialPageRoute(builder: (_) => const LoginScreen());
      case '/dashboard':
        return MaterialPageRoute(builder: (_) => const DashboardScreen());
      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            appBar: AppBar(title: const Text('صفحة غير موجودة')),
            body: const Center(
              child: Text('الصفحة المطلوبة غير موجودة'),
            ),
          ),
        );
    }
  }
}
