// To parse this JSON data, do
//
//     final orderDetailResponse = orderDetailResponseFromJson(jsonString);
//https://app.quicktype.io/
// ignore_for_file: non_constant_identifier_names, prefer_typing_uninitialized_variables

import 'dart:convert';

OrderDetailResponse orderDetailResponseFromJson(String str) =>
    OrderDetailResponse.fromJson(json.decode(str));

String orderDetailResponseToJson(OrderDetailResponse data) =>
    json.encode(data.toJson());

class OrderDetailResponse {
  OrderDetailResponse({this.detailed_orders, this.success, this.status});

  List<DetailedOrder>? detailed_orders;
  bool? success;
  int? status;

  factory OrderDetailResponse.fromJson(Map<String, dynamic> json) =>
      OrderDetailResponse(
        detailed_orders: List<DetailedOrder>.from(
          json["data"].map((x) => DetailedOrder.fromJson(x)),
        ),
        success: json["success"],
        status: json["status"],
      );

  Map<String, dynamic> toJson() => {
    "data": List<dynamic>.from(detailed_orders!.map((x) => x.toJson())),
    "success": success,
    "status": status,
  };
}

class DetailedOrder {
  DetailedOrder({
    this.id,
    this.code,
    this.user_id,
    this.manually_payable,
    this.shipping_address,
    this.billing_address,
    this.pickupPoint,
    this.shipping_type,
    this.shipping_type_string,
    this.payment_type,
    this.payment_status,
    this.seller_address,
    this.payment_status_string,
    this.delivery_status,
    this.delivery_status_string,
    this.grand_total,
    this.plane_grand_total,
    this.coupon_discount,
    this.shipping_cost,
    this.subtotal,
    this.tax,
    this.gst_amount,
    this.gstin,
    this.gst_applicable,
    this.date,
    this.links,
    this.cancelRequest,
    this.delivery_boy,
    this.deliveryVerificationStatus,
    this.deliveryVerifiedAt,
    this.deliveryVerifiedBy,
    this.deliveryVerificationSource,
    this.customerPickupQrPayload,
    this.customerPickupQrImage,
  });

  int? id;
  String? code;
  int? user_id;
  bool? manually_payable;
  ShippingAddress? shipping_address;
  ShippingAddress? billing_address;
  String? seller_address;
  PickupPoint? pickupPoint;
  String? shipping_type;
  String? shipping_type_string;
  String? payment_type;
  String? payment_status;
  String? payment_status_string;
  String? delivery_status;
  String? delivery_status_string;
  String? grand_total;
  var plane_grand_total;
  String? coupon_discount;
  String? shipping_cost;
  String? subtotal;
  String? tax;
  String? gst_amount;
  String? gstin;
  int? gst_applicable;
  String? date;
  Links? links;
  bool? cancelRequest;
  DeliveryContact? delivery_boy;
  bool? deliveryVerificationStatus;
  String? deliveryVerifiedAt;
  int? deliveryVerifiedBy;
  String? deliveryVerificationSource;
  String? customerPickupQrPayload;
  String? customerPickupQrImage;

  factory DetailedOrder.fromJson(Map<String, dynamic> json) => DetailedOrder(
    id: json["id"],
    code: json["code"],
    user_id: json["user_id"],
    manually_payable: json["manually_payable"],
    //shipping_address: ShippingAddress.fromJson(json["shipping_address"]),
    shipping_address: json["shipping_address"].isEmpty
        ? null
        : ShippingAddress.fromJson(json["shipping_address"]),
    billing_address:
        json["billing_address"] == null ||
            (json["billing_address"] is List && json["billing_address"].isEmpty)
        ? null
        : ShippingAddress.fromJson(json["billing_address"]),
    pickupPoint: json["pickup_point"] == null
        ? null
        : PickupPoint.fromJson(json["pickup_point"]),
    shipping_type: json["shipping_type"],
    shipping_type_string: json["shipping_type_string"],
    seller_address: json['seller_address'],
    payment_type: json["payment_type"],
    payment_status: json["payment_status"],
    payment_status_string: json["payment_status_string"],
    delivery_status: json["delivery_status"],
    delivery_status_string: json["delivery_status_string"],
    grand_total: json["grand_total"],
    plane_grand_total: json["plane_grand_total"],
    coupon_discount: json["coupon_discount"],
    shipping_cost: json["shipping_cost"],
    subtotal: json["subtotal"],
    tax: json["tax"],
    gst_amount: json["gst_amount"],
    gstin: json["gstin"],
    gst_applicable: json["gst_applicable"],
    date: json["date"],
    links: Links.fromJson(json["links"]),
    delivery_boy: json["delivery_boy"] == null
        ? null
        : DeliveryContact.fromJson(json["delivery_boy"]),
    deliveryVerificationStatus: json["delivery_verification_status"],
    deliveryVerifiedAt: json["delivery_verified_at"],
    deliveryVerifiedBy: json["delivery_verified_by"],
    deliveryVerificationSource: json["delivery_verification_source"],
    customerPickupQrPayload: json["customer_pickup_qr_payload"],
    customerPickupQrImage: json["customer_pickup_qr_image"],
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "code": code,
    "user_id": user_id,
    "manually_payable": manually_payable,
    "shipping_address": shipping_address?.toJson(),
    "billing_address": billing_address?.toJson(),
    "pickup_point": pickupPoint?.toJson(),
    "shipping_type": shipping_type,
    "shipping_type_string": shipping_type_string,
    "payment_type": payment_type,
    "payment_status": payment_status,
    "payment_status_string": payment_status_string,
    "delivery_status": delivery_status,
    "delivery_status_string": delivery_status_string,
    "grand_total": grand_total,
    "plane_grand_total": plane_grand_total,
    "coupon_discount": coupon_discount,
    "shipping_cost": shipping_cost,
    "subtotal": subtotal,
    "tax": tax,
    "gst_amount": gst_amount,
    "gstin": gstin,
    "gst_applicable": gst_applicable,
    "date": date,
    "links": links!.toJson(),
    "delivery_boy": delivery_boy?.toJson(),
    "delivery_verification_status": deliveryVerificationStatus,
    "delivery_verified_at": deliveryVerifiedAt,
    "delivery_verified_by": deliveryVerifiedBy,
    "delivery_verification_source": deliveryVerificationSource,
    "customer_pickup_qr_payload": customerPickupQrPayload,
    "customer_pickup_qr_image": customerPickupQrImage,
  };
}

class DeliveryContact {
  DeliveryContact({
    this.id,
    this.name,
    this.phone,
    this.avatarOriginal,
  });

  int? id;
  String? name;
  String? phone;
  String? avatarOriginal;

  factory DeliveryContact.fromJson(Map<String, dynamic> json) =>
      DeliveryContact(
        id: json["id"],
        name: json["name"],
        phone: json["phone"],
        avatarOriginal: json["avatar_original"],
      );

  Map<String, dynamic> toJson() => {
    "id": id,
    "name": name,
    "phone": phone,
    "avatar_original": avatarOriginal,
  };
}

class Links {
  Links({this.details});

  String? details;

  factory Links.fromJson(Map<String, dynamic> json) =>
      Links(details: json["details"]);

  Map<String, dynamic> toJson() => {"details": details};
}

class ShippingAddress {
  ShippingAddress({
    this.name,
    this.email,
    this.address,
    this.country,
    this.state,
    this.city,
    this.postal_code,
    this.phone,
    this.checkout_type,
  });

  String? name;
  String? email;
  String? address;
  String? country;
  String? state;
  String? city;
  String? postal_code;
  String? phone;
  String? checkout_type;

  factory ShippingAddress.fromJson(Map<String, dynamic> json) =>
      ShippingAddress(
        name: json["name"],
        email: json["email"],
        address: json["address"],
        country: json["country"],
        state: json["state"],
        city: json["city"],
        postal_code: json["postal_code"],
        phone: json["phone"],
        checkout_type: json["checkout_type"],
      );

  Map<String, dynamic> toJson() => {
    "name": name,
    "email": email,
    "address": address,
    "country": country,
    "state": state,
    "city": city,
    "postal_code": postal_code,
    "phone": phone,
    "checkout_type": checkout_type,
  };
}

class PickupPoint {
  PickupPoint({
    this.id,
    this.staffId,
    this.name,
    this.address,
    this.phone,
    this.internalCode,
    this.openingTime,
    this.closingTime,
    this.workingHours,
    this.pickupHoldDays,
    this.instructions,
    this.supportsReturn,
    this.supportsCod,
    this.latitude,
    this.longitude,
    this.pickupWindowDeadline,
    this.pickupWindowDaysLeft,
    this.isReturnDue,
    this.pickUpStatus,
    this.cashOnPickupStatus,
    this.createdAt,
    this.updatedAt,
  });

  int? id;
  int? staffId;
  String? name;
  String? address;
  String? phone;
  String? internalCode;
  String? openingTime;
  String? closingTime;
  String? workingHours;
  int? pickupHoldDays;
  String? instructions;
  bool? supportsReturn;
  bool? supportsCod;
  dynamic latitude;
  dynamic longitude;
  String? pickupWindowDeadline;
  int? pickupWindowDaysLeft;
  bool? isReturnDue;
  int? pickUpStatus;
  dynamic cashOnPickupStatus;
  DateTime? createdAt;
  DateTime? updatedAt;

  factory PickupPoint.fromJson(Map<String, dynamic> json) => PickupPoint(
    id: json["id"],
    staffId: json["staff_id"],
    name: json["name"],
    address: json["address"],
    phone: json["phone"],
    internalCode: json["internal_code"],
    openingTime: json["opening_time"],
    closingTime: json["closing_time"],
    workingHours: json["working_hours"],
    pickupHoldDays: json["pickup_hold_days"],
    instructions: json["instructions"],
    supportsReturn: json["supports_return"],
    supportsCod: json["supports_cod"],
    latitude: json["latitude"],
    longitude: json["longitude"],
    pickupWindowDeadline: json["pickup_window_deadline"],
    pickupWindowDaysLeft: json["pickup_window_days_left"],
    isReturnDue: json["is_return_due"],
    pickUpStatus: json["pick_up_status"],
    cashOnPickupStatus: json["cash_on_pickup_status"],
    createdAt: json["created_at"] == null
        ? null
        : DateTime.parse(json["created_at"]),
    updatedAt: json["updated_at"] == null
        ? null
        : DateTime.parse(json["updated_at"]),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "staff_id": staffId,
    "name": name,
    "address": address,
    "phone": phone,
    "internal_code": internalCode,
    "opening_time": openingTime,
    "closing_time": closingTime,
    "working_hours": workingHours,
    "pickup_hold_days": pickupHoldDays,
    "instructions": instructions,
    "supports_return": supportsReturn,
    "supports_cod": supportsCod,
    "latitude": latitude,
    "longitude": longitude,
    "pickup_window_deadline": pickupWindowDeadline,
    "pickup_window_days_left": pickupWindowDaysLeft,
    "is_return_due": isReturnDue,
    "pick_up_status": pickUpStatus,
    "cash_on_pickup_status": cashOnPickupStatus,
    "created_at": createdAt?.toIso8601String(),
    "updated_at": updatedAt?.toIso8601String(),
  };
}
