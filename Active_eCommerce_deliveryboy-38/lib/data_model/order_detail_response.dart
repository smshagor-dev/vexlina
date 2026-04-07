// To parse this JSON data, do
//
//     final orderDetailResponse = orderDetailResponseFromJson(jsonString);
//https://app.quicktype.io/
import 'dart:convert';

OrderDetailResponse orderDetailResponseFromJson(String str) => OrderDetailResponse.fromJson(json.decode(str));

String orderDetailResponseToJson(OrderDetailResponse data) => json.encode(data.toJson());

class OrderDetailResponse {
  OrderDetailResponse({
    this.detailed_orders,
    this.success,
    this.status,
  });

  List<DetailedOrder>? detailed_orders;
  bool? success;
  int? status;

  factory OrderDetailResponse.fromJson(Map<String, dynamic> json) => OrderDetailResponse(
    detailed_orders: List<DetailedOrder>.from(json["data"].map((x) => DetailedOrder.fromJson(x))),
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
    this.shipping_address,
    this.pickup_point,
    this.shipping_type,
    this.shipping_type_string,
    this.payment_type,
    this.payment_status,
    this.payment_status_string,
    this.delivery_status,
    this.delivery_status_string,
    this.grand_total,
    this.coupon_discount,
    this.shipping_cost,
    this.subtotal,
    this.tax,
    this.date,
    this.cancel_request,
    this.delivery_verification_status,
    this.delivery_verified_at,
    this.delivery_verified_by,
    this.delivery_verification_source,
    this.customer_pickup_qr_payload,
    this.customer_pickup_qr_image,
    this.links,
  });

  int? id;
  String? code;
  int? user_id;
  ShippingAddress? shipping_address;
  OrderPickupPoint? pickup_point;
  String? shipping_type;
  String? shipping_type_string;
  String? payment_type;
  String? payment_status;
  String? payment_status_string;
  String? delivery_status;
  String? delivery_status_string;
  String? grand_total;
  String? coupon_discount;
  String? shipping_cost;
  String? subtotal;
  String? tax;
  String? date;
  bool? cancel_request;
  bool? delivery_verification_status;
  String? delivery_verified_at;
  dynamic delivery_verified_by;
  String? delivery_verification_source;
  String? customer_pickup_qr_payload;
  String? customer_pickup_qr_image;
  Links? links;

  factory DetailedOrder.fromJson(Map<String, dynamic> json) => DetailedOrder(
    id: json["id"],
    code: json["code"],
    user_id: json["user_id"],
    shipping_address: ShippingAddress.fromJson(json["shipping_address"]),
    pickup_point: json["pickup_point"] == null
        ? null
        : OrderPickupPoint.fromJson(json["pickup_point"]),
    shipping_type: json["shipping_type"],
    shipping_type_string: json["shipping_type_string"],
    payment_type: json["payment_type"],
    payment_status: json["payment_status"],
    payment_status_string: json["payment_status_string"],
    delivery_status: json["delivery_status"],
    delivery_status_string: json["delivery_status_string"],
    grand_total: json["grand_total"],
    coupon_discount: json["coupon_discount"],
    shipping_cost: json["shipping_cost"],
    subtotal: json["subtotal"],
    tax: json["tax"],
    date: json["date"],
    cancel_request: json["cancel_request"],
    delivery_verification_status: json["delivery_verification_status"],
    delivery_verified_at: json["delivery_verified_at"],
    delivery_verified_by: json["delivery_verified_by"],
    delivery_verification_source: json["delivery_verification_source"],
    customer_pickup_qr_payload: json["customer_pickup_qr_payload"],
    customer_pickup_qr_image: json["customer_pickup_qr_image"],
    links: Links.fromJson(json["links"]),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "code": code,
    "user_id": user_id,
    "shipping_address": shipping_address!.toJson(),
    "pickup_point": pickup_point?.toJson(),
    "shipping_type": shipping_type,
    "shipping_type_string": shipping_type_string,
    "payment_type": payment_type,
    "payment_status": payment_status,
    "payment_status_string": payment_status_string,
    "delivery_status": delivery_status,
    "delivery_status_string": delivery_status_string,
    "grand_total": grand_total,
    "coupon_discount": coupon_discount,
    "shipping_cost": shipping_cost,
    "subtotal": subtotal,
    "tax": tax,
    "date": date,
    "cancel_request": cancel_request,
    "delivery_verification_status": delivery_verification_status,
    "delivery_verified_at": delivery_verified_at,
    "delivery_verified_by": delivery_verified_by,
    "delivery_verification_source": delivery_verification_source,
    "customer_pickup_qr_payload": customer_pickup_qr_payload,
    "customer_pickup_qr_image": customer_pickup_qr_image,
    "links": links!.toJson(),
  };
}

class OrderPickupPoint {
  OrderPickupPoint({
    this.id,
    this.name,
    this.address,
    this.phone,
    this.internal_code,
    this.opening_time,
    this.closing_time,
    this.working_hours,
    this.pickup_hold_days,
    this.instructions,
    this.supports_return,
    this.supports_cod,
    this.latitude,
    this.longitude,
    this.pickup_window_deadline,
    this.pickup_window_days_left,
    this.is_return_due,
  });

  int? id;
  String? name;
  String? address;
  String? phone;
  String? internal_code;
  String? opening_time;
  String? closing_time;
  String? working_hours;
  int? pickup_hold_days;
  String? instructions;
  bool? supports_return;
  bool? supports_cod;
  double? latitude;
  double? longitude;
  String? pickup_window_deadline;
  int? pickup_window_days_left;
  bool? is_return_due;

  static double? _parseDouble(dynamic value) {
    if (value == null) {
      return null;
    }
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value.toString());
  }

  factory OrderPickupPoint.fromJson(Map<String, dynamic> json) =>
      OrderPickupPoint(
        id: json["id"],
        name: json["name"],
        address: json["address"],
        phone: json["phone"],
        internal_code: json["internal_code"],
        opening_time: json["opening_time"],
        closing_time: json["closing_time"],
        working_hours: json["working_hours"],
        pickup_hold_days: json["pickup_hold_days"],
        instructions: json["instructions"],
        supports_return: json["supports_return"],
        supports_cod: json["supports_cod"],
        latitude: _parseDouble(json["latitude"]),
        longitude: _parseDouble(json["longitude"]),
        pickup_window_deadline: json["pickup_window_deadline"],
        pickup_window_days_left: json["pickup_window_days_left"],
        is_return_due: json["is_return_due"],
      );

  Map<String, dynamic> toJson() => {
        "id": id,
        "name": name,
        "address": address,
        "phone": phone,
        "internal_code": internal_code,
        "opening_time": opening_time,
        "closing_time": closing_time,
        "working_hours": working_hours,
        "pickup_hold_days": pickup_hold_days,
        "instructions": instructions,
        "supports_return": supports_return,
        "supports_cod": supports_cod,
        "latitude": latitude,
        "longitude": longitude,
        "pickup_window_deadline": pickup_window_deadline,
        "pickup_window_days_left": pickup_window_days_left,
        "is_return_due": is_return_due,
      };
}

class Links {
  Links({
    this.details,
  });

  String? details;

  factory Links.fromJson(Map<String, dynamic> json) => Links(
    details: json["details"],
  );

  Map<String, dynamic> toJson() => {
    "details": details,
  };
}

class ShippingAddress {
  ShippingAddress({
    this.name,
    this.email,
    this.address,
    this.country,
    this.city,
    this.postal_code,
    this.phone,
    this.checkout_type,
  });

  String? name;
  String? email;
  String? address;
  String? country;
  String? city;
  String? postal_code;
  String? phone;
  String? checkout_type;

  factory ShippingAddress.fromJson(Map<String, dynamic> json) => ShippingAddress(
    name: json["name"] == null ? null : json["name"],
    email: json["email"] == null ? null : json["email"],
    address: json["address"] == null ? null : json["address"],
    country: json["country"] == null ? null : json["country"],
    city: json["city"] == null ? null : json["city"],
    postal_code: json["postal_code"] == null ? null : json["postal_code"],
    phone: json["phone"] == null ? null : json["phone"],
    checkout_type: json["checkout_type"] == null ? null : json["checkout_type"],
  );

  Map<String, dynamic> toJson() => {
    "name": name == null ? null : name,
    "email": email == null ? null : email,
    "address": address == null ? null : address,
    "country": country == null ? null : country,
    "city": city == null ? null : city,
    "postal_code": postal_code == null ? null : postal_code,
    "phone": phone == null ? null : phone,
    "checkout_type": checkout_type == null ? null : checkout_type,
  };
}
