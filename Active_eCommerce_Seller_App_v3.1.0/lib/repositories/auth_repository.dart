import 'dart:convert';

import 'package:active_ecommerce_seller_app/api_request.dart';
import 'package:active_ecommerce_seller_app/app_config.dart';
import 'package:active_ecommerce_seller_app/data_model/common_response.dart';
import 'package:active_ecommerce_seller_app/data_model/login_response.dart';
import 'package:active_ecommerce_seller_app/data_model/logout_response.dart';
import 'package:active_ecommerce_seller_app/data_model/password_confirm_response.dart';
import 'package:active_ecommerce_seller_app/data_model/password_forget_response.dart';
import 'package:active_ecommerce_seller_app/data_model/resend_code_response.dart';
import 'package:active_ecommerce_seller_app/helpers/shared_value_helper.dart';
import 'package:flutter/foundation.dart';

class AuthRepository {
  Future<LoginResponse> getRegResponse({
    required String name,
    required String email,
    required String password,
    required String confirmPassword,
    required String shopName,
    required String address,
    // required String capchaKey,
  }) async {
    var postBody = jsonEncode({
      "name": name,
      "email": email,
      "password": password,
      "password_confirmation": confirmPassword,
      "shop_name": shopName,
      "address": address,
      // "g-recaptcha-response": "$capchaKey",
    });

    String url = ("${AppConfig.BASE_URL_WITH_PREFIX}/shops/create");

    // print("login url " + url.toString());
    // print("login body " + post_body.toString());

    final response = await ApiRequest.post(
        url: url,
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLApiRequestRequest",
          "App-Language": app_language.$!,
          "System-Key": AppConfig.system_key,
        },
        body: postBody);

    // print("login re ${response.body}");
    return loginResponseFromJson(response.body);
  }

  Future<LoginResponse> getLoginResponse(
      String? email, String password, String loginBy) async {
    var postBody = jsonEncode({
      "email": "$email",
      "password": password,
      "user_type": "seller",
      "login_by": loginBy
    });

    String url = ("${AppConfig.BASE_URL}/auth/login");

    //print("login url " + url.toString());
    // print("login body " + post_body.toString());

    final response = await ApiRequest.post(
        url: url,
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLApiRequestRequest",
          "App-Language": app_language.$!,
          "System-Key": AppConfig.system_key,
        },
        body: postBody);

    //print("login re ${response.body}");
    return loginResponseFromJson(response.body);
  }

  Future<LogoutResponse> getLogoutResponse() async {
    String url = ("${AppConfig.BASE_URL}/auth/logout");
    final response = await ApiRequest.get(
      url: url,
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
    );

    // print(response.body);

    return logoutResponseFromJson(response.body);
  }

  Future<LoginResponse> getUserByTokenResponse() async {
    var postBody = jsonEncode({"access_token": "${access_token.$}"});

    String url = ("${AppConfig.BASE_URL}/auth/info");
    // print(post_body);
    // print(url);
    if (access_token.$!.isNotEmpty) {
      final response = await ApiRequest.post(
          url: url,
          headers: {
            "Content-Type": "application/json",
            "App-Language": app_language.$!,
            "System-Key": AppConfig.system_key,
          },
          body: postBody);
      // print(response.body);

      return loginResponseFromJson(response.body);
    }
    return LoginResponse();
  }

  Future<PasswordForgetResponse> getPasswordForgetResponse(
      @required String? emailOrPhone, @required String sendCodeBy) async {
    var postBody = jsonEncode(
        {"email_or_phone": "$emailOrPhone", "send_code_by": sendCodeBy});

    String url = ("${AppConfig.BASE_URL}/auth/password/forget_request");

    final response = await ApiRequest.post(
        url: url,
        headers: {
          "Content-Type": "application/json",
          "App-Language": app_language.$!,
          "System-Key": AppConfig.system_key,
        },
        body: postBody);

    // print("response.body.toString()${response.body.toString()}");

    return passwordForgetResponseFromJson(response.body);
  }

  Future<PasswordConfirmResponse> getPasswordConfirmResponse(
      @required String verificationCode, @required String password) async {
    var postBody = jsonEncode(
        {"verification_code": verificationCode, "password": password});

    String url = ("${AppConfig.BASE_URL}/auth/password/confirm_reset");
    final response = await ApiRequest.post(
        url: url,
        headers: {
          "Content-Type": "application/json",
          "App-Language": app_language.$!,
          "System-Key": AppConfig.system_key,
        },
        body: postBody);

    return passwordConfirmResponseFromJson(response.body);
  }

  Future<ResendCodeResponse> getPasswordResendCodeResponse(
      @required String? emailOrCode, @required String verifyBy) async {
    var postBody =
        jsonEncode({"email_or_code": "$emailOrCode", "verify_by": verifyBy});

    String url = ("${AppConfig.BASE_URL}/auth/password/resend_code");
    final response = await ApiRequest.post(
        url: url,
        headers: {
          "Content-Type": "application/json",
          "App-Language": app_language.$!,
        },
        body: postBody);

    return resendCodeResponseFromJson(response.body);
  }

  Future<CommonResponse> getConfirmCodeResponse(String verificationCode) async {
    var postBody = jsonEncode({"verification_code": verificationCode});

    String url = ("${AppConfig.BASE_URL}/auth/confirm_code");
    final response = await ApiRequest.post(
        url: url,
        headers: {
          "Content-Type": "application/json",
          "App-Language": app_language.$!,
          "Authorization": "Bearer ${access_token.$}",
          "System-Key": AppConfig.system_key,
        },
        body: postBody);

    return commonResponseFromJson(response.body);
  }

  Future<ResendCodeResponse> getResendCodeResponse() async {
    String url = ("${AppConfig.BASE_URL}/auth/resend_code");
    final response = await ApiRequest.get(
      url: url,
      headers: {
        "Content-Type": "application/json",
        "App-Language": app_language.$!,
        "Authorization": "Bearer ${access_token.$}",
        "System-Key": AppConfig.system_key,
      },
    );
    return resendCodeResponseFromJson(response.body);
  }
}
