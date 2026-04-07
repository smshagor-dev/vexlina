import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/data_model/common_response.dart';
import 'package:active_flutter_delivery_app/helpers/api_request.dart';
import 'package:active_flutter_delivery_app/data_model/login_response.dart';
import 'package:active_flutter_delivery_app/data_model/logout_response.dart';
import 'dart:convert';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';

class AuthRepository {
  Future<LoginResponse> _attemptLoginResponse(
    String? email,
    String password,
    String loginBy,
    String requestedUserType,
  ) async {
    var post_body = jsonEncode({
      "user_type": requestedUserType,
      "email": "$email",
      "password": "$password",
      "login_by": loginBy
    });

    final response = await ApiRequest.post(
      url: ("${AppConfig.BASE_URL}/auth/login"),
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLApiRequestRequest",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key
      },
      body: post_body,
    );

    return loginResponseFromJson(response.body);
  }

  Future<LoginResponse> getLoginResponse(
       String? email,  String password,String loginBy) async {
    final deliveryBoyResponse = await _attemptLoginResponse(
      email,
      password,
      loginBy,
      "delivery_boy",
    );

    if (deliveryBoyResponse.result == true) {
      return deliveryBoyResponse;
    }

    final pickupPointResponse = await _attemptLoginResponse(
      email,
      password,
      loginBy,
      "pickup_point",
    );

    return pickupPointResponse;
  }

  Future<LogoutResponse> getLogoutResponse() async {
    final response = await ApiRequest.get(url:
      ( "${AppConfig.BASE_URL}/auth/logout")
     ,
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key
      },
    );



    return logoutResponseFromJson(response.body);
  }


  Future<LoginResponse> getUserByTokenResponse() async {
    var post_body = jsonEncode({"access_token": "${access_token.$}"});

    final response = await ApiRequest.post(url:
      ("${AppConfig.BASE_URL}/auth/info")
        ,
        headers: {"Content-Type": "application/json","App-Language": app_language.$!,"System-Key": AppConfig.system_key},
        body: post_body);

    return loginResponseFromJson(response.body);
  }

  Future<CommonResponse> getAccountDeleteResponse() async {
    String url = ("${AppConfig.BASE_URL}/auth/account-deletion");

    print(url.toString());

    print("Bearer ${access_token.$}");
    final response = await ApiRequest.get(
      url: url,
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
    );
    print(response.body);
    return commonResponseFromJson(response.body);
  }

}
