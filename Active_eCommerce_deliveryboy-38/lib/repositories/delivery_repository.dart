import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/data_model/order_mini_response.dart';
import 'package:active_flutter_delivery_app/helpers/api_request.dart';
import 'dart:convert';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/data_model/earning_summary_response.dart';
import 'package:active_flutter_delivery_app/data_model/collection_summary_response.dart';
import 'package:active_flutter_delivery_app/data_model/earning_or_collection_response.dart';
import 'package:active_flutter_delivery_app/data_model/cancel_request_response.dart';
import 'package:active_flutter_delivery_app/data_model/delivery_status_change_response.dart';

class DeliveryRepository {
  String _resolveType(String type) {
    if (!PortalHelper.isPickupPointApp) {
      return type;
    }

    switch (type) {
      case "assigned":
        return "upcoming";
      case "cancelled":
        return "returned";
      default:
        return type;
    }
  }

  Future<OrderMiniResponse> getDeliveryListResponse(
      {type = "completed",
      page = 1,
      date_range = "",
      payment_type}) async {
    final resolvedType = _resolveType(type);
    final response = await ApiRequest.get(url:
      ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/deliveries/${resolvedType}/${user_id.$}?date_range=$date_range&payment_type=$payment_type&page=$page")
        ,
        headers: {"Authorization": "Bearer ${access_token.$}","App-Language": app_language.$!,"System-Key": AppConfig.system_key});

    print("body\n");
    print(response.body.toString());
    return orderMiniResponseFromJson(response.body);
  }

  Future<EarningSummaryResponse> getEarningSummaryResponse() async {
    final response = await ApiRequest.get(url:
      ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/earning-summary/${user_id.$}")
        ,
        headers: {"Authorization": "Bearer ${access_token.$}","App-Language": app_language.$!,"System-Key": AppConfig.system_key});

    /*print("body\n");
    print(response.body.toString());*/
    return earningSummaryResponseFromJson(response.body);
  }

  Future<CollectionSummaryResponse> getCollectionSummaryResponse() async {
    if (PortalHelper.isPickupPointApp) {
      final earningSummary = await getEarningSummaryResponse();
      return CollectionSummaryResponse(
        today_collection: earningSummary.today_earning,
        yesterday_collection: earningSummary.yesterday_earning,
        today_date: earningSummary.today_date,
        yesterday_date: earningSummary.yesterday_date,
      );
    }

    final response = await ApiRequest.get(url:
      ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/collection-summary/${user_id.$}")
        ,
        headers: {"Authorization": "Bearer ${access_token.$}","App-Language": app_language.$!,"System-Key": AppConfig.system_key});

    /*print("body\n");
    print(response.body.toString());*/
    return collectionSummaryResponseFromJson(response.body);
  }

  Future<EarningOrCollectionResponse> getEarningResponse({page = 1}) async {
    final response = await ApiRequest.get(url:
      ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/earning/${user_id.$}?page=${page}")
        ,
        headers: {"Authorization": "Bearer ${access_token.$}","App-Language": app_language.$!,"System-Key": AppConfig.system_key});

    /*print("body\n");
    print(response.body.toString());*/
    return earningOrCollectionResponseFromJson(response.body);
  }

  Future<EarningOrCollectionResponse> getCollectionResponse({page = 1}) async {
    if (PortalHelper.isPickupPointApp) {
      return getEarningResponse(page: page);
    }

    final response = await ApiRequest.get(url:
      ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/collection/${user_id.$}?page=${page}")
        ,
        headers: {"Authorization": "Bearer ${access_token.$}","App-Language": app_language.$!,"System-Key": AppConfig.system_key});

    /*print("body\n");
    print(response.body.toString());*/
    return earningOrCollectionResponseFromJson(response.body);
  }

  Future<CancelRequestResponse> getCancelRequestResponse(order_id) async {
    final response = await ApiRequest.get(url:
      ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/cancel-request/${order_id}")
        ,
        headers: {"Authorization": "Bearer ${access_token.$}","App-Language": app_language.$!,"System-Key": AppConfig.system_key});

    /*print("body\n");
    print(response.body.toString());*/
    return cancelRequestResponseFromJson(response.body);
  }

  Future<DeliveryStatusChangeResponse> getDeliveryStatusChangeResponse({
      required status,
      order_id,
      String? delivery_verification_code}
      ) async {
    var post_body = jsonEncode({
      "status": "${status}",
      if (order_id != null) "order_id": "$order_id",
      if (!PortalHelper.isPickupPointApp) "delivery_boy_id": "${user_id.$}",
      if (delivery_verification_code != null)
        "delivery_verification_code": delivery_verification_code
    });

    final response = await ApiRequest.post(url:
      ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/change-delivery-status")
        ,
        headers: {
          "Content-Type": "application/json",
          "Authorization": "Bearer ${access_token.$}",
          "App-Language": app_language.$!,
          "System-Key": AppConfig.system_key
        },
        body: post_body);

    /*print("body\n");
    print(response.body.toString());*/
    return deliveryStatusChangeResponseFromJson(response.body);
  }
}
