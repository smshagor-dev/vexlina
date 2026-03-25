import 'dart:convert';

LotteryOverviewResponse lotteryOverviewResponseFromJson(String str) =>
    LotteryOverviewResponse.fromJson(json.decode(str));

LotterySummaryResponse lotterySummaryResponseFromJson(String str) =>
    LotterySummaryResponse.fromJson(json.decode(str));

LotteryTicketListResponse lotteryTicketListResponseFromJson(String str) =>
    LotteryTicketListResponse.fromJson(json.decode(str));

LotteryTicketDetailsResponse lotteryTicketDetailsResponseFromJson(String str) =>
    LotteryTicketDetailsResponse.fromJson(json.decode(str));

LotteryWinsResponse lotteryWinsResponseFromJson(String str) =>
    LotteryWinsResponse.fromJson(json.decode(str));

LotteryClaimResponse lotteryClaimResponseFromJson(String str) =>
    LotteryClaimResponse.fromJson(json.decode(str));

class LotteryOverviewResponse {
  final bool success;
  final String message;
  final LotteryOverviewData data;

  LotteryOverviewResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory LotteryOverviewResponse.fromJson(Map<String, dynamic> json) {
    return LotteryOverviewResponse(
      success: json['success'] == true,
      message: json['message']?.toString() ?? '',
      data: LotteryOverviewData.fromJson(
        (json['data'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
    );
  }
}

class LotteryOverviewData {
  final LotteryItem? current;
  final List<LotteryItem> upcoming;
  final int drawnCount;

  LotteryOverviewData({
    required this.current,
    required this.upcoming,
    required this.drawnCount,
  });

  factory LotteryOverviewData.fromJson(Map<String, dynamic> json) {
    return LotteryOverviewData(
      current: json['current'] is Map<String, dynamic>
          ? LotteryItem.fromJson(json['current'] as Map<String, dynamic>)
          : null,
      upcoming: (json['upcoming'] as List? ?? [])
          .map((e) => LotteryItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      drawnCount: _toInt(json['drawn_count']),
    );
  }
}

class LotteryItem {
  final int id;
  final String title;
  final String description;
  final String? photoUrl;
  final dynamic price;
  final int prizeNumber;
  final int winnerNumber;
  final String? startDate;
  final String? drewDate;
  final bool isDrew;
  final bool isActive;
  final String type;
  final List<LotteryPrizeItem> prizes;

  LotteryItem({
    required this.id,
    required this.title,
    required this.description,
    required this.photoUrl,
    required this.price,
    required this.prizeNumber,
    required this.winnerNumber,
    required this.startDate,
    required this.drewDate,
    required this.isDrew,
    required this.isActive,
    required this.type,
    required this.prizes,
  });

  factory LotteryItem.fromJson(Map<String, dynamic> json) {
    return LotteryItem(
      id: _toInt(json['id']),
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      photoUrl: json['photo_url']?.toString(),
      price: json['price'],
      prizeNumber: _toInt(json['prize_number']),
      winnerNumber: _toInt(json['winner_number']),
      startDate: json['start_date']?.toString(),
      drewDate: json['drew_date']?.toString(),
      isDrew: json['is_drew'] == true,
      isActive: json['is_active'] == true,
      type: json['type']?.toString() ?? '',
      prizes: (json['prizes'] as List? ?? [])
          .map((e) => LotteryPrizeItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class LotteryPrizeItem {
  final int id;
  final String name;
  final String description;
  final dynamic prizeValue;
  final int winnerNumber;
  final String? photoUrl;

  LotteryPrizeItem({
    required this.id,
    required this.name,
    required this.description,
    required this.prizeValue,
    required this.winnerNumber,
    required this.photoUrl,
  });

  factory LotteryPrizeItem.fromJson(Map<String, dynamic> json) {
    return LotteryPrizeItem(
      id: _toInt(json['id']),
      name: json['name']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      prizeValue: json['prize_value'],
      winnerNumber: _toInt(json['winner_number']),
      photoUrl: json['photo_url']?.toString(),
    );
  }
}

class LotterySummaryResponse {
  final bool success;
  final String message;
  final LotterySummaryData data;

  LotterySummaryResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory LotterySummaryResponse.fromJson(Map<String, dynamic> json) {
    return LotterySummaryResponse(
      success: json['success'] == true,
      message: json['message']?.toString() ?? '',
      data: LotterySummaryData.fromJson(
        (json['data'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
    );
  }
}

class LotterySummaryData {
  final int totalTickets;
  final int activeTickets;
  final int totalWins;
  final int pendingClaims;
  final List<LotteryTicketSummary> recentTickets;

  LotterySummaryData({
    required this.totalTickets,
    required this.activeTickets,
    required this.totalWins,
    required this.pendingClaims,
    required this.recentTickets,
  });

  factory LotterySummaryData.fromJson(Map<String, dynamic> json) {
    return LotterySummaryData(
      totalTickets: _toInt(json['total_tickets']),
      activeTickets: _toInt(json['active_tickets']),
      totalWins: _toInt(json['total_wins']),
      pendingClaims: _toInt(json['pending_claims']),
      recentTickets: (json['recent_tickets'] as List? ?? [])
          .map((e) => LotteryTicketSummary.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class LotteryTicketSummary {
  final String ticketNumber;
  final String title;
  final String? drewDate;
  final String status;

  LotteryTicketSummary({
    required this.ticketNumber,
    required this.title,
    required this.drewDate,
    required this.status,
  });

  factory LotteryTicketSummary.fromJson(Map<String, dynamic> json) {
    return LotteryTicketSummary(
      ticketNumber: json['ticket_number']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      drewDate: json['drew_date']?.toString(),
      status: json['status']?.toString() ?? '',
    );
  }
}

class LotteryTicketListResponse {
  final bool success;
  final String message;
  final LotteryTicketListData data;

  LotteryTicketListResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory LotteryTicketListResponse.fromJson(Map<String, dynamic> json) {
    return LotteryTicketListResponse(
      success: json['success'] == true,
      message: json['message']?.toString() ?? '',
      data: LotteryTicketListData.fromJson(
        (json['data'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
    );
  }
}

class LotteryTicketListData {
  final String filter;
  final int total;
  final List<LotteryTicketItem> tickets;

  LotteryTicketListData({
    required this.filter,
    required this.total,
    required this.tickets,
  });

  factory LotteryTicketListData.fromJson(Map<String, dynamic> json) {
    return LotteryTicketListData(
      filter: json['filter']?.toString() ?? 'all',
      total: _toInt(json['total']),
      tickets: (json['tickets'] as List? ?? [])
          .map((e) => LotteryTicketItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class LotteryTicketItem {
  final String ticketNumber;
  final String? ticketBuyDate;
  final int lotteryId;
  final String title;
  final String description;
  final dynamic price;
  final String? drewDate;
  final bool isDrew;
  final String name;
  final String email;
  final String phone;
  final String fullAddress;
  final String winStatus;

  LotteryTicketItem({
    required this.ticketNumber,
    required this.ticketBuyDate,
    required this.lotteryId,
    required this.title,
    required this.description,
    required this.price,
    required this.drewDate,
    required this.isDrew,
    required this.name,
    required this.email,
    required this.phone,
    required this.fullAddress,
    required this.winStatus,
  });

  factory LotteryTicketItem.fromJson(Map<String, dynamic> json) {
    return LotteryTicketItem(
      ticketNumber: json['ticket_number']?.toString() ?? '',
      ticketBuyDate: json['ticket_buy_date']?.toString(),
      lotteryId: _toInt(json['lottery_id']),
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      price: json['price'],
      drewDate: json['drew_date']?.toString(),
      isDrew: json['is_drew'] == true,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      fullAddress: json['full_address']?.toString() ?? '',
      winStatus: json['win_status']?.toString() ?? 'lose',
    );
  }
}

class LotteryTicketDetailsResponse {
  final bool success;
  final String message;
  final LotteryTicketDetails data;

  LotteryTicketDetailsResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory LotteryTicketDetailsResponse.fromJson(Map<String, dynamic> json) {
    return LotteryTicketDetailsResponse(
      success: json['success'] == true,
      message: json['message']?.toString() ?? '',
      data: LotteryTicketDetails.fromJson(
        (json['data'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
    );
  }
}

class LotteryTicketDetails extends LotteryTicketItem {
  final LotteryWinnerClaim? winner;

  LotteryTicketDetails({
    required super.ticketNumber,
    required super.ticketBuyDate,
    required super.lotteryId,
    required super.title,
    required super.description,
    required super.price,
    required super.drewDate,
    required super.isDrew,
    required super.name,
    required super.email,
    required super.phone,
    required super.fullAddress,
    required super.winStatus,
    required this.winner,
  });

  factory LotteryTicketDetails.fromJson(Map<String, dynamic> json) {
    return LotteryTicketDetails(
      ticketNumber: json['ticket_number']?.toString() ?? '',
      ticketBuyDate: json['ticket_buy_date']?.toString(),
      lotteryId: _toInt(json['lottery_id']),
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      price: json['price'],
      drewDate: json['drew_date']?.toString(),
      isDrew: json['is_drew'] == true,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      fullAddress: json['full_address']?.toString() ?? '',
      winStatus: json['win_status']?.toString() ?? 'lose',
      winner: json['winner'] is Map<String, dynamic>
          ? LotteryWinnerClaim.fromJson(json['winner'] as Map<String, dynamic>)
          : null,
    );
  }
}

class LotteryWinsResponse {
  final bool success;
  final String message;
  final LotteryWinsData data;

  LotteryWinsResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory LotteryWinsResponse.fromJson(Map<String, dynamic> json) {
    return LotteryWinsResponse(
      success: json['success'] == true,
      message: json['message']?.toString() ?? '',
      data: LotteryWinsData.fromJson(
        (json['data'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
    );
  }
}

class LotteryWinsData {
  final String filter;
  final int totalWins;
  final List<LotteryWinItem> wins;

  LotteryWinsData({
    required this.filter,
    required this.totalWins,
    required this.wins,
  });

  factory LotteryWinsData.fromJson(Map<String, dynamic> json) {
    return LotteryWinsData(
      filter: json['filter']?.toString() ?? 'all',
      totalWins: _toInt(json['total_wins']),
      wins: (json['wins'] as List? ?? [])
          .map((e) => LotteryWinItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class LotteryWinItem {
  final int winnerId;
  final String ticketNumber;
  final String status;
  final LotteryWinnerClaim claim;
  final LotteryWinLottery lottary;
  final LotteryWinPrize prize;

  LotteryWinItem({
    required this.winnerId,
    required this.ticketNumber,
    required this.status,
    required this.claim,
    required this.lottary,
    required this.prize,
  });

  factory LotteryWinItem.fromJson(Map<String, dynamic> json) {
    return LotteryWinItem(
      winnerId: _toInt(json['winner_id']),
      ticketNumber: json['ticket_number']?.toString() ?? '',
      status: json['status']?.toString() ?? 'old',
      claim: LotteryWinnerClaim.fromJson(
        (json['claim'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
      lottary: LotteryWinLottery.fromJson(
        (json['lottary'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
      prize: LotteryWinPrize.fromJson(
        (json['prize'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
    );
  }
}

class LotteryWinnerClaim {
  final int winnerId;
  final int claimRequest;
  final String claimCode;
  final int sendGift;
  final String mobile;
  final String address;

  LotteryWinnerClaim({
    this.winnerId = 0,
    required this.claimRequest,
    required this.claimCode,
    required this.sendGift,
    required this.mobile,
    required this.address,
  });

  factory LotteryWinnerClaim.fromJson(Map<String, dynamic> json) {
    return LotteryWinnerClaim(
      winnerId: _toInt(json['winner_id']),
      claimRequest: _toInt(json['claim_request']),
      claimCode: json['claim_code']?.toString() ?? '',
      sendGift: _toInt(json['send_gift']),
      mobile: json['mobile']?.toString() ?? '',
      address: json['address']?.toString() ?? '',
    );
  }
}

class LotteryWinLottery {
  final int id;
  final String title;
  final String? drewDate;

  LotteryWinLottery({
    required this.id,
    required this.title,
    required this.drewDate,
  });

  factory LotteryWinLottery.fromJson(Map<String, dynamic> json) {
    return LotteryWinLottery(
      id: _toInt(json['id']),
      title: json['title']?.toString() ?? '',
      drewDate: json['drew_date']?.toString(),
    );
  }
}

class LotteryWinPrize {
  final String name;
  final String description;
  final dynamic prizeValue;
  final int winnerNumber;
  final String? photoUrl;

  LotteryWinPrize({
    required this.name,
    required this.description,
    required this.prizeValue,
    required this.winnerNumber,
    required this.photoUrl,
  });

  factory LotteryWinPrize.fromJson(Map<String, dynamic> json) {
    return LotteryWinPrize(
      name: json['name']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      prizeValue: json['prize_value'],
      winnerNumber: _toInt(json['winner_number']),
      photoUrl: json['photo_url']?.toString(),
    );
  }
}

class LotteryClaimResponse {
  final bool success;
  final String message;
  final LotteryClaimData data;

  LotteryClaimResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory LotteryClaimResponse.fromJson(Map<String, dynamic> json) {
    return LotteryClaimResponse(
      success: json['success'] == true,
      message: json['message']?.toString() ?? '',
      data: LotteryClaimData.fromJson(
        (json['data'] as Map<String, dynamic>?) ?? <String, dynamic>{},
      ),
    );
  }
}

class LotteryClaimData {
  final int winnerId;
  final String claimCode;

  LotteryClaimData({
    required this.winnerId,
    required this.claimCode,
  });

  factory LotteryClaimData.fromJson(Map<String, dynamic> json) {
    return LotteryClaimData(
      winnerId: _toInt(json['winner_id']),
      claimCode: json['claim_code']?.toString() ?? '',
    );
  }
}

int _toInt(dynamic value) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}
