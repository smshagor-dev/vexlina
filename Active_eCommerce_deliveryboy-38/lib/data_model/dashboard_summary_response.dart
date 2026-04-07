// To parse this JSON data, do
//
//     final dashboardSummaryResponse = dashboardSummaryResponseFromJson(jsonString);

import 'dart:convert';

DashboardSummaryResponse dashboardSummaryResponseFromJson(String str) => DashboardSummaryResponse.fromJson(json.decode(str));

String dashboardSummaryResponseToJson(DashboardSummaryResponse data) => json.encode(data.toJson());

class DashboardSummaryResponse {
  DashboardSummaryResponse({
    this.completed_delivery,
    this.pending_delivery,
    this.total_collection,
    this.total_earning,
    this.cancelled,
    this.on_the_way,
    this.picked,
    this.assigned,
    this.reached,
    this.return_orders,
    this.pickup_point_name,
    this.pickup_point,
    this.earning_summary,
    this.upcoming_orders,
    this.picked_up_orders,
    this.on_the_way_orders,
    this.reached_orders,
    this.completed_orders,
    this.return_due_orders_count,
    this.return_due_orders,
  });

  int? completed_delivery;
  int? pending_delivery;
  String? total_collection;
  String? total_earning;
  int? cancelled;
  int? on_the_way;
  int? picked;
  int? assigned;
  int? reached;
  int? return_orders;
  String? pickup_point_name;
  PickupPointSummary? pickup_point;
  List<EarningSummaryWindow>? earning_summary;
  int? upcoming_orders;
  int? picked_up_orders;
  int? on_the_way_orders;
  int? reached_orders;
  int? completed_orders;
  int? return_due_orders_count;
  List<ReturnDueOrder>? return_due_orders;

  factory DashboardSummaryResponse.fromJson(Map<String, dynamic> json) => DashboardSummaryResponse(
    completed_delivery: json["completed_delivery"],
    pending_delivery: json["pending_delivery"],
    total_collection: json["total_collection"],
    total_earning: json["total_earning"],
    cancelled: json["cancelled"],
    on_the_way: json["on_the_way"],
    picked: json["picked"],
    assigned: json["assigned"],
    reached: json["reached"] ?? json["reached_orders"],
    return_orders: json["return_orders"] ?? json["cancelled"],
    pickup_point_name: json["pickup_point"] is Map ? json["pickup_point"]["name"] : null,
    pickup_point: json["pickup_point"] is Map
        ? PickupPointSummary.fromJson(json["pickup_point"])
        : null,
    earning_summary: json["earning_summary"] == null
        ? []
        : List<EarningSummaryWindow>.from(
            json["earning_summary"].map((x) => EarningSummaryWindow.fromJson(x))),
    upcoming_orders: json["upcoming_orders"] ?? json["assigned"],
    picked_up_orders: json["picked_up_orders"] ?? json["picked"],
    on_the_way_orders: json["on_the_way_orders"] ?? json["on_the_way"],
    reached_orders: json["reached_orders"] ?? json["reached"],
    completed_orders: json["completed_orders"] ?? json["completed_delivery"],
    return_due_orders_count: json["return_due_orders_count"],
    return_due_orders: json["return_due_orders"] == null
        ? []
        : List<ReturnDueOrder>.from(
            json["return_due_orders"].map((x) => ReturnDueOrder.fromJson(x))),
  );

  Map<String, dynamic> toJson() => {
    "completed_delivery": completed_delivery,
    "pending_delivery": pending_delivery,
    "total_collection": total_collection,
    "total_earning": total_earning,
    "cancelled": cancelled,
    "on_the_way": on_the_way,
    "picked": picked,
    "assigned": assigned,
    "reached": reached,
    "return_orders": return_orders,
    "pickup_point_name": pickup_point_name,
    "pickup_point": pickup_point?.toJson(),
    "earning_summary":
        earning_summary == null ? [] : earning_summary!.map((x) => x.toJson()).toList(),
    "upcoming_orders": upcoming_orders,
    "picked_up_orders": picked_up_orders,
    "on_the_way_orders": on_the_way_orders,
    "reached_orders": reached_orders,
    "completed_orders": completed_orders,
    "return_due_orders_count": return_due_orders_count,
    "return_due_orders":
        return_due_orders == null ? [] : return_due_orders!.map((x) => x.toJson()).toList(),
  };
}

class PickupPointSummary {
  PickupPointSummary({
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

  static double? _parseDouble(dynamic value) {
    if (value == null) {
      return null;
    }
    if (value is num) {
      return value.toDouble();
    }
    return double.tryParse(value.toString());
  }

  factory PickupPointSummary.fromJson(Map<String, dynamic> json) => PickupPointSummary(
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
      };
}

class EarningSummaryWindow {
  EarningSummaryWindow({
    this.label,
    this.delivery_earning,
    this.delivery_earning_string,
    this.return_earning,
    this.return_earning_string,
    this.total_earning,
    this.total_earning_string,
  });

  String? label;
  num? delivery_earning;
  String? delivery_earning_string;
  num? return_earning;
  String? return_earning_string;
  num? total_earning;
  String? total_earning_string;

  factory EarningSummaryWindow.fromJson(Map<String, dynamic> json) =>
      EarningSummaryWindow(
        label: json["label"],
        delivery_earning: json["delivery_earning"],
        delivery_earning_string: json["delivery_earning_string"],
        return_earning: json["return_earning"],
        return_earning_string: json["return_earning_string"],
        total_earning: json["total_earning"],
        total_earning_string: json["total_earning_string"],
      );

  Map<String, dynamic> toJson() => {
        "label": label,
        "delivery_earning": delivery_earning,
        "delivery_earning_string": delivery_earning_string,
        "return_earning": return_earning,
        "return_earning_string": return_earning_string,
        "total_earning": total_earning,
        "total_earning_string": total_earning_string,
      };
}

class ReturnDueOrder {
  ReturnDueOrder({
    this.id,
    this.code,
    this.reached_at,
  });

  int? id;
  String? code;
  String? reached_at;

  factory ReturnDueOrder.fromJson(Map<String, dynamic> json) => ReturnDueOrder(
        id: json["id"],
        code: json["code"],
        reached_at: json["reached_at"],
      );

  Map<String, dynamic> toJson() => {
        "id": id,
        "code": code,
        "reached_at": reached_at,
      };
}
