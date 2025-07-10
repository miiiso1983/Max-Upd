import 'dart:convert';
import 'package:http/http.dart' as http;

/// Simplified API Service for MaxCon Sales Rep App
/// Uses basic HTTP package for better compatibility
class ApiService {
  static final ApiService _instance = ApiService._internal();
  static ApiService get instance => _instance;
  ApiService._internal();

  // Base URL for API calls
  static const String baseUrl = 'https://phpstack-1486247-5676575.cloudwaysapps.com/api';
  
  // Default headers
  static const Map<String, String> defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  /// Test API connection
  Future<Map<String, dynamic>> testConnection() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/test/sales-reps'),
        headers: defaultHeaders,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': 'اتصال ناجح بـ API',
          'data': data,
          'status_code': response.statusCode,
        };
      } else {
        return {
          'success': false,
          'message': 'فشل الاتصال بـ API. كود الخطأ: ${response.statusCode}',
          'status_code': response.statusCode,
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'خطأ في الاتصال: $e',
        'error': e.toString(),
      };
    }
  }

  /// Login to the system
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/mobile/login'),
        headers: defaultHeaders,
        body: json.encode({
          'email': email,
          'password': password,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return {
          'success': true,
          'message': 'تم تسجيل الدخول بنجاح',
          'data': data['data'],
          'access_token': data['data']?['access_token'],
          'user': data['data']?['user'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'فشل في تسجيل الدخول',
          'errors': data['errors'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'خطأ في الاتصال: $e',
        'error': e.toString(),
      };
    }
  }

  /// Get sales representatives list
  Future<Map<String, dynamic>> getSalesReps() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/sales-reps'),
        headers: defaultHeaders,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': 'تم جلب البيانات بنجاح',
          'data': data,
        };
      } else {
        return {
          'success': false,
          'message': 'فشل في جلب البيانات. كود الخطأ: ${response.statusCode}',
          'status_code': response.statusCode,
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'خطأ في الاتصال: $e',
        'error': e.toString(),
      };
    }
  }

  /// Get customers list
  Future<Map<String, dynamic>> getCustomers() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/customers'),
        headers: defaultHeaders,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': 'تم جلب العملاء بنجاح',
          'data': data,
        };
      } else {
        return {
          'success': false,
          'message': 'فشل في جلب العملاء. كود الخطأ: ${response.statusCode}',
          'status_code': response.statusCode,
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'خطأ في الاتصال: $e',
        'error': e.toString(),
      };
    }
  }

  /// Get visits list
  Future<Map<String, dynamic>> getVisits() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/visits'),
        headers: defaultHeaders,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': 'تم جلب الزيارات بنجاح',
          'data': data,
        };
      } else {
        return {
          'success': false,
          'message': 'فشل في جلب الزيارات. كود الخطأ: ${response.statusCode}',
          'status_code': response.statusCode,
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'خطأ في الاتصال: $e',
        'error': e.toString(),
      };
    }
  }

  /// Create a new visit
  Future<Map<String, dynamic>> createVisit({
    required int customerId,
    required String visitDate,
    String? notes,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/visits'),
        headers: defaultHeaders,
        body: json.encode({
          'customer_id': customerId,
          'visit_date': visitDate,
          'notes': notes,
          'status': 'planned',
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 201 && data['success'] == true) {
        return {
          'success': true,
          'message': 'تم إنشاء الزيارة بنجاح',
          'data': data['data'],
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'فشل في إنشاء الزيارة',
          'errors': data['errors'],
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'خطأ في الاتصال: $e',
        'error': e.toString(),
      };
    }
  }

  /// Check internet connectivity
  Future<bool> hasInternetConnection() async {
    try {
      final response = await http.get(
        Uri.parse('https://www.google.com'),
      ).timeout(const Duration(seconds: 5));
      
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }

  /// Get app configuration
  Future<Map<String, dynamic>> getAppConfig() async {
    return {
      'success': true,
      'message': 'إعدادات التطبيق',
      'data': {
        'app_name': 'MaxCon Sales Rep',
        'version': '1.0.0',
        'api_base_url': baseUrl,
        'features': {
          'offline_mode': true,
          'gps_tracking': true,
          'whatsapp_integration': true,
          'arabic_support': true,
        },
        'demo_credentials': {
          'email': 'admin@maxcon-erp.com',
          'password': 'MaxCon@2025',
        },
      },
    };
  }
}
