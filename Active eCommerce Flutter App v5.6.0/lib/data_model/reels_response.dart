import 'dart:convert';

ReelsFeedResponse reelsFeedResponseFromJson(String str) =>
    ReelsFeedResponse.fromJson(json.decode(str));

ReelDetailResponse reelDetailResponseFromJson(String str) =>
    ReelDetailResponse.fromJson(json.decode(str));

ReelCommentsResponse reelCommentsResponseFromJson(String str) =>
    ReelCommentsResponse.fromJson(json.decode(str));

ReelsPermissionsResponse reelsPermissionsResponseFromJson(String str) =>
    ReelsPermissionsResponse.fromJson(json.decode(str));

class ReelsFeedResponse {
  ReelsFeedResponse({
    required this.result,
    required this.message,
    required this.data,
    this.meta,
  });

  final bool result;
  final String message;
  final List<ReelItem> data;
  final ReelsMeta? meta;

  factory ReelsFeedResponse.fromJson(Map<String, dynamic> json) {
    return ReelsFeedResponse(
      result: json["result"] ?? false,
      message: json["message"] ?? "",
      data: (json["data"] as List? ?? [])
          .map((e) => ReelItem.fromJson(Map<String, dynamic>.from(e)))
          .toList(),
      meta: json["meta"] == null
          ? null
          : ReelsMeta.fromJson(Map<String, dynamic>.from(json["meta"])),
    );
  }
}

class ReelDetailResponse {
  ReelDetailResponse({
    required this.result,
    this.data,
  });

  final bool result;
  final ReelItem? data;

  factory ReelDetailResponse.fromJson(Map<String, dynamic> json) {
    return ReelDetailResponse(
      result: json["result"] ?? false,
      data: json["data"] == null
          ? null
          : ReelItem.fromJson(Map<String, dynamic>.from(json["data"])),
    );
  }
}

class ReelCommentsResponse {
  ReelCommentsResponse({
    required this.result,
    required this.data,
  });

  final bool result;
  final List<ReelCommentItem> data;

  factory ReelCommentsResponse.fromJson(Map<String, dynamic> json) {
    return ReelCommentsResponse(
      result: json["result"] ?? false,
      data: (json["data"] as List? ?? [])
          .map((e) => ReelCommentItem.fromJson(Map<String, dynamic>.from(e)))
          .toList(),
    );
  }
}

class ReelsPermissionsResponse {
  ReelsPermissionsResponse({
    required this.result,
    this.data,
  });

  final bool result;
  final ReelsPermissionData? data;

  factory ReelsPermissionsResponse.fromJson(Map<String, dynamic> json) {
    return ReelsPermissionsResponse(
      result: json["result"] ?? false,
      data: json["data"] == null
          ? null
          : ReelsPermissionData.fromJson(
              Map<String, dynamic>.from(json["data"]),
            ),
    );
  }
}

class ReelsMeta {
  ReelsMeta({
    this.currentPage,
    this.lastPage,
    this.total,
    this.canPost,
  });

  final int? currentPage;
  final int? lastPage;
  final int? total;
  final bool? canPost;

  factory ReelsMeta.fromJson(Map<String, dynamic> json) {
    return ReelsMeta(
      currentPage: _toInt(json["current_page"]),
      lastPage: _toInt(json["last_page"]),
      total: _toInt(json["total"]),
      canPost: json["can_post"] == true,
    );
  }
}

class ReelsPermissionData {
  ReelsPermissionData({
    required this.canPost,
    required this.isSeller,
    required this.classifiedPackageId,
    required this.remainingUploads,
  });

  final bool canPost;
  final bool isSeller;
  final int classifiedPackageId;
  final int remainingUploads;

  factory ReelsPermissionData.fromJson(Map<String, dynamic> json) {
    return ReelsPermissionData(
      canPost: json["can_post"] == true,
      isSeller: json["is_seller"] == true,
      classifiedPackageId: _toInt(json["classified_package_id"]),
      remainingUploads: _toInt(json["remaining_uploads"]),
    );
  }
}

class ReelItem {
  ReelItem({
    required this.id,
    required this.caption,
    required this.videoUrl,
    required this.thumbnailUrl,
    required this.durationSeconds,
    required this.allowComments,
    required this.viewsCount,
    required this.likesCount,
    required this.commentsCount,
    required this.sharesCount,
    required this.savesCount,
    required this.isLiked,
    required this.isSaved,
    required this.canEdit,
    required this.user,
    this.product,
    this.link,
    this.createdAt,
  });

  final int id;
  final String caption;
  final String videoUrl;
  final String? thumbnailUrl;
  final int durationSeconds;
  final bool allowComments;
  final int viewsCount;
  final int likesCount;
  final int commentsCount;
  final int sharesCount;
  final int savesCount;
  final bool isLiked;
  final bool isSaved;
  final bool canEdit;
  final ReelUser user;
  final ReelProduct? product;
  final String? link;
  final String? createdAt;

  factory ReelItem.fromJson(Map<String, dynamic> json) {
    return ReelItem(
      id: _toInt(json["id"]),
      caption: json["caption"]?.toString() ?? "",
      videoUrl: json["video_url"]?.toString() ?? "",
      thumbnailUrl: json["thumbnail_url"]?.toString(),
      durationSeconds: _toInt(json["duration_seconds"]),
      allowComments: json["allow_comments"] == true,
      viewsCount: _toInt(json["views_count"]),
      likesCount: _toInt(json["likes_count"]),
      commentsCount: _toInt(json["comments_count"]),
      sharesCount: _toInt(json["shares_count"]),
      savesCount: _toInt(json["saves_count"]),
      isLiked: json["is_liked"] == true,
      isSaved: json["is_saved"] == true,
      canEdit: json["can_edit"] == true,
      user: ReelUser.fromJson(Map<String, dynamic>.from(json["user"] ?? {})),
      product: json["product"] == null
          ? null
          : ReelProduct.fromJson(Map<String, dynamic>.from(json["product"])),
      link: json["link"]?.toString(),
      createdAt: json["created_at"]?.toString(),
    );
  }

  ReelItem copyWith({
    int? viewsCount,
    int? likesCount,
    int? commentsCount,
    int? sharesCount,
    int? savesCount,
    bool? isLiked,
    bool? isSaved,
  }) {
    return ReelItem(
      id: id,
      caption: caption,
      videoUrl: videoUrl,
      thumbnailUrl: thumbnailUrl,
      durationSeconds: durationSeconds,
      allowComments: allowComments,
      viewsCount: viewsCount ?? this.viewsCount,
      likesCount: likesCount ?? this.likesCount,
      commentsCount: commentsCount ?? this.commentsCount,
      sharesCount: sharesCount ?? this.sharesCount,
      savesCount: savesCount ?? this.savesCount,
      isLiked: isLiked ?? this.isLiked,
      isSaved: isSaved ?? this.isSaved,
      canEdit: canEdit,
      user: user,
      product: product,
      link: link,
      createdAt: createdAt,
    );
  }
}

class ReelUser {
  ReelUser({
    required this.id,
    required this.name,
    required this.avatar,
    required this.userType,
  });

  final int id;
  final String name;
  final String? avatar;
  final String userType;

  factory ReelUser.fromJson(Map<String, dynamic> json) {
    return ReelUser(
      id: _toInt(json["id"]),
      name: json["name"]?.toString() ?? "",
      avatar: json["avatar"]?.toString(),
      userType: json["user_type"]?.toString() ?? "",
    );
  }
}

class ReelProduct {
  ReelProduct({
    required this.id,
    required this.slug,
    required this.name,
    required this.thumbnailImage,
    required this.price,
    required this.link,
  });

  final int id;
  final String slug;
  final String name;
  final String? thumbnailImage;
  final String price;
  final String? link;

  factory ReelProduct.fromJson(Map<String, dynamic> json) {
    return ReelProduct(
      id: _toInt(json["id"]),
      slug: json["slug"]?.toString() ?? "",
      name: json["name"]?.toString() ?? "",
      thumbnailImage: json["thumbnail_image"]?.toString(),
      price: json["price"]?.toString() ?? "",
      link: json["link"]?.toString(),
    );
  }
}

class ReelCommentItem {
  ReelCommentItem({
    required this.id,
    required this.comment,
    required this.createdAt,
    required this.user,
    required this.replies,
  });

  final int id;
  final String comment;
  final String createdAt;
  final ReelUser user;
  final List<ReelCommentItem> replies;

  factory ReelCommentItem.fromJson(Map<String, dynamic> json) {
    return ReelCommentItem(
      id: _toInt(json["id"]),
      comment: json["comment"]?.toString() ?? "",
      createdAt: json["created_at"]?.toString() ?? "",
      user: ReelUser.fromJson(Map<String, dynamic>.from(json["user"] ?? {})),
      replies: (json["replies"] as List? ?? [])
          .map((e) => ReelCommentItem.fromJson(Map<String, dynamic>.from(e)))
          .toList(),
    );
  }
}

int _toInt(dynamic value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}
