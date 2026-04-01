import 'dart:convert';

CartSummaryResponse cartSummaryResponseFromJson(String str) =>
    CartSummaryResponse.fromJson(json.decode(str));

String cartSummaryResponseToJson(CartSummaryResponse data) =>
    json.encode(data.toJson());

class CartSummaryResponse {
  String? subTotal;
  String? tax;
  String? gst;
  String? shippingCost;
  String? discount;
  String? walletPaymentDiscount;
  double? walletPaymentDiscountValue;
  double? walletPaymentDiscountPercent;
  bool? walletPaymentDiscountApplied;
  dynamic grandTotal;
  double? grandTotalValue;
  String? couponCode;
  bool? couponApplied;
  int? totalProduct;
  int? clubPoint;

  CartSummaryResponse({
    this.subTotal,
    this.tax,
    this.gst,
    this.shippingCost,
    this.discount,
    this.walletPaymentDiscount,
    this.walletPaymentDiscountValue,
    this.walletPaymentDiscountPercent,
    this.walletPaymentDiscountApplied,
    this.grandTotal,
    this.grandTotalValue,
    this.couponCode,
    this.couponApplied,
    this.totalProduct,
    this.clubPoint,
  });

  factory CartSummaryResponse.fromJson(Map<String, dynamic> json) =>
      CartSummaryResponse(
        subTotal: json["sub_total"],
        tax: json["tax"],
        gst: json["gst"],
        shippingCost: json["shipping_cost"],
        discount: json["discount"],
        walletPaymentDiscount: json["wallet_payment_discount"],
        walletPaymentDiscountValue:
            double.tryParse(json["wallet_payment_discount_value"].toString()) ??
            0.0,
        walletPaymentDiscountPercent:
            double.tryParse(
              json["wallet_payment_discount_percent"].toString(),
            ) ??
            0.0,
        walletPaymentDiscountApplied: json["wallet_payment_discount_applied"],
        grandTotal: json["grand_total"],
        grandTotalValue:
            double.tryParse(json["grand_total_value"].toString()) ?? 0.0,
        couponCode: json["coupon_code"],
        couponApplied: json["coupon_applied"],
        totalProduct: json["total_items"],
        clubPoint: json["club_point"],
      );

  Map<String, dynamic> toJson() => {
    "sub_total": subTotal,
    "tax": tax,
    "gst": gst,
    "shipping_cost": shippingCost,
    "discount": discount,
    "wallet_payment_discount": walletPaymentDiscount,
    "wallet_payment_discount_value": walletPaymentDiscountValue,
    "wallet_payment_discount_percent": walletPaymentDiscountPercent,
    "wallet_payment_discount_applied": walletPaymentDiscountApplied,
    "grand_total": grandTotal,
    "grand_total_value": grandTotalValue,
    "coupon_code": couponCode,
    "coupon_applied": couponApplied,
    "total_items": totalProduct,
    "club_point": clubPoint,
  };
}
