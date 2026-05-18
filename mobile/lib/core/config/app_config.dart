import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppConfig {
  AppConfig._();

  static String get baseUrl => const String.fromEnvironment(
        'API_BASE_URL',
        defaultValue: 'http://localhost:8000/api',
      );

  static Duration get connectTimeout => const Duration(seconds: 15);
  static Duration get receiveTimeout => const Duration(seconds: 30);

  static late SharedPreferences prefs;

  static Future<void> init() async {
    prefs = await SharedPreferences.getInstance();
  }

  static String? get token => prefs.getString('auth_token');
  static set token(String? value) {
    if (value == null) {
      prefs.remove('auth_token');
    } else {
      prefs.setString('auth_token', value);
    }
  }

  static bool get isRtl => prefs.getString('locale') == 'ar';
}
