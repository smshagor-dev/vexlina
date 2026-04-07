// To parse this JSON data, do
//
//     final orderMiniResponse = orderMiniResponseFromJson(jsonString);
// https://app.quicktype.io/
import 'dart:convert';

OrderMiniResponse orderMiniResponseFromJson(String str) =>
    OrderMiniResponse.fromJson(json.decode(str));

String orderMiniResponseToJson(OrderMiniResponse data) =>
    json.encode(data.toJson());

class OrderMiniResponse {
  OrderMiniResponse({
    this.orders,
    this.links,
    this.meta,
    this.success,
    this.status,
  });

  List<Order>? orders;
  OrderMiniResponseLinks? links;
  Meta? meta;
  bool? success;
  int? status;

  factory OrderMiniResponse.fromJson(Map<String, dynamic> json) =>
      OrderMiniResponse(
        orders: json["data"] == null
            ? <Order>[]
            : List<Order>.from(
                (json["data"] as List).map((x) => Order.fromJson(x)),
              ),
        links: json["links"] == null
            ? null
            : OrderMiniResponseLinks.fromJson(json["links"]),
        meta: json["meta"] == null ? null : Meta.fromJson(json["meta"]),
        success: json["success"],
        status: json["status"],
      );

  Map<String, dynamic> toJson() => {
    "data": List<dynamic>.from(orders!.map((x) => x.toJson())),
    "links": links!.toJson(),
    "meta": meta?.toJson(),
    "success": success,
    "status": status,
  };
}

class Order {
  Order({
    this.id,
    this.code,
    this.userId,
    this.shippingType,
    this.pickupPoint,
    this.paymentType,
    this.paymentStatus,
    this.paymentStatusString,
    this.deliveryStatus,
    this.deliveryStatusString,
    this.grandTotal,
    this.date,
    this.deliveryVerificationStatus,
    this.deliveryVerifiedAt,
    this.customerPickupQrPayload,
    this.customerPickupQrImage,
    this.links,
  });

  int? id;
  String? code;
  int? userId;
  String? shippingType;
  PickupPoint? pickupPoint;
  String? paymentType;
  String? paymentStatus;
  String? paymentStatusString;
  String? deliveryStatus;
  String? deliveryStatusString;
  String? grandTotal;
  String? date;
  bool? deliveryVerificationStatus;
  String? deliveryVerifiedAt;
  String? customerPickupQrPayload;
  String? customerPickupQrImage;
  OrderLinks? links;

  factory Order.fromJson(Map<String, dynamic> json) => Order(
    id: json["id"],
    code: json["code"],
    userId: json["user_id"],
    shippingType: json["shipping_type"],
    pickupPoint: json["pickup_point"] == null
        ? null
        : PickupPoint.fromJson(json["pickup_point"]),
    paymentType: json["payment_type"],
    paymentStatus: json["payment_status"],
    paymentStatusString: json["payment_status_string"],
    deliveryStatus: json["delivery_status"],
    deliveryStatusString: json["delivery_status_string"],
    grandTotal: json["grand_total"],
    date: json["date"],
    deliveryVerificationStatus: json["delivery_verification_status"],
    deliveryVerifiedAt: json["delivery_verified_at"],
    customerPickupQrPayload: json["customer_pickup_qr_payload"],
    customerPickupQrImage: json["customer_pickup_qr_image"],
    links: json["links"] == null ? null : OrderLinks.fromJson(json["links"]),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "code": code,
    "user_id": userId,
    "shipping_type": shippingType,
    "pickup_point": pickupPoint?.toJson(),
    "payment_type": paymentType,
    "payment_status": paymentStatus,
    "payment_status_string": paymentStatusString,
    "delivery_status": deliveryStatus,
    "delivery_status_string": deliveryStatusString,
    "grand_total": grandTotal,
    "date": date,
    "delivery_verification_status": deliveryVerificationStatus,
    "delivery_verified_at": deliveryVerifiedAt,
    "customer_pickup_qr_payload": customerPickupQrPayload,
    "customer_pickup_qr_image": customerPickupQrImage,
    "links": links!.toJson(),
  };
}

class OrderLinks {
  OrderLinks({this.details});

  String? details;

  factory OrderLinks.fromJson(Map<String, dynamic> json) =>
      OrderLinks(details: json["details"]);

  Map<String, dynamic> toJson() => {"details": details};
}

class OrderMiniResponseLinks {
  OrderMiniResponseLinks({this.first, this.last, this.prev, this.next});

  dynamic first;
  dynamic last;
  dynamic prev;
  dynamic next;

  factory OrderMiniResponseLinks.fromJson(Map<String, dynamic> json) =>
      OrderMiniResponseLinks(
        first: json["first"],
        last: json["last"],
        prev: json["prev"],
        next: json["next"],
      );

  Map<String, dynamic> toJson() => {
    "first": first,
    "last": last,
    "prev": prev,
    "next": next,
  };
}

class Meta {
  Meta({
    this.currentPage,
    this.from,
    this.lastPage,
    this.path,
    this.perPage,
    this.to,
    this.total,
  });

  int? currentPage;
  int? from;
  int? lastPage;
  String? path;
  int? perPage;
  int? to;
  int? total;

  factory Meta.fromJson(Map<String, dynamic> json) => Meta(
    currentPage: json["current_page"],
    from: json["from"],
    lastPage: json["last_page"],
    path: json["path"],
    perPage: json["per_page"],
    to: json["to"],
    total: json["total"],
  );

  Map<String, dynamic> toJson() => {
    "current_page": currentPage,
    "from": from,
    "last_page": lastPage,
    "path": path,
    "per_page": perPage,
    "to": to,
    "total": total,
  };
}

class PickupPoint {
  PickupPoint({
    this.id,
    this.name,
    this.address,
    this.phone,
    this.internalCode,
    this.workingHours,
    this.pickupHoldDays,
    this.pickupWindowDeadline,
    this.pickupWindowDaysLeft,
    this.isReturnDue,
  });

  int? id;
  String? name;
  String? address;
  String? phone;
  String? internalCode;
  String? workingHours;
  int? pickupHoldDays;
  String? pickupWindowDeadline;
  int? pickupWindowDaysLeft;
  bool? isReturnDue;

  factory PickupPoint.fromJson(Map<String, dynamic> json) => PickupPoint(
    id: json["id"],
    name: json["name"],
    address: json["address"],
    phone: json["phone"],
    internalCode: json["internal_code"],
    workingHours: json["working_hours"],
    pickupHoldDays: json["pickup_hold_days"],
    pickupWindowDeadline: json["pickup_window_deadline"],
    pickupWindowDaysLeft: json["pickup_window_days_left"],
    isReturnDue: json["is_return_due"],
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "name": name,
    "address": address,
    "phone": phone,
    "internal_code": internalCode,
    "working_hours": workingHours,
    "pickup_hold_days": pickupHoldDays,
    "pickup_window_deadline": pickupWindowDeadline,
    "pickup_window_days_left": pickupWindowDaysLeft,
    "is_return_due": isReturnDue,
  };
}
