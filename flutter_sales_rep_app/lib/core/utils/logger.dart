import 'dart:developer' as developer;
import 'package:flutter/foundation.dart';

/// Logger utility for the application
class Logger {
  static const String _defaultTag = 'MaxConSalesRep';
  
  /// Log debug message
  static void debug(String message, {String? tag, Object? error, StackTrace? stackTrace}) {
    if (kDebugMode) {
      developer.log(
        message,
        name: tag ?? _defaultTag,
        level: 500, // Debug level
        error: error,
        stackTrace: stackTrace,
      );
    }
  }

  /// Log info message
  static void info(String message, {String? tag, Object? error, StackTrace? stackTrace}) {
    developer.log(
      message,
      name: tag ?? _defaultTag,
      level: 800, // Info level
      error: error,
      stackTrace: stackTrace,
    );
  }

  /// Log warning message
  static void warning(String message, {String? tag, Object? error, StackTrace? stackTrace}) {
    developer.log(
      message,
      name: tag ?? _defaultTag,
      level: 900, // Warning level
      error: error,
      stackTrace: stackTrace,
    );
  }

  /// Log error message
  static void error(String message, {String? tag, Object? error, StackTrace? stackTrace}) {
    developer.log(
      message,
      name: tag ?? _defaultTag,
      level: 1000, // Error level
      error: error,
      stackTrace: stackTrace,
    );
  }

  /// Log API request
  static void apiRequest(String method, String url, {Map<String, dynamic>? data, Map<String, String>? headers}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('🌐 API Request: $method $url');
      
      if (headers != null && headers.isNotEmpty) {
        buffer.writeln('📋 Headers: $headers');
      }
      
      if (data != null && data.isNotEmpty) {
        buffer.writeln('📦 Data: $data');
      }
      
      debug(buffer.toString(), tag: 'API');
    }
  }

  /// Log API response
  static void apiResponse(int statusCode, String url, {dynamic data, Duration? duration}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('📡 API Response: $statusCode $url');
      
      if (duration != null) {
        buffer.writeln('⏱️ Duration: ${duration.inMilliseconds}ms');
      }
      
      if (data != null) {
        buffer.writeln('📦 Response: $data');
      }
      
      if (statusCode >= 200 && statusCode < 300) {
        info(buffer.toString(), tag: 'API');
      } else if (statusCode >= 400) {
        error(buffer.toString(), tag: 'API');
      } else {
        warning(buffer.toString(), tag: 'API');
      }
    }
  }

  /// Log API error
  static void apiError(String url, Object error, {StackTrace? stackTrace}) {
    Logger.error(
      '❌ API Error: $url - $error',
      tag: 'API',
      error: error,
      stackTrace: stackTrace,
    );
  }

  /// Log navigation
  static void navigation(String from, String to) {
    if (kDebugMode) {
      info('🧭 Navigation: $from → $to', tag: 'Navigation');
    }
  }

  /// Log user action
  static void userAction(String action, {Map<String, dynamic>? data}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('👤 User Action: $action');
      
      if (data != null && data.isNotEmpty) {
        buffer.writeln('📊 Data: $data');
      }
      
      info(buffer.toString(), tag: 'UserAction');
    }
  }

  /// Log database operation
  static void database(String operation, {String? table, Map<String, dynamic>? data}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('🗄️ Database: $operation');
      
      if (table != null) {
        buffer.writeln('📋 Table: $table');
      }
      
      if (data != null && data.isNotEmpty) {
        buffer.writeln('📦 Data: $data');
      }
      
      debug(buffer.toString(), tag: 'Database');
    }
  }

  /// Log sync operation
  static void sync(String operation, {String? status, int? count}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('🔄 Sync: $operation');
      
      if (status != null) {
        buffer.writeln('📊 Status: $status');
      }
      
      if (count != null) {
        buffer.writeln('🔢 Count: $count');
      }
      
      info(buffer.toString(), tag: 'Sync');
    }
  }

  /// Log location update
  static void location(double latitude, double longitude, {double? accuracy}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('📍 Location: $latitude, $longitude');
      
      if (accuracy != null) {
        buffer.writeln('🎯 Accuracy: ${accuracy.toStringAsFixed(2)}m');
      }
      
      debug(buffer.toString(), tag: 'Location');
    }
  }

  /// Log performance metric
  static void performance(String operation, Duration duration, {Map<String, dynamic>? metrics}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('⚡ Performance: $operation');
      buffer.writeln('⏱️ Duration: ${duration.inMilliseconds}ms');
      
      if (metrics != null && metrics.isNotEmpty) {
        buffer.writeln('📊 Metrics: $metrics');
      }
      
      info(buffer.toString(), tag: 'Performance');
    }
  }

  /// Log memory usage
  static void memory(String context, {int? usedMemory, int? freeMemory}) {
    if (kDebugMode) {
      final buffer = StringBuffer();
      buffer.writeln('💾 Memory: $context');
      
      if (usedMemory != null) {
        buffer.writeln('📊 Used: ${(usedMemory / 1024 / 1024).toStringAsFixed(2)} MB');
      }
      
      if (freeMemory != null) {
        buffer.writeln('🆓 Free: ${(freeMemory / 1024 / 1024).toStringAsFixed(2)} MB');
      }
      
      debug(buffer.toString(), tag: 'Memory');
    }
  }
}
