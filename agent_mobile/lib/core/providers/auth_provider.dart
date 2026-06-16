import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../api/dio_client.dart';
import '../constants/app_constants.dart';
import '../models/user.dart';
import '../storage/secure_storage.dart';

class AuthProvider extends ChangeNotifier {
  final DioClient _dioClient = DioClient();

  bool _isLoading = false;
  bool _isAuthenticated = false;
  User? _user;
  bool _mustChangePassword = false;
  String? _errorMessage;
  String? _resetPhone;

  bool get isLoading => _isLoading;
  bool get isAuthenticated => _isAuthenticated;
  User? get user => _user;
  bool get mustChangePassword => _mustChangePassword;
  String? get errorMessage => _errorMessage;
  String? get resetPhone => _resetPhone;

  Future<void> checkAuthStatus() async {
    final token = await SecureStorage.read(AppConstants.tokenKey);
    final userJson = await SecureStorage.read(AppConstants.userKey);

    print('--- DEBUG AUTH PROVIDER ---');
    print('token: $token');
    print('userJson: $userJson');
    if (token != null && userJson != null) {
      final decoded = json.decode(userJson);
      print('decoded user: $decoded');
      _user = User.fromJson(decoded);
      _isAuthenticated = true;
      notifyListeners();
    }
  }

  Future<bool> login({required String phone, required String password}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _dioClient.dio.post(
        '/login',
        data: {'phone': phone, 'password': password},
      );

      final data = response.data['data'];
      _user = User.fromJson(data['user']);
      _mustChangePassword = data['must_change_password'] ?? false;

      await SecureStorage.write(AppConstants.tokenKey, data['token']);
      await SecureStorage.write(AppConstants.userKey, json.encode(_user!.toJson()));

      _isAuthenticated = true;
      return true;
    } catch (e) {
      if (e is DioException && e.response != null) {
        final errors = e.response!.data['errors'];
        if (errors != null) {
          _errorMessage = errors.values.first.first;
        } else {
          _errorMessage = e.response!.data['message'] ?? 'Erreur inconnue';
        }
      } else {
        _errorMessage = 'Vérifiez votre connexion internet';
      }
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> changePassword({
    required String password,
    required String passwordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _dioClient.dio.post(
        '/password/change',
        data: {
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      _mustChangePassword = false;
      return true;
    } catch (e) {
      if (e is DioException && e.response != null) {
        final errors = e.response!.data['errors'];
        if (errors != null) {
          _errorMessage = errors.values.first.first;
        } else {
          _errorMessage = e.response!.data['message'] ?? 'Erreur inconnue';
        }
      } else {
        _errorMessage = 'Vérifiez votre connexion internet';
      }
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      await _dioClient.dio.post('/logout');
    } catch (_) {}

    await SecureStorage.deleteAll();
    _isAuthenticated = false;
    _user = null;
    _mustChangePassword = false;
    _errorMessage = null;
    notifyListeners();
  }

  Future<bool> requestOtp({required String phone}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _dioClient.dio.post(
        '/password/forgot',
        data: {'phone': phone},
      );
      _resetPhone = phone;
      return true;
    } catch (e) {
      if (e is DioException && e.response != null) {
        final errors = e.response!.data['errors'];
        if (errors != null) {
          _errorMessage = errors.values.first.first;
        } else {
          _errorMessage = e.response!.data['message'] ?? 'Erreur inconnue';
        }
      } else {
        _errorMessage = 'Vérifiez votre connexion internet';
      }
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> verifyOtp({required String otp}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    return true;
  }

  Future<bool> resetPassword({
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _dioClient.dio.post(
        '/password/reset',
        data: {
          'phone': _resetPhone,
          'otp': otp,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );
      _resetPhone = null;
      return true;
    } catch (e) {
      if (e is DioException && e.response != null) {
        final errors = e.response!.data['errors'];
        if (errors != null) {
          _errorMessage = errors.values.first.first;
        } else {
          _errorMessage = e.response!.data['message'] ?? 'Erreur inconnue';
        }
      } else {
        _errorMessage = 'Vérifiez votre connexion internet';
      }
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
