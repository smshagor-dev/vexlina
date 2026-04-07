import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:active_ecommerce_cms_demo_app/data_model/common_response.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/product_mini_response.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/reels_response.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

import '../app_config.dart';
import '../helpers/shared_value_helper.dart';
import 'api-request.dart';

class ReelsRepository {
  Map<String, String> _publicHeaders() {
    return {
      "App-Language": app_language.$!,
      "System-Key": AppConfig.system_key,
    };
  }

  Map<String, String> _authHeaders() {
    return {
      "App-Language": app_language.$!,
      "Content-Type": "application/json",
      "Authorization": "Bearer ${access_token.$}",
      "System-Key": AppConfig.system_key,
    };
  }

  Future<ReelsFeedResponse> getFeed({int page = 1}) async {
    final url = "${AppConfig.BASE_URL}/reals?page=$page";
    final response = await ApiRequest.get(url: url, headers: _publicHeaders());
    return reelsFeedResponseFromJson(response.body);
  }

  Future<ReelsPermissionsResponse> getPermissions() async {
    final url = "${AppConfig.BASE_URL}/reals/my-permissions";
    final response = await ApiRequest.get(url: url, headers: _authHeaders());
    return reelsPermissionsResponseFromJson(response.body);
  }

  Future<ReelsFeedResponse> getMyPosts() async {
    final url = "${AppConfig.BASE_URL}/reals/my-posts";
    final response = await ApiRequest.get(url: url, headers: _authHeaders());
    return reelsFeedResponseFromJson(response.body);
  }

  Future<ReelCommentsResponse> getComments(int reelId) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId/comments";
    final response = await ApiRequest.get(url: url, headers: _publicHeaders());
    return reelCommentsResponseFromJson(response.body);
  }

  Future<CommonResponse> toggleLike(int reelId) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId/like";
    final response = await ApiRequest.post(
      url: url,
      headers: _authHeaders(),
      body: jsonEncode({}),
    );
    return commonResponseFromJson(response.body);
  }

  Future<CommonResponse> toggleSave(int reelId) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId/save";
    final response = await ApiRequest.post(
      url: url,
      headers: _authHeaders(),
      body: jsonEncode({}),
    );
    return commonResponseFromJson(response.body);
  }

  Future<CommonResponse> addComment({
    required int reelId,
    required String comment,
    int? parentId,
  }) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId/comment";
    final response = await ApiRequest.post(
      url: url,
      headers: _authHeaders(),
      body: jsonEncode({"comment": comment, "parent_id": parentId}),
    );
    return commonResponseFromJson(response.body);
  }

  Future<int> recordView(int reelId, {String? deviceKey}) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId/view";
    final response = await ApiRequest.post(
      url: url,
      headers: {
        "App-Language": app_language.$!,
        "Content-Type": "application/json",
        "System-Key": AppConfig.system_key,
        if (access_token.$ != null && access_token.$!.isNotEmpty)
          "Authorization": "Bearer ${access_token.$}",
      },
      body: jsonEncode({"device_key": deviceKey}),
    );
    try {
      final jsonBody = json.decode(response.body) as Map<String, dynamic>;
      return (jsonBody["data"]?["views_count"] as num?)?.toInt() ?? 0;
    } catch (_) {
      return 0;
    }
  }

  Future<int> recordShare(int reelId, {String platform = "app"}) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId/share";
    final response = await ApiRequest.post(
      url: url,
      headers: _authHeaders(),
      body: jsonEncode({"platform": platform}),
    );
    try {
      final jsonBody = json.decode(response.body) as Map<String, dynamic>;
      return (jsonBody["data"]?["shares_count"] as num?)?.toInt() ?? 0;
    } catch (_) {
      return 0;
    }
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
    int? durationSeconds,
    bool allowComments = true,
    ValueChanged<double>? onProgress,
  }) async {
    final url = Uri.parse("${AppConfig.BASE_URL}/reals/store");
    final request = http.MultipartRequest("POST", url);
    request.headers.addAll({
      "App-Language": app_language.$!,
      "Authorization": "Bearer ${access_token.$}",
      "Accept": "application/json",
      "System-Key": AppConfig.system_key,
    });

    final totalBytes =
        await videoFile.length() +
        (thumbnailFile != null ? await thumbnailFile.length() : 0);
    var uploadedBytes = 0;

    request.files.add(
      await _multipartFileWithProgress(
        field: "video",
        file: videoFile,
        onChunkUploaded: (chunkLength) {
          uploadedBytes += chunkLength;
          onProgress?.call(
            totalBytes == 0 ? 0 : (uploadedBytes / totalBytes).clamp(0.0, 1.0),
          );
        },
      ),
    );
    if (thumbnailFile != null) {
      request.files.add(
        await _multipartFileWithProgress(
          field: "thumbnail",
          file: thumbnailFile,
          onChunkUploaded: (chunkLength) {
            uploadedBytes += chunkLength;
            onProgress?.call(
              totalBytes == 0
                  ? 0
                  : (uploadedBytes / totalBytes).clamp(0.0, 1.0),
            );
          },
        ),
      );
    }

    if (caption != null && caption.trim().isNotEmpty) {
      request.fields["caption"] = caption.trim();
    }
    if (productId != null) {
      request.fields["product_id"] = productId.toString();
    }
    if (durationSeconds != null) {
      request.fields["duration_seconds"] = durationSeconds.toString();
    }
    request.fields["allow_comments"] = allowComments ? "1" : "0";

    final streamedResponse = await request.send();
    onProgress?.call(1);
    final body = await streamedResponse.stream.bytesToString();
    return commonResponseFromJson(body);
  }

  Future<http.MultipartFile> _multipartFileWithProgress({
    required String field,
    required File file,
    required void Function(int chunkLength) onChunkUploaded,
  }) async {
    final length = await file.length();
    final stream = http.ByteStream(
      file.openRead().transform(
        StreamTransformer.fromHandlers(
          handleData: (data, sink) {
            onChunkUploaded(data.length);
            sink.add(data);
          },
        ),
      ),
    );

    return http.MultipartFile(
      field,
      stream,
      length,
      filename: file.uri.pathSegments.last,
    );
  }

  Future<ReelDetailResponse> updateReel({
    required int reelId,
    String? caption,
    int? productId,
    required bool allowComments,
  }) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId/update";
    final response = await ApiRequest.post(
      url: url,
      headers: _authHeaders(),
      body: jsonEncode({
        "caption": caption?.trim(),
        "product_id": productId,
        "allow_comments": allowComments,
      }),
    );
    return reelDetailResponseFromJson(response.body);
  }

  Future<CommonResponse> deleteReel(int reelId) async {
    final url = "${AppConfig.BASE_URL}/reals/$reelId";
    final response = await ApiRequest.delete(
      url: url,
      headers: {
        "App-Language": app_language.$!,
        "Authorization": "Bearer ${access_token.$}",
        "System-Key": AppConfig.system_key,
      },
    );
    return commonResponseFromJson(response.body);
  }
}
