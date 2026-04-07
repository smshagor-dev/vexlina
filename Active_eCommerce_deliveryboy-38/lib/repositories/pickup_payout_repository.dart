import 'dart:convert';

import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/data_model/common_response.dart';
import 'package:active_flutter_delivery_app/data_model/pickup_payout_requests_response.dart';
import 'package:active_flutter_delivery_app/data_model/pickup_payout_summary_response.dart';
import 'package:active_flutter_delivery_app/helpers/api_request.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';

class PickupPayoutRepository {
  Future<PickupPayoutSummaryResponse> getSummary() async {
    final response = await ApiRequest.get(
      url: "${AppConfig.BASE_URL}/pickup-point/payout-summary/${user_id.$}",
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
    );

    return pickupPayoutSummaryResponseFromJson(response.body);
  }

  Future<PickupPayoutRequestsResponse> getRequests({int page = 1}) async {
    final response = await ApiRequest.get(
      url:
          "${AppConfig.BASE_URL}/pickup-point/payout-requests/${user_id.$}?page=$page",
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
    );

    return pickupPayoutRequestsResponseFromJson(response.body);
  }

  Future<CommonResponse> updatePayoutInfo({
    required String payoutMethod,
    required String payoutAccountName,
    String? payoutAccountNumber,
    String? payoutBankName,
    String? payoutBranchName,
    String? payoutRoutingNumber,
    String? payoutMobileWalletType,
    String? payoutMobileWalletNumber,
    String? payoutNotes,
  }) async {
    final postBody = jsonEncode({
      "payout_method": payoutMethod,
      "payout_account_name": payoutAccountName,
      "payout_account_number": payoutAccountNumber,
      "payout_bank_name": payoutBankName,
      "payout_branch_name": payoutBranchName,
      "payout_routing_number": payoutRoutingNumber,
      "payout_mobile_wallet_type": payoutMobileWalletType,
      "payout_mobile_wallet_number": payoutMobileWalletNumber,
      "payout_notes": payoutNotes,
    });

    final response = await ApiRequest.post(
      url: "${AppConfig.BASE_URL}/pickup-point/payout-info/update",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
      body: postBody,
    );

    return commonResponseFromJson(response.body);
  }

  Future<CommonResponse> storePayoutRequest({
    required String amount,
    String? message,
  }) async {
    final postBody = jsonEncode({
      "amount": amount,
      "message": message,
    });

    final response = await ApiRequest.post(
      url: "${AppConfig.BASE_URL}/pickup-point/payout-request/store",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
      body: postBody,
    );

    return commonResponseFromJson(response.body);
  }
}
