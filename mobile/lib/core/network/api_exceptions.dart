import 'dart:io';

class ApiException implements Exception {
  final String message;
  final int? statusCode;

  ApiException(this.message, {this.statusCode});

  factory ApiException.fromDioError(dynamic error) {
    if (error is SocketException) {
      return ApiException('No internet connection');
    }
    return ApiException('Something went wrong');
  }

  @override
  String toString() => message;
}
