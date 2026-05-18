import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/config/app_config.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/user.dart';

enum AuthStatus { unknown, authenticated, unauthenticated }

class AuthState {
  final AuthStatus status;
  final User? user;
  final String? error;
  final bool isLoading;

  const AuthState({
    this.status = AuthStatus.unknown,
    this.user,
    this.error,
    this.isLoading = false,
  });

  AuthState copyWith({
    AuthStatus? status,
    User? user,
    String? error,
    bool? isLoading,
  }) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      error: error,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

final authStateProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.read(apiClientProvider));
});

class AuthNotifier extends StateNotifier<AuthState> {
  final ApiClient _api;

  AuthNotifier(this._api) : super(const AuthState()) {
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    final token = AppConfig.token;
    if (token == null) {
      state = state.copyWith(status: AuthStatus.unauthenticated);
      return;
    }
    try {
      final res = await _api.get('/me');
      state = AuthState(
        status: AuthStatus.authenticated,
        user: User.fromJson(res.data as Map<String, dynamic>),
      );
    } catch (_) {
      AppConfig.token = null;
      state = state.copyWith(status: AuthStatus.unauthenticated);
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String role,
  }) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _api.post('/register', data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        'role': role,
      });
      final auth = AuthResponse.fromJson(res.data as Map<String, dynamic>);
      AppConfig.token = auth.token;
      state = AuthState(
        status: AuthStatus.authenticated,
        user: auth.user,
      );
    } on Exception catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: _extractError(e),
      );
    }
  }

  Future<void> login({required String email, required String password}) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final res = await _api.post('/login', data: {
        'email': email,
        'password': password,
      });
      final auth = AuthResponse.fromJson(res.data as Map<String, dynamic>);
      AppConfig.token = auth.token;
      state = AuthState(
        status: AuthStatus.authenticated,
        user: auth.user,
      );
    } on Exception catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: _extractError(e),
      );
    }
  }

  Future<void> logout() async {
    try {
      await _api.post('/logout');
    } catch (_) {}
    AppConfig.token = null;
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  String _extractError(Exception e) {
    if (e is DioException && e.response?.data != null) {
      final data = e.response!.data as Map<String, dynamic>;
      if (data['message'] is String) return data['message'] as String;
      if (data['errors'] is Map) {
        return (data['errors'] as Map).values.first is List
            ? ((data['errors'] as Map).values.first as List).first as String
            : 'Validation error';
      }
    }
    return 'Something went wrong';
  }
}
