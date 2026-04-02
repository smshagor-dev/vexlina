// // To parse this JSON data, do
// //
// //     final CategoryWiseProductResponse = CategoryWiseProductResponseFromJson(jsonString);

// import 'dart:convert';

// List<CategoryWiseProductResponse> categoryWiseProductResponseFromJson(String str) => List<CategoryWiseProductResponse>.from(json.decode(str).map((x) => CategoryWiseProductResponse.fromJson(x)));

// String categoryWiseProductResponseToJson(List<CategoryWiseProductResponse> data) => json.encode(List<dynamic>.from(data.map((x) => x.toJson())));

// class CategoryWiseProductResponse {
//   CategoryWiseProductResponse({
//     this.name,
//     this.cntProduct,
//     this.banner,
//   });

//   String? name;
//   int? cntProduct;
//   String? banner;

//   factory CategoryWiseProductResponse.fromJson(Map<String, dynamic> json) => CategoryWiseProductResponse(
//     name: json["name"],
//     cntProduct: json["cnt_product"],
//     banner: json["banner"],
//   );

//   Map<String, dynamic> toJson() => {
//     "name": name,
//     "cnt_product": cntProduct,
//     "banner": banner,
//   };
// }

import 'dart:convert';

// Function to parse JSON when it's an array of objects
List<CategoryWiseProductResponse> categoryWiseProductResponseFromJson(
    String str) {
  final jsonData = json.decode(str);
  if (jsonData is List) {
    // Handles the case where JSON is an array
    return List<CategoryWiseProductResponse>.from(
        jsonData.map((x) => CategoryWiseProductResponse.fromJson(x)));
  } else if (jsonData is Map) {
    // Handles the case where JSON is a map
    return jsonData.values
        .map((x) => CategoryWiseProductResponse.fromJson(x))
        .toList();
  } else {
    throw Exception("Invalid JSON format: Expected List or Map");
  }
}

// Function to convert a list of objects back to JSON
String categoryWiseProductResponseToJson(
        List<CategoryWiseProductResponse> data) =>
    json.encode(List<dynamic>.from(data.map((x) => x.toJson())));

class CategoryWiseProductResponse {
  CategoryWiseProductResponse({
    this.name,
    this.cntProduct,
    this.banner,
  });

  String? name;
  int? cntProduct;
  String? banner;

  // Factory constructor to create an object from JSON
  factory CategoryWiseProductResponse.fromJson(Map<String, dynamic> json) =>
      CategoryWiseProductResponse(
        name: json["name"] as String?,
        cntProduct: json["cnt_product"] as int?,
        banner: json["banner"] as String?,
      );

  // Method to convert an object to JSON
  Map<String, dynamic> toJson() => {
        "name": name,
        "cnt_product": cntProduct,
        "banner": banner,
      };
}
