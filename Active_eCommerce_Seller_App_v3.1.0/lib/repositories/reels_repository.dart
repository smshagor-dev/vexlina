import 'dart:io';

import 'package:active_ecommerce_seller_app/data_model/common_response.dart';
import 'package:active_ecommerce_seller_app/data_model/product_mini_response.dart';
import 'package:active_ecommerce_seller_app/data_model/reels_response.dart';
import 'package:http/http.dart' as http;

import '../api_request.dart';
import '../app_config.dart';
import '../helpers/shared_value_helper.dart';

class ReelsRepository {
  Map<String, String> _publicHeaders() {
    return {
      "App-Language": app_language.$!,
      "System-Key": AppConfig.system_key,
      "Accept": "application/json",
    };
  }

  Map<String, String> _authHeaders() {
    return {
      "App-Language": app_language.$!,
      "Content-Type": "application/json",
      "Authorization": "Bearer ${access_token.$}",
      "System-Key": AppConfig.system_key,
      "Accept": "application/json",
    };
  }

  Future<ReelsFeedResponse> getMyPosts({int page = 1}) async {
    final url = "${AppConfig.BASE_URL}/reals/my-posts?page=$page";
    final response = await ApiRequest.get(url: url, headers: _authHeaders());
    return reelsFeedResponseFromJson(response.body);
  }

  Future<ReelsPermissionsResponse> getPermissions() async {
    final url = "${AppConfig.BASE_URL}/reals/my-permissions";
    final response = await ApiRequest.get(url: url, headers: _authHeaders());
    return reelsPermissionsResponseFromJson(response.body);
  }

  Future<CommonResponse> deleteReel(int reelId) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId";
    final response = await ApiRequest.delete(url: url, headers: _authHeaders());
    return commonResponseFromJson(response.body);
  }

  Future<ProductMiniResponse> searchProducts(String keyword) async {
    final url =
        "${AppConfig.BASE_URL}/products/search?name=${Uri.encodeComponent(keyword)}";
    final response = await ApiRequest.get(url: url, headers: _publicHeaders());
    return productMiniResponseFromJson(response.body);
  }

  Future<CommonResponse> createReel({
    required File videoFile,
    File? thumbnailFile,
    String? caption,
    int? productId,
    bool allowComments = true,
  }) async {
    final url = Uri.parse("${AppConfig.BASE_URL}/reals/store");
    final request = http.MultipartRequest("POST", url);
    request.headers.addAll({
      "App-Language": app_language.$!,
      "Authorization": "Bearer ${access_token.$}",
      "Accept": "application/json",
      "System-Key": AppConfig.system_key,
    });

    request.files.add(await http.MultipartFile.fromPath("video", videoFile.path));
    if (thumbnailFile != null) {
      request.files.add(
        await http.MultipartFile.fromPath("thumbnail", thumbnailFile.path),
      );
    }
    if (caption != null && caption.trim().isNotEmpty) {
      request.fields["caption"] = caption.trim();
    }
    if (productId != null) {
      request.fields["product_id"] = productId.toString();
    }
    request.fields["allow_comments"] = allowComments ? "1" : "0";

    final streamedResponse = await request.send();
    final body = await streamedResponse.stream.bytesToString();
    return commonResponseFromJson(body);
  }
}
