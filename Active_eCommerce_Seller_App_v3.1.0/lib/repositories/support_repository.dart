import 'dart:convert';

import 'package:active_ecommerce_seller_app/api_request.dart';
import 'package:active_ecommerce_seller_app/app_config.dart';
import 'package:active_ecommerce_seller_app/data_model/common_response.dart';
import 'package:active_ecommerce_seller_app/helpers/shared_value_helper.dart';

class SupportRepository {
  Future<CommonResponse> sendLoginSupportMessage({
    required String name,
    required String email,
    required String phone,
    required String subject,
    required String message,
  }) async {
    final postBody = jsonEncode({
      "name": name,
      "email": email,
      "phone": phone,
      "subject": subject,
      "message": message,
    });

    final response = await ApiRequest.post(
      url: "${AppConfig.BASE_URL}/auth/seller/support-message",
      headers: {
        "Content-Type": "application/json",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
      body: postBody,
    );

    return commonResponseFromJson(response.body);
  }
}
