// To parse this JSON data, do
//
//     final walletRechargeResponse = walletRechargeResponseFromJson(jsonString);
//https://app.quicktype.io/
import 'dart:convert';

WalletRechargeResponse walletRechargeResponseFromJson(String str) =>
    WalletRechargeResponse.fromJson(json.decode(str));

String walletRechargeResponseToJson(WalletRechargeResponse data) =>
    json.encode(data.toJson());

class WalletRechargeResponse {
  WalletRechargeResponse({
    this.recharges,
    this.links,
    this.meta,
    this.result,
    this.status,
  });

  List<Recharge>? recharges;
  Links? links;
  Meta? meta;
  bool? result;
  int? status;

  factory WalletRechargeResponse.fromJson(Map<String, dynamic> json) =>
      WalletRechargeResponse(
        recharges: json["data"] == null
            ? <Recharge>[]
            : List<Recharge>.from(
                json["data"].map((x) => Recharge.fromJson(x)),
              ),
        links: json["links"] == null
            ? null
            : Links.fromJson(Map<String, dynamic>.from(json["links"])),
        meta: json["meta"] == null
            ? Meta(
                currentPage: 1,
                from: 0,
                lastPage: 1,
                perPage: 0,
                to: 0,
                total: 0,
              )
            : Meta.fromJson(Map<String, dynamic>.from(json["meta"])),
        result: json["result"] ?? false,
        status: json["status"] ?? 200,
      );

  Map<String, dynamic> toJson() => {
    "data": List<dynamic>.from(recharges!.map((x) => x.toJson())),
    "links": links!.toJson(),
    "meta": meta!.toJson(),
    "result": result,
    "status": status,
  };
}

class Recharge {
  Recharge({
    this.transactionNumber,
    this.amount,
    this.rawAmount,
    this.paymentMethod,
    this.approvalString,
    this.direction,
    this.counterparty,
    this.date,
  });

  String? transactionNumber;
  String? amount;
  double? rawAmount;
  String? paymentMethod;
  String? approvalString;
  String? direction;
  String? counterparty;
  String? date;

  factory Recharge.fromJson(Map<String, dynamic> json) => Recharge(
    transactionNumber: json["transaction_number"]?.toString(),
    amount: json["amount"]?.toString(),
    rawAmount: double.tryParse(json["raw_amount"].toString()),
    paymentMethod: json["payment_method"]?.toString(),
    approvalString: json["approval_string"]?.toString(),
    direction: json["direction"]?.toString(),
    counterparty: json["counterparty"]?.toString(),
    date: json["date"]?.toString(),
  );

  Map<String, dynamic> toJson() => {
    "transaction_number": transactionNumber,
    "amount": amount,
    "raw_amount": rawAmount,
    "payment_method": paymentMethod,
    "approval_string": approvalString,
    "direction": direction,
    "counterparty": counterparty,
    "date": date,
  };
}

class Links {
  Links({this.first, this.last, this.prev, this.next});

  String? first;
  String? last;
  dynamic prev;
  dynamic next;

  factory Links.fromJson(Map<String, dynamic> json) => Links(
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
    currentPage: json["current_page"] ?? 1,
    from: json["from"] ?? 0,
    lastPage: json["last_page"] ?? 1,
    path: json["path"]?.toString(),
    perPage: json["per_page"] ?? 0,
    to: json["to"] ?? 0,
    total: json["total"] ?? 0,
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
