import 'dart:convert';

import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/data_model/common_response.dart';
import 'package:active_flutter_delivery_app/helpers/api_request.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';

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
      url: "${AppConfig.BASE_URL}/auth/${PortalHelper.isPickupPointApp ? AppConfig.PICKUP_POINT_PREFIX : AppConfig.DELIVERY_PREFIX}/support-message",
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
