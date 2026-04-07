import 'dart:convert';

PickupPayoutSummaryResponse pickupPayoutSummaryResponseFromJson(String str) =>
    PickupPayoutSummaryResponse.fromJson(json.decode(str));

class PickupPayoutSummaryResponse {
  PickupPayoutSummaryResponse({
    this.result,
    this.pickupPoint,
    this.summary,
  });

  bool? result;
  PickupPayoutProfile? pickupPoint;
  PickupPayoutSummary? summary;

  factory PickupPayoutSummaryResponse.fromJson(Map<String, dynamic> json) =>
      PickupPayoutSummaryResponse(
        result: json["result"],
        pickupPoint: json["pickup_point"] == null
            ? null
            : PickupPayoutProfile.fromJson(json["pickup_point"]),
        summary: json["summary"] == null
            ? null
            : PickupPayoutSummary.fromJson(json["summary"]),
      );
}

class PickupPayoutProfile {
  PickupPayoutProfile({
    this.id,
    this.name,
    this.payoutMethod,
    this.payoutAccountName,
    this.payoutAccountNumber,
    this.payoutBankName,
    this.payoutBranchName,
    this.payoutRoutingNumber,
    this.payoutMobileWalletType,
    this.payoutMobileWalletNumber,
    this.payoutNotes,
    this.payoutFrequencyDays,
  });

  int? id;
  String? name;
  String? payoutMethod;
  String? payoutAccountName;
  String? payoutAccountNumber;
  String? payoutBankName;
  String? payoutBranchName;
  String? payoutRoutingNumber;
  String? payoutMobileWalletType;
  String? payoutMobileWalletNumber;
  String? payoutNotes;
  int? payoutFrequencyDays;

  factory PickupPayoutProfile.fromJson(Map<String, dynamic> json) =>
      PickupPayoutProfile(
        id: json["id"],
        name: json["name"],
        payoutMethod: json["payout_method"],
        payoutAccountName: json["payout_account_name"],
        payoutAccountNumber: json["payout_account_number"],
        payoutBankName: json["payout_bank_name"],
        payoutBranchName: json["payout_branch_name"],
        payoutRoutingNumber: json["payout_routing_number"],
        payoutMobileWalletType: json["payout_mobile_wallet_type"],
        payoutMobileWalletNumber: json["payout_mobile_wallet_number"],
        payoutNotes: json["payout_notes"],
        payoutFrequencyDays: json["payout_frequency_days"],
      );
}

class PickupPayoutSummary {
  PickupPayoutSummary({
    this.totalEarned,
    this.approvedPayoutTotal,
    this.pendingPayoutTotal,
    this.currentBalance,
    this.requestableBalance,
    this.currentBalanceValue,
    this.requestableBalanceValue,
    this.payoutFrequencyDays,
    this.nextEligibleAt,
    this.canRequest,
    this.eligibilityMessage,
  });

  String? totalEarned;
  String? approvedPayoutTotal;
  String? pendingPayoutTotal;
  String? currentBalance;
  String? requestableBalance;
  num? currentBalanceValue;
  num? requestableBalanceValue;
  int? payoutFrequencyDays;
  String? nextEligibleAt;
  bool? canRequest;
  String? eligibilityMessage;

  factory PickupPayoutSummary.fromJson(Map<String, dynamic> json) =>
      PickupPayoutSummary(
        totalEarned: json["total_earned"],
        approvedPayoutTotal: json["approved_payout_total"],
        pendingPayoutTotal: json["pending_payout_total"],
        currentBalance: json["current_balance"],
        requestableBalance: json["requestable_balance"],
        currentBalanceValue: json["current_balance_value"],
        requestableBalanceValue: json["requestable_balance_value"],
        payoutFrequencyDays: json["payout_frequency_days"],
        nextEligibleAt: json["next_eligible_at"],
        canRequest: json["can_request"],
        eligibilityMessage: json["eligibility_message"],
      );
}
