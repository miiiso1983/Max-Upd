/// API Response model for handling server responses
class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final Map<String, dynamic>? errors;
  final int? statusCode;
  final Map<String, dynamic>? meta;

  const ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
    this.statusCode,
    this.meta,
  });

  /// Create a successful response
  factory ApiResponse.success({
    required String message,
    T? data,
    int? statusCode,
    Map<String, dynamic>? meta,
  }) {
    return ApiResponse<T>(
      success: true,
      message: message,
      data: data,
      statusCode: statusCode ?? 200,
      meta: meta,
    );
  }

  /// Create an error response
  factory ApiResponse.error({
    required String message,
    Map<String, dynamic>? errors,
    int? statusCode,
    T? data,
  }) {
    return ApiResponse<T>(
      success: false,
      message: message,
      errors: errors,
      statusCode: statusCode ?? 400,
      data: data,
    );
  }

  /// Create response from JSON
  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic)? fromJsonT,
  ) {
    return ApiResponse<T>(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null && fromJsonT != null
          ? fromJsonT(json['data'])
          : json['data'],
      errors: json['errors'],
      statusCode: json['status_code'] ?? json['statusCode'],
      meta: json['meta'],
    );
  }

  /// Convert to JSON
  Map<String, dynamic> toJson() {
    return {
      'success': success,
      'message': message,
      'data': data,
      'errors': errors,
      'status_code': statusCode,
      'meta': meta,
    };
  }

  /// Check if response has data
  bool get hasData => data != null;

  /// Check if response has errors
  bool get hasErrors => errors != null && errors!.isNotEmpty;

  /// Get error message from errors map
  String get errorMessage {
    if (!hasErrors) return message;
    
    final errorList = <String>[];
    errors!.forEach((key, value) {
      if (value is List) {
        errorList.addAll(value.map((e) => e.toString()));
      } else {
        errorList.add(value.toString());
      }
    });
    
    return errorList.join(', ');
  }

  /// Check if response is successful with data
  bool get isSuccessWithData => success && hasData;

  /// Copy with new values
  ApiResponse<T> copyWith({
    bool? success,
    String? message,
    T? data,
    Map<String, dynamic>? errors,
    int? statusCode,
    Map<String, dynamic>? meta,
  }) {
    return ApiResponse<T>(
      success: success ?? this.success,
      message: message ?? this.message,
      data: data ?? this.data,
      errors: errors ?? this.errors,
      statusCode: statusCode ?? this.statusCode,
      meta: meta ?? this.meta,
    );
  }

  @override
  String toString() {
    return 'ApiResponse{success: $success, message: $message, data: $data, errors: $errors, statusCode: $statusCode}';
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ApiResponse &&
          runtimeType == other.runtimeType &&
          success == other.success &&
          message == other.message &&
          data == other.data &&
          errors == other.errors &&
          statusCode == other.statusCode;

  @override
  int get hashCode =>
      success.hashCode ^
      message.hashCode ^
      data.hashCode ^
      errors.hashCode ^
      statusCode.hashCode;
}

/// Pagination meta data
class PaginationMeta {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;
  final int from;
  final int to;

  const PaginationMeta({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
    required this.from,
    required this.to,
  });

  factory PaginationMeta.fromJson(Map<String, dynamic> json) {
    return PaginationMeta(
      currentPage: json['current_page'] ?? 1,
      lastPage: json['last_page'] ?? 1,
      perPage: json['per_page'] ?? 15,
      total: json['total'] ?? 0,
      from: json['from'] ?? 0,
      to: json['to'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'current_page': currentPage,
      'last_page': lastPage,
      'per_page': perPage,
      'total': total,
      'from': from,
      'to': to,
    };
  }

  bool get hasNextPage => currentPage < lastPage;
  bool get hasPreviousPage => currentPage > 1;
}

/// Paginated API Response
class PaginatedApiResponse<T> extends ApiResponse<List<T>> {
  final PaginationMeta pagination;

  const PaginatedApiResponse({
    required super.success,
    required super.message,
    required this.pagination,
    super.data,
    super.errors,
    super.statusCode,
  });

  factory PaginatedApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Map<String, dynamic>) fromJsonT,
  ) {
    final dataList = json['data'] as List?;
    final items = dataList?.map((item) => fromJsonT(item)).toList();

    return PaginatedApiResponse<T>(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: items,
      pagination: PaginationMeta.fromJson(json['meta'] ?? {}),
      errors: json['errors'],
      statusCode: json['status_code'] ?? json['statusCode'],
    );
  }
}
