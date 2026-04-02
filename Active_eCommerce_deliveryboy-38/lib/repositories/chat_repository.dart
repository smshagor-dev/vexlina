import 'dart:convert';

import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/data_model/conversation_create_response.dart';
import 'package:active_flutter_delivery_app/data_model/message_response.dart';
import 'package:active_flutter_delivery_app/helpers/api_request.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';

class ChatRepository {
  Future<MessageResponse> getMessageResponse({
    required int conversationId,
    int page = 1,
  }) async {
    final url =
        "${AppConfig.BASE_URL}/chat/messages/$conversationId?page=$page";

    final response = await ApiRequest.get(
      url: url,
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
    );

    return messageResponseFromJson(response.body);
  }

  Future<MessageResponse> getInserMessageResponse({
    required int conversationId,
    required String message,
  }) async {
    final postBody = jsonEncode({
      "user_id": "${user_id.$}",
      "conversation_id": "$conversationId",
      "message": message,
    });

    final response = await ApiRequest.post(
      url: "${AppConfig.BASE_URL}/chat/insert-message",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
      body: postBody,
    );

    return messageResponseFromJson(response.body);
  }

  Future<MessageResponse> getNewMessageResponse({
    required int conversationId,
    required int lastMessageId,
  }) async {
    final url =
        "${AppConfig.BASE_URL}/chat/get-new-messages/$conversationId/$lastMessageId";

    final response = await ApiRequest.get(
      url: url,
      headers: {
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
    );

    return messageResponseFromJson(response.body);
  }

  Future<ConversationCreateResponse> openCustomerConversation({
    required int orderId,
  }) async {
    final postBody = jsonEncode({
      "order_id": "$orderId",
    });

    final response = await ApiRequest.post(
      url: "${AppConfig.BASE_URL}/${AppConfig.DELIVERY_PREFIX}/chat/open",
      headers: {
        "Content-Type": "application/json",
        "Authorization": "Bearer ${access_token.$}",
        "App-Language": app_language.$!,
        "System-Key": AppConfig.system_key,
      },
      body: postBody,
    );

    return conversationCreateResponseFromJson(response.body);
  }
}
