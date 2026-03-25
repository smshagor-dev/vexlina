import 'dart:convert';

import 'package:active_ecommerce_cms_demo_app/app_config.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/lottery_response.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/api-request.dart';

class LotteryRepository {
  Future<LotteryOverviewResponse> getOverview() async {
    final response = await ApiRequest.get(
      url: "${AppConfig.BASE_URL}/lottery/overview",
      headers: {
        "App-Language": app_language.$!,
      },
    );

    return lotteryOverviewResponseFromJson(response.body);
  }

  Future<LotterySummaryResponse> getMySummary() async {
    final response = await ApiRequest.get(
      url: "${AppConfig.BASE_URL}/lottery/my-summary",
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
      },
    );

    return lotterySummaryResponseFromJson(response.body);
  }

  Future<LotteryTicketListResponse> getTickets({String filter = 'all'}) async {
    final response = await ApiRequest.get(
      url: "${AppConfig.BASE_URL}/lottery/tickets?filter=$filter",
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
      },
    );

    return lotteryTicketListResponseFromJson(response.body);
  }

  Future<LotteryTicketDetailsResponse> getTicketDetails(
    String ticketNumber,
  ) async {
    final response = await ApiRequest.get(
      url: "${AppConfig.BASE_URL}/lottery/tickets/$ticketNumber",
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
      },
    );

    return lotteryTicketDetailsResponseFromJson(response.body);
  }

  Future<LotteryWinsResponse> getWins({String filter = 'all'}) async {
    final response = await ApiRequest.get(
      url: "${AppConfig.BASE_URL}/lottery/wins?filter=$filter",
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
      },
    );

    return lotteryWinsResponseFromJson(response.body);
  }

  Future<LotteryClaimResponse> submitClaim({
    required int winnerId,
    required String mobile,
    required String address,
  }) async {
    final response = await ApiRequest.post(
      url: "${AppConfig.BASE_URL}/lottery/wins/$winnerId/claim",
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "Content-Type": "application/json",
      },
      body: jsonEncode({
        "mobile": mobile,
        "address": address,
      }),
    );

    return lotteryClaimResponseFromJson(response.body);
  }
}
