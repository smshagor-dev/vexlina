import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:local_auth/local_auth.dart';

class BiometricHelper {
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static const _loginByKey = 'biometric_login_by';
  static const _identifierKey = 'biometric_identifier';
  static const _passwordKey = 'biometric_password';

  final LocalAuthentication _localAuth = LocalAuthentication();

  Future<bool> isBiometricAvailable() async {
    try {
      final canCheck = await _localAuth.canCheckBiometrics;
      final supported = await _localAuth.isDeviceSupported();
      final available = await _localAuth.getAvailableBiometrics();
      return (canCheck || supported) && available.isNotEmpty;
    } catch (_) {
      return false;
    }
  }

  Future<bool> hasSavedCredentials() async {
    final identifier = await _storage.read(key: _identifierKey);
    final password = await _storage.read(key: _passwordKey);
    final loginBy = await _storage.read(key: _loginByKey);
    return (identifier?.isNotEmpty ?? false) &&
        (password?.isNotEmpty ?? false) &&
        (loginBy?.isNotEmpty ?? false);
  }

  Future<void> saveCredentials({
    required String loginBy,
    required String identifier,
    required String password,
  }) async {
    await _storage.write(key: _loginByKey, value: loginBy);
    await _storage.write(key: _identifierKey, value: identifier);
    await _storage.write(key: _passwordKey, value: password);
  }

  Future<Map<String, String>?> authenticateAndGetCredentials() async {
    try {
      final authenticated = await _localAuth.authenticate(
        localizedReason: 'Verify your identity to log in',
        options: const AuthenticationOptions(
          biometricOnly: true,
          stickyAuth: true,
        ),
      );

      if (!authenticated) {
        return null;
      }

      final loginBy = await _storage.read(key: _loginByKey);
      final identifier = await _storage.read(key: _identifierKey);
      final password = await _storage.read(key: _passwordKey);

      if ([loginBy, identifier, password].any((value) => value == null || value.isEmpty)) {
        return null;
      }

      return <String, String>{
        'login_by': loginBy!,
        'identifier': identifier!,
        'password': password!,
      };
    } catch (e) {
      debugPrint('Biometric auth error: $e');
      return null;
    }
  }
}
