import 'dart:convert';

ProductMiniResponse productMiniResponseFromJson(String str) =>
    ProductMiniResponse.fromJson(json.decode(str));

class ProductMiniResponse {
  ProductMiniResponse({
    this.products,
    this.success,
    this.status,
  });

  final List<MiniProduct>? products;
  final bool? success;
  final int? status;

  factory ProductMiniResponse.fromJson(Map<String, dynamic> json) =>
      ProductMiniResponse(
        products: json["data"] != null
            ? List<MiniProduct>.from(
                json["data"].map((x) => MiniProduct.fromJson(x)),
              )
            : null,
        success: json["success"],
        status: json["status"],
      );
}

class MiniProduct {
  MiniProduct({
    this.id,
    this.slug,
    this.name,
    this.thumbnailImage,
    this.mainPrice,
  });

  final int? id;
  final String? slug;
  final String? name;
  final String? thumbnailImage;
  final String? mainPrice;

  factory MiniProduct.fromJson(Map<String, dynamic> json) => MiniProduct(
        id: json["id"],
        slug: json["slug"],
        name: json["name"],
        thumbnailImage: json["thumbnail_image"],
        mainPrice: json["main_price"],
      );
}
