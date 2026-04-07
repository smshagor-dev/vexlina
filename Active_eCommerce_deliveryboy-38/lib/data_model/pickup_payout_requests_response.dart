import 'dart:convert';

PickupPayoutRequestsResponse pickupPayoutRequestsResponseFromJson(String str) =>
    PickupPayoutRequestsResponse.fromJson(json.decode(str));

class PickupPayoutRequestsResponse {
  PickupPayoutRequestsResponse({
    this.data,
    this.meta,
    this.success,
    this.status,
  });

  List<PickupPayoutRequestItem>? data;
  PickupPayoutRequestsMeta? meta;
  bool? success;
  int? status;

  factory PickupPayoutRequestsResponse.fromJson(Map<String, dynamic> json) =>
      PickupPayoutRequestsResponse(
        data: json["data"] == null
            ? []
            : List<PickupPayoutRequestItem>.from(
                json["data"].map((x) => PickupPayoutRequestItem.fromJson(x))),
        meta: json["meta"] == null
            ? null
            : PickupPayoutRequestsMeta.fromJson(json["meta"]),
        success: json["success"],
        status: json["status"],
      );
}

class PickupPayoutRequestItem {
  PickupPayoutRequestItem({
    this.id,
    this.amount,
    this.amountValue,
    this.status,
    this.statusLabel,
    this.message,
    this.adminNote,
    this.paymentMethod,
    this.paymentReference,
    this.requestedAt,
    this.processedAt,
  });

  int? id;
  String? amount;
  num? amountValue;
  int? status;
  String? statusLabel;
  String? message;
  String? adminNote;
  String? paymentMethod;
  String? paymentReference;
  String? requestedAt;
  String? processedAt;

  factory PickupPayoutRequestItem.fromJson(Map<String, dynamic> json) =>
      PickupPayoutRequestItem(
        id: json["id"],
        amount: json["amount"],
        amountValue: json["amount_value"],
        status: json["status"],
        statusLabel: json["status_label"],
        message: json["message"],
        adminNote: json["admin_note"],
        paymentMethod: json["payment_method"],
        paymentReference: json["payment_reference"],
        requestedAt: json["requested_at"],
        processedAt: json["processed_at"],
      );
}

class PickupPayoutRequestsMeta {
  PickupPayoutRequestsMeta({
    this.currentPage,
    this.lastPage,
    this.perPage,
    this.total,
  });

  int? currentPage;
  int? lastPage;
  int? perPage;
  int? total;

  factory PickupPayoutRequestsMeta.fromJson(Map<String, dynamic> json) =>
      PickupPayoutRequestsMeta(
        currentPage: json["current_page"],
        lastPage: json["last_page"],
        perPage: json["per_page"],
        total: json["total"],
      );
}
