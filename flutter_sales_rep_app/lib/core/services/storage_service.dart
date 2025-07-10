import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/logger.dart';

/// Storage service for managing local data persistence
class StorageService {
  static StorageService? _instance;
  static StorageService get instance => _instance ??= StorageService._();
  
  StorageService._();
  
  SharedPreferences? _prefs;
  
  /// Initialize the storage service
  Future<void> init() async {
    try {
      _prefs = await SharedPreferences.getInstance();
      Logger.info('Storage service initialized successfully');
    } catch (e, stackTrace) {
      Logger.error('Failed to initialize storage service', error: e, stackTrace: stackTrace);
      rethrow;
    }
  }
  
  /// Check if storage is initialized
  bool get isInitialized => _prefs != null;
  
  /// Ensure storage is initialized
  void _ensureInitialized() {
    if (!isInitialized) {
      throw Exception('StorageService not initialized. Call init() first.');
    }
  }
  
  // String operations
  
  /// Save string value
  Future<bool> setString(String key, String value) async {
    _ensureInitialized();
    try {
      final result = await _prefs!.setString(key, value);
      Logger.debug('Saved string: $key');
      return result;
    } catch (e, stackTrace) {
      Logger.error('Failed to save string: $key', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Get string value
  String? getString(String key, {String? defaultValue}) {
    _ensureInitialized();
    try {
      final value = _prefs!.getString(key) ?? defaultValue;
      Logger.debug('Retrieved string: $key = $value');
      return value;
    } catch (e, stackTrace) {
      Logger.error('Failed to get string: $key', error: e, stackTrace: stackTrace);
      return defaultValue;
    }
  }
  
  // Integer operations
  
  /// Save integer value
  Future<bool> setInt(String key, int value) async {
    _ensureInitialized();
    try {
      final result = await _prefs!.setInt(key, value);
      Logger.debug('Saved int: $key = $value');
      return result;
    } catch (e, stackTrace) {
      Logger.error('Failed to save int: $key', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Get integer value
  int? getInt(String key, {int? defaultValue}) {
    _ensureInitialized();
    try {
      final value = _prefs!.getInt(key) ?? defaultValue;
      Logger.debug('Retrieved int: $key = $value');
      return value;
    } catch (e, stackTrace) {
      Logger.error('Failed to get int: $key', error: e, stackTrace: stackTrace);
      return defaultValue;
    }
  }
  
  // Boolean operations
  
  /// Save boolean value
  Future<bool> setBool(String key, bool value) async {
    _ensureInitialized();
    try {
      final result = await _prefs!.setBool(key, value);
      Logger.debug('Saved bool: $key = $value');
      return result;
    } catch (e, stackTrace) {
      Logger.error('Failed to save bool: $key', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Get boolean value
  bool? getBool(String key, {bool? defaultValue}) {
    _ensureInitialized();
    try {
      final value = _prefs!.getBool(key) ?? defaultValue;
      Logger.debug('Retrieved bool: $key = $value');
      return value;
    } catch (e, stackTrace) {
      Logger.error('Failed to get bool: $key', error: e, stackTrace: stackTrace);
      return defaultValue;
    }
  }
  
  // Double operations
  
  /// Save double value
  Future<bool> setDouble(String key, double value) async {
    _ensureInitialized();
    try {
      final result = await _prefs!.setDouble(key, value);
      Logger.debug('Saved double: $key = $value');
      return result;
    } catch (e, stackTrace) {
      Logger.error('Failed to save double: $key', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Get double value
  double? getDouble(String key, {double? defaultValue}) {
    _ensureInitialized();
    try {
      final value = _prefs!.getDouble(key) ?? defaultValue;
      Logger.debug('Retrieved double: $key = $value');
      return value;
    } catch (e, stackTrace) {
      Logger.error('Failed to get double: $key', error: e, stackTrace: stackTrace);
      return defaultValue;
    }
  }
  
  // JSON operations
  
  /// Save JSON object
  Future<bool> setJson(String key, Map<String, dynamic> value) async {
    try {
      final jsonString = jsonEncode(value);
      return await setString(key, jsonString);
    } catch (e, stackTrace) {
      Logger.error('Failed to save JSON: $key', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Get JSON object
  Map<String, dynamic>? getJson(String key) {
    try {
      final jsonString = getString(key);
      if (jsonString == null) return null;
      return jsonDecode(jsonString) as Map<String, dynamic>;
    } catch (e, stackTrace) {
      Logger.error('Failed to get JSON: $key', error: e, stackTrace: stackTrace);
      return null;
    }
  }
  
  // List operations
  
  /// Save string list
  Future<bool> setStringList(String key, List<String> value) async {
    _ensureInitialized();
    try {
      final result = await _prefs!.setStringList(key, value);
      Logger.debug('Saved string list: $key (${value.length} items)');
      return result;
    } catch (e, stackTrace) {
      Logger.error('Failed to save string list: $key', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Get string list
  List<String>? getStringList(String key) {
    _ensureInitialized();
    try {
      final value = _prefs!.getStringList(key);
      Logger.debug('Retrieved string list: $key (${value?.length ?? 0} items)');
      return value;
    } catch (e, stackTrace) {
      Logger.error('Failed to get string list: $key', error: e, stackTrace: stackTrace);
      return null;
    }
  }
  
  // Utility operations
  
  /// Check if key exists
  bool containsKey(String key) {
    _ensureInitialized();
    return _prefs!.containsKey(key);
  }
  
  /// Remove key
  Future<bool> remove(String key) async {
    _ensureInitialized();
    try {
      final result = await _prefs!.remove(key);
      Logger.debug('Removed key: $key');
      return result;
    } catch (e, stackTrace) {
      Logger.error('Failed to remove key: $key', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Clear all data
  Future<bool> clear() async {
    _ensureInitialized();
    try {
      final result = await _prefs!.clear();
      Logger.info('Cleared all storage data');
      return result;
    } catch (e, stackTrace) {
      Logger.error('Failed to clear storage', error: e, stackTrace: stackTrace);
      return false;
    }
  }
  
  /// Get all keys
  Set<String> getKeys() {
    _ensureInitialized();
    return _prefs!.getKeys();
  }

  // Authentication helper methods

  /// Save access token
  Future<bool> saveAccessToken(String token) async {
    return await setString(StorageKeys.authToken, token);
  }

  /// Get access token
  String? getAccessToken() {
    return getString(StorageKeys.authToken);
  }

  /// Save refresh token
  Future<bool> saveRefreshToken(String token) async {
    return await setString(StorageKeys.refreshToken, token);
  }

  /// Get refresh token
  String? getRefreshToken() {
    return getString(StorageKeys.refreshToken);
  }

  /// Save sales rep ID
  Future<bool> saveSalesRepId(String id) async {
    return await setString(StorageKeys.salesRepId, id);
  }

  /// Get sales rep ID
  String? getSalesRepId() {
    return getString(StorageKeys.salesRepId);
  }

  /// Save device ID
  Future<bool> saveDeviceId(String id) async {
    return await setString(StorageKeys.deviceId, id);
  }

  /// Get device ID
  String? getDeviceId() {
    return getString(StorageKeys.deviceId);
  }

  /// Clear all authentication data
  Future<bool> clearAuthData() async {
    final results = await Future.wait<bool>([
      remove(StorageKeys.authToken),
      remove(StorageKeys.refreshToken),
      remove(StorageKeys.userId),
      remove(StorageKeys.userProfile),
      remove(StorageKeys.salesRepId),
    ]);

    return results.every((result) => result);
  }

  /// Check if user is logged in
  bool isLoggedIn() {
    final token = getAccessToken();
    return token != null && token.isNotEmpty;
  }
}

/// Storage keys constants
class StorageKeys {
  static const String authToken = 'auth_token';
  static const String refreshToken = 'refresh_token';
  static const String userId = 'user_id';
  static const String userProfile = 'user_profile';
  static const String tenantId = 'tenant_id';
  static const String salesRepId = 'sales_rep_id';
  static const String deviceId = 'device_id';
  static const String isFirstLaunch = 'is_first_launch';
  static const String lastSyncTime = 'last_sync_time';
  static const String offlineData = 'offline_data';
  static const String appSettings = 'app_settings';
  static const String cacheExpiry = 'cache_expiry';
}
