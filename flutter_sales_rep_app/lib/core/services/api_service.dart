import 'dart:convert';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:connectivity_plus/connectivity_plus.dart';

import '../config/app_config.dart';
import '../models/api_response.dart';
import '../utils/logger.dart';
import 'storage_service.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  static ApiService get instance => _instance;
  ApiService._internal();

  late Dio _dio;
  final StorageService _storage = StorageService.instance;

  void init() {
    _dio = Dio(BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    ));

    // Add interceptors
    _dio.interceptors.add(_AuthInterceptor());
    _dio.interceptors.add(_LoggingInterceptor());
    _dio.interceptors.add(_ErrorInterceptor());
  }

  // Authentication methods
  Future<ApiResponse<Map<String, dynamic>>> login({
    required String email,
    required String password,
    String? deviceId,
  }) async {
    try {
      final response = await _dio.post('/mobile/login', data: {
        'email': email,
        'password': password,
        'device_id': deviceId,
      });

      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> refreshToken() async {
    try {
      final refreshToken = await _storage.getRefreshToken();
      if (refreshToken == null) {
        throw Exception('No refresh token available');
      }

      final response = await _dio.post('/mobile/refresh', data: {
        'refresh_token': refreshToken,
      });

      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<void>> logout() async {
    try {
      await _dio.post('/mobile/logout');
      return ApiResponse(success: true, message: 'تم تسجيل الخروج بنجاح');
    } catch (e) {
      return _handleError(e);
    }
  }

  // Profile methods
  Future<ApiResponse<Map<String, dynamic>>> getProfile() async {
    try {
      final response = await _dio.get('/mobile/profile');
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> updateProfile(Map<String, dynamic> data) async {
    try {
      final response = await _dio.put('/mobile/profile', data: data);
      return ApiResponse.fromJson(response.data);
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
      final queryParams = <String, dynamic>{};
      if (salesRepId != null) queryParams['sales_rep_id'] = salesRepId;
      if (filters != null) queryParams.addAll(filters);

      final response = await _dio.get('/sales-reps/$salesRepId/customers', 
        queryParameters: queryParams);
      
      return ApiResponse.fromJson(response.data);
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
      final queryParams = <String, dynamic>{};
      if (salesRepId != null) queryParams['sales_rep_id'] = salesRepId;
      if (startDate != null) queryParams['start_date'] = startDate;
      if (endDate != null) queryParams['end_date'] = endDate;
      if (status != null) queryParams['status'] = status;

      final response = await _dio.get('/visits', queryParameters: queryParams);
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> createVisit(Map<String, dynamic> visitData) async {
    try {
      final response = await _dio.post('/visits', data: visitData);
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> checkInVisit(
    int visitId, 
    Map<String, dynamic> checkInData
  ) async {
    try {
      final response = await _dio.post('/visits/$visitId/check-in', data: checkInData);
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> checkOutVisit(
    int visitId, 
    Map<String, dynamic> checkOutData
  ) async {
    try {
      final response = await _dio.post('/visits/$visitId/check-out', data: checkOutData);
      return ApiResponse.fromJson(response.data);
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
      final queryParams = <String, dynamic>{};
      if (salesRepId != null) queryParams['sales_rep_id'] = salesRepId;
      if (status != null) queryParams['status'] = status;
      if (type != null) queryParams['type'] = type;

      final response = await _dio.get('/my-tasks', queryParameters: queryParams);
      return ApiResponse.fromJson(response.data);
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
      final data = {'status': status};
      if (additionalData != null) data.addAll(additionalData);

      final response = await _dio.put('/tasks/$taskId', data: data);
      return ApiResponse.fromJson(response.data);
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
      final salesRepId = await _storage.getSalesRepId();
      if (salesRepId == null) throw Exception('Sales rep ID not found');

      await _dio.post('/sales-reps/$salesRepId/location', data: {
        'latitude': latitude,
        'longitude': longitude,
        'accuracy': accuracy,
        'activity_type': activityType,
      });

      return ApiResponse(success: true, message: 'تم تحديث الموقع');
    } catch (e) {
      return _handleError(e);
    }
  }

  // Sync methods
  Future<ApiResponse<Map<String, dynamic>>> syncVisits(List<Map<String, dynamic>> visits) async {
    try {
      final response = await _dio.post('/visits-sync', data: {'visits': visits});
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> getOfflineData() async {
    try {
      final response = await _dio.get('/mobile/offline-data');
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  // File upload
  Future<ApiResponse<List<String>>> uploadFiles(
    String endpoint, 
    List<File> files
  ) async {
    try {
      final formData = FormData();
      
      for (int i = 0; i < files.length; i++) {
        formData.files.add(MapEntry(
          'files[]',
          await MultipartFile.fromFile(files[i].path),
        ));
      }

      final response = await _dio.post(endpoint, data: formData);
      return ApiResponse.fromJson(response.data);
    } catch (e) {
      return _handleError(e);
    }
  }

  // Connectivity check
  Future<bool> hasInternetConnection() async {
    final connectivityResult = await Connectivity().checkConnectivity();
    return connectivityResult != ConnectivityResult.none;
  }

  // Error handling
  ApiResponse<T> _handleError<T>(dynamic error) {
    String message = 'حدث خطأ غير متوقع';
    
    if (error is DioException) {
      switch (error.type) {
        case DioExceptionType.connectionTimeout:
        case DioExceptionType.receiveTimeout:
          message = 'انتهت مهلة الاتصال';
          break;
        case DioExceptionType.connectionError:
          message = 'لا يوجد اتصال بالإنترنت';
          break;
        case DioExceptionType.badResponse:
          if (error.response?.data != null) {
            final data = error.response!.data;
            if (data is Map && data.containsKey('message')) {
              message = data['message'];
            }
          }
          break;
        default:
          message = 'حدث خطأ في الشبكة';
      }
    }

    Logger.error('API Error: $error');
    return ApiResponse(success: false, message: message);
  }
}

// Interceptors
class _AuthInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    final token = await StorageService.instance.getAccessToken();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    
    final deviceId = await StorageService.instance.getDeviceId();
    if (deviceId != null) {
      options.headers['Device-ID'] = deviceId;
    }
    
    options.headers['App-Version'] = AppConfig.appVersion;
    
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      // Token expired, try to refresh
      final refreshResult = await ApiService.instance.refreshToken();
      if (refreshResult.success && refreshResult.data != null) {
        final newToken = refreshResult.data!['access_token'];
        await StorageService.instance.saveAccessToken(newToken);
        
        // Retry the original request
        final options = err.requestOptions;
        options.headers['Authorization'] = 'Bearer $newToken';
        
        try {
          final response = await Dio().fetch(options);
          handler.resolve(response);
          return;
        } catch (e) {
          // If retry fails, continue with original error
        }
      }
      
      // Refresh failed, logout user
      await StorageService.instance.clearAuthData();
    }
    
    handler.next(err);
  }
}

class _LoggingInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    Logger.info('API Request: ${options.method} ${options.path}');
    handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    Logger.info('API Response: ${response.statusCode} ${response.requestOptions.path}');
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    Logger.error('API Error: ${err.message}');
    handler.next(err);
  }
}

class _ErrorInterceptor extends Interceptor {
  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    // Handle specific error cases
    if (err.response?.statusCode == 422) {
      // Validation errors
      final data = err.response?.data;
      if (data is Map && data.containsKey('errors')) {
        // Handle validation errors
      }
    }
    
    handler.next(err);
  }
}
