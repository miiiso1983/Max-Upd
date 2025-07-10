import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

// Core imports
import '../models/api_response.dart';
import '../utils/logger.dart';
import 'storage_service.dart';

/// API Service for MaxCon Sales Rep App
/// Uses HTTP package with ApiResponse wrapper for better compatibility
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

  /// Initialize the API service
  void init() {
    Logger.info('API Service initialized with base URL: $baseUrl');
  }

  // Authentication methods
  Future<ApiResponse<Map<String, dynamic>>> login({
    required String email,
    required String password,
    String? deviceId,
  }) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.post(
        Uri.parse('$baseUrl/mobile/login'),
        headers: headers,
        body: json.encode({
          'email': email,
          'password': password,
          'device_id': deviceId,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تسجيل الدخول',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> refreshToken() async {
    try {
      final refreshToken = StorageService.instance.getRefreshToken();
      if (refreshToken == null) {
        return ApiResponse.error(message: 'لا يوجد refresh token متاح');
      }

      final headers = Map<String, String>.from(defaultHeaders);
      final response = await http.post(
        Uri.parse('$baseUrl/mobile/refresh'),
        headers: headers,
        body: json.encode({
          'refresh_token': refreshToken,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تحديث الرمز المميز',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<void>> logout() async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      await http.post(
        Uri.parse('$baseUrl/mobile/logout'),
        headers: headers,
      );

      return const ApiResponse(success: true, message: 'تم تسجيل الخروج بنجاح');
    } catch (e) {
      return _handleError(e);
    }
  }

  // Profile methods
  Future<ApiResponse<Map<String, dynamic>>> getProfile() async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.get(
        Uri.parse('$baseUrl/mobile/profile'),
        headers: headers,
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في جلب الملف الشخصي',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> updateProfile(Map<String, dynamic> profileData) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.put(
        Uri.parse('$baseUrl/mobile/profile'),
        headers: headers,
        body: json.encode(profileData),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تحديث الملف الشخصي',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Customer methods
  Future<ApiResponse<List<Map<String, dynamic>>>> getCustomers({
    int? salesRepId,
    Map<String, dynamic>? filters,
  }) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      String endpoint = '/customers';
      if (salesRepId != null) {
        endpoint = '/sales-reps/$salesRepId/customers';
      }

      final queryParams = <String, String>{};
      if (filters != null) {
        filters.forEach((key, value) {
          queryParams[key] = value.toString();
        });
      }

      final uri = Uri.parse('$baseUrl$endpoint').replace(queryParameters: queryParams);
      final response = await http.get(uri, headers: headers);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) {
          if (data is List) {
            return data.cast<Map<String, dynamic>>();
          }
          return <Map<String, dynamic>>[];
        });
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في جلب العملاء',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Visit methods
  Future<ApiResponse<List<Map<String, dynamic>>>> getVisits({
    int? salesRepId,
    String? startDate,
    String? endDate,
    String? status,
  }) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final queryParams = <String, String>{};
      if (salesRepId != null) queryParams['sales_rep_id'] = salesRepId.toString();
      if (startDate != null) queryParams['start_date'] = startDate;
      if (endDate != null) queryParams['end_date'] = endDate;
      if (status != null) queryParams['status'] = status;

      final uri = Uri.parse('$baseUrl/visits').replace(queryParameters: queryParams);
      final response = await http.get(uri, headers: headers);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) {
          if (data is List) {
            return data.cast<Map<String, dynamic>>();
          }
          return <Map<String, dynamic>>[];
        });
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في جلب الزيارات',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> createVisit(Map<String, dynamic> visitData) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.post(
        Uri.parse('$baseUrl/visits'),
        headers: headers,
        body: json.encode(visitData),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 201 || response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في إنشاء الزيارة',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> checkInVisit(
    int visitId,
    Map<String, dynamic> checkInData
  ) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.post(
        Uri.parse('$baseUrl/visits/$visitId/check-in'),
        headers: headers,
        body: json.encode(checkInData),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تسجيل الدخول للزيارة',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> checkOutVisit(
    int visitId,
    Map<String, dynamic> checkOutData
  ) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.post(
        Uri.parse('$baseUrl/visits/$visitId/check-out'),
        headers: headers,
        body: json.encode(checkOutData),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تسجيل الخروج من الزيارة',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Task methods
  Future<ApiResponse<List<Map<String, dynamic>>>> getTasks({
    int? salesRepId,
    String? status,
    String? type,
  }) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final queryParams = <String, String>{};
      if (salesRepId != null) queryParams['sales_rep_id'] = salesRepId.toString();
      if (status != null) queryParams['status'] = status;
      if (type != null) queryParams['type'] = type;

      final uri = Uri.parse('$baseUrl/my-tasks').replace(queryParameters: queryParams);
      final response = await http.get(uri, headers: headers);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) {
          if (data is List) {
            return data.cast<Map<String, dynamic>>();
          }
          return <Map<String, dynamic>>[];
        });
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في جلب المهام',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> updateTaskStatus(
    int taskId,
    String status,
    {Map<String, dynamic>? additionalData}
  ) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final requestData = <String, dynamic>{'status': status};
      if (additionalData != null) requestData.addAll(additionalData);

      final response = await http.put(
        Uri.parse('$baseUrl/tasks/$taskId'),
        headers: headers,
        body: json.encode(requestData),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تحديث حالة المهمة',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Location tracking
  Future<ApiResponse<void>> updateLocation({
    required double latitude,
    required double longitude,
    int? accuracy,
    String? activityType,
  }) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final salesRepId = StorageService.instance.getSalesRepId();
      if (salesRepId == null) {
        return ApiResponse.error(message: 'لم يتم العثور على معرف المندوب');
      }

      final response = await http.post(
        Uri.parse('$baseUrl/sales-reps/$salesRepId/location'),
        headers: headers,
        body: json.encode({
          'latitude': latitude,
          'longitude': longitude,
          'accuracy': accuracy,
          'activity_type': activityType,
        }),
      );

      if (response.statusCode == 200) {
        return const ApiResponse(success: true, message: 'تم تحديث الموقع');
      } else {
        final data = json.decode(response.body);
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تحديث الموقع',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Sync methods
  Future<ApiResponse<Map<String, dynamic>>> syncVisits(List<Map<String, dynamic>> visits) async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.post(
        Uri.parse('$baseUrl/visits-sync'),
        headers: headers,
        body: json.encode({'visits': visits}),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في مزامنة الزيارات',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> getOfflineData() async {
    try {
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.get(
        Uri.parse('$baseUrl/mobile/offline-data'),
        headers: headers,
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في جلب البيانات المحلية',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // File upload (simplified - requires multipart support)
  Future<ApiResponse<List<String>>> uploadFiles(
    String endpoint,
    List<File> files
  ) async {
    try {
      // Note: This is a simplified implementation
      // For full multipart support, consider using dio or http_parser
      return ApiResponse.error(
        message: 'رفع الملفات غير مدعوم في هذا الإصدار المبسط',
      );
    } catch (e) {
      return _handleError(e);
    }
  }

  // Connectivity check
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

  // Demo login method for testing
  Future<ApiResponse<Map<String, dynamic>?>> loginDemo() async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/mobile/login'),
        headers: defaultHeaders,
        body: json.encode({
          'email': 'admin@maxcon-erp.com',
          'password': 'MaxCon@2025',
          'device_id': 'flutter_demo_device',
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        final result = ApiResponse.fromJson(data, (data) => data as Map<String, dynamic>);
        if (result.success && result.data != null) {
          final token = result.data!['access_token'];
          if (token != null) {
            await StorageService.instance.saveAccessToken(token);
          }
        }
        return result;
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في تسجيل الدخول التجريبي',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Sales Representatives methods
  Future<ApiResponse<List<Map<String, dynamic>>>> getSalesReps({
    int page = 1,
    int perPage = 20,
    String? search,
  }) async {
    try {
      // First try to get a demo token if we don't have one
      final token = StorageService.instance.getAccessToken();
      if (token == null) {
        final loginResult = await loginDemo();
        if (!loginResult.success) {
          return ApiResponse.error(
            message: 'فشل في الحصول على token للاختبار'
          );
        }
      }

      final headers = Map<String, String>.from(defaultHeaders);
      final currentToken = StorageService.instance.getAccessToken();
      if (currentToken != null) {
        headers['Authorization'] = 'Bearer $currentToken';
      }

      final queryParams = <String, String>{
        'page': page.toString(),
        'per_page': perPage.toString(),
      };
      if (search != null) queryParams['search'] = search;

      final uri = Uri.parse('$baseUrl/sales-reps').replace(queryParameters: queryParams);
      final response = await http.get(uri, headers: headers);

      final data = json.decode(response.body);

      if (response.statusCode == 200) {
        return ApiResponse.fromJson(data, (data) {
          if (data is List) {
            return data.cast<Map<String, dynamic>>();
          }
          return <Map<String, dynamic>>[];
        });
      } else {
        return ApiResponse.error(
          message: data['message'] ?? 'فشل في جلب المندوبين',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Test API connection
  Future<ApiResponse<Map<String, dynamic>>> testConnection() async {
    try {
      // Try to login first
      final loginResult = await loginDemo();
      if (!loginResult.success) {
        return ApiResponse.error(
          message: loginResult.message,
          data: <String, dynamic>{}
        );
      }

      // Then test the sales-reps endpoint
      final headers = Map<String, String>.from(defaultHeaders);
      final token = StorageService.instance.getAccessToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }

      final response = await http.get(
        Uri.parse('$baseUrl/sales-reps?page=1&per_page=1'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        return ApiResponse.success(
          message: 'تم الاتصال بنجاح',
          data: {
            'status': 'connected',
            'endpoint': '/sales-reps',
            'response_code': response.statusCode,
          }
        );
      } else {
        return ApiResponse.error(
          message: 'فشل في اختبار الاتصال',
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return _handleError(e);
    }
  }

  // Error handling
  ApiResponse<T> _handleError<T>(dynamic error) {
    String message = 'حدث خطأ غير متوقع';

    if (error is SocketException) {
      message = 'لا يوجد اتصال بالإنترنت';
    } else if (error is FormatException) {
      message = 'خطأ في تنسيق البيانات';
    } else if (error is Exception) {
      message = error.toString();
    }

    Logger.error('API Error: $error');
    return ApiResponse.error(message: message);
  }
}
