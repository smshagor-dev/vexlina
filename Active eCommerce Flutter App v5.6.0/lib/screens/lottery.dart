import 'dart:async';

import 'package:active_ecommerce_cms_demo_app/data_model/lottery_response.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/lottery_repository.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class LotteryScreen extends StatefulWidget {
  const LotteryScreen({super.key});

  @override
  State<LotteryScreen> createState() => _LotteryScreenState();
}

class _LotteryScreenState extends State<LotteryScreen> {
  final LotteryRepository _repository = LotteryRepository();

  LotteryOverviewData? _overview;
  LotterySummaryData? _summary;
  bool _isLoading = true;
  String? _error;
  DateTime _now = DateTime.now();
  Timer? _ticker;

  @override
  void initState() {
    super.initState();
    _startTicker();
    _fetchData();
  }

  @override
  void dispose() {
    _ticker?.cancel();
    super.dispose();
  }

  void _startTicker() {
    _ticker?.cancel();
    _ticker = Timer.periodic(const Duration(seconds: 1), (_) {
      if (!mounted) return;
      setState(() {
        _now = DateTime.now();
      });
    });
  }

  Future<void> _fetchData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final overview = await _repository.getOverview();
      LotterySummaryData? summary;

      if (is_logged_in.$) {
        try {
          final summaryResponse = await _repository.getMySummary();
          summary = summaryResponse.data;
        } catch (_) {}
      }

      if (!mounted) return;
      setState(() {
        _overview = overview.data;
        _summary = summary;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  String _formatDate(String? date) {
    if (date == null || date.isEmpty) return 'N/A';
    try {
      final parsed = DateTime.parse(date).toLocal();
      const months = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
      ];
      return '${parsed.day.toString().padLeft(2, '0')} ${months[parsed.month - 1]} ${parsed.year}';
    } catch (_) {
      return date;
    }
  }

  String _formatDateTime(String? date) {
    if (date == null || date.isEmpty) return 'N/A';
    try {
      final parsed = DateTime.parse(date).toLocal();
      const months = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
      ];
      final hour = parsed.hour % 12 == 0 ? 12 : parsed.hour % 12;
      final minute = parsed.minute.toString().padLeft(2, '0');
      final suffix = parsed.hour >= 12 ? 'PM' : 'AM';
      return '${parsed.day.toString().padLeft(2, '0')} ${months[parsed.month - 1]} ${parsed.year}, $hour:$minute $suffix';
    } catch (_) {
      return date;
    }
  }

  String _formatPrice(dynamic price) {
    if (price == null || price.toString().isEmpty) return 'N/A';
    return 'Tk ${price.toString()}';
  }

  DateTime? _parseDate(String? value) {
    if (value == null || value.isEmpty) return null;
    try {
      return DateTime.parse(value).toLocal();
    } catch (_) {
      return null;
    }
  }

  String _formatCountdown(DateTime? target) {
    if (target == null) return 'N/A';
    final diff = target.difference(_now);
    if (diff.isNegative) return 'Live now';
    final days = diff.inDays;
    final hours = diff.inHours.remainder(24);
    final minutes = diff.inMinutes.remainder(60);
    final seconds = diff.inSeconds.remainder(60);
    if (days > 0) return '${days}d ${hours}h ${minutes}m';
    if (hours > 0) return '${hours}h ${minutes}m ${seconds}s';
    return '${minutes}m ${seconds}s';
  }

  void _showLoginNotice() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Please login first to view your lottery tickets.'),
      ),
    );
  }

  void _openTickets(String filter) {
    if (!is_logged_in.$) {
      _showLoginNotice();
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => LotteryTicketsScreen(initialFilter: filter),
      ),
    );
  }

  void _openWins(String filter) {
    if (!is_logged_in.$) {
      _showLoginNotice();
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => LotteryWinsScreen(initialFilter: filter),
      ),
    );
  }

  void _openTicketDetails(String ticketNumber) {
    if (!is_logged_in.$) {
      _showLoginNotice();
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => LotteryTicketDetailsScreen(ticketNumber: ticketNumber),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF7F8FA),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: const Text('Lottery'),
      ),
      body: RefreshIndicator(
        color: MyTheme.accent_color,
        onRefresh: _fetchData,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _LotteryErrorState(onRefresh: _fetchData)
                : ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: EdgeInsets.all(16.w),
                    children: [
                      _LotteryHero(
                        upcomingCount: _overview?.upcoming.length ?? 0,
                        drawnCount: _overview?.drawnCount ?? 0,
                        isLoggedIn: is_logged_in.$,
                      ),
                      SizedBox(height: 16.h),
                      if (_summary != null) ...[
                        _SummaryGrid(
                          summary: _summary!,
                          onItemTap: (itemKey) {
                            switch (itemKey) {
                              case 'total_tickets':
                                _openTickets('all');
                                break;
                              case 'active_tickets':
                                _openTickets('active');
                                break;
                              case 'total_wins':
                                _openWins('all');
                                break;
                              case 'pending_claims':
                                _openWins('unclaimed');
                                break;
                            }
                          },
                        ),
                        SizedBox(height: 16.h),
                      ],
                      _TicketCtaSection(
                        onMyTicketsTap: () => _openTickets('all'),
                        onDrawnHistoryTap: () => _openWins('claimed'),
                      ),
                      SizedBox(height: 18.h),
                      if ((_summary?.recentTickets.isNotEmpty ?? false)) ...[
                        _SectionTitle(
                          title: 'Recent Tickets',
                          subtitle:
                              'Your latest lottery activity from the same account.',
                        ),
                        SizedBox(height: 10.h),
                        ..._summary!.recentTickets.map(
                          (ticket) => _TicketTile(
                            ticket: ticket,
                            formatDate: _formatDate,
                            onTap: () => _openTicketDetails(ticket.ticketNumber),
                          ),
                        ),
                        SizedBox(height: 18.h),
                      ],
                      if (_overview?.current != null) ...[
                        _SectionTitle(
                          title: 'Current Lottery',
                          subtitle: 'Match the active draw shown on the website.',
                        ),
                        SizedBox(height: 10.h),
                        _LotteryCard(
                          lottery: _overview!.current!,
                          formatDate: _formatDate,
                          formatDateTime: _formatDateTime,
                          formatPrice: _formatPrice,
                          countdownValue: _formatCountdown(
                            _parseDate(_overview!.current!.drewDate),
                          ),
                          countdownLabel: 'Draw countdown',
                          accentColors: const [
                            Color(0xffF97316),
                            Color(0xffFB923C),
                          ],
                        ),
                        SizedBox(height: 18.h),
                      ] else ...[
                        _SoftInfoCard(
                          icon: Icons.hourglass_top_rounded,
                          title: 'No active lottery right now',
                          subtitle:
                              'The app will automatically show the next available draw once it goes live.',
                        ),
                        SizedBox(height: 18.h),
                      ],
                      _SectionTitle(
                        title: 'Upcoming Lotteries',
                        subtitle:
                            '${_overview?.upcoming.length ?? 0} upcoming, ${_overview?.drawnCount ?? 0} previously drawn',
                      ),
                      SizedBox(height: 10.h),
                      if ((_overview?.upcoming.isEmpty ?? true))
                        _SoftInfoCard(
                          icon: Icons.calendar_month_outlined,
                          title: 'No upcoming lottery right now',
                          subtitle:
                              'As soon as a new lottery is scheduled, it will appear here with dates and prize details.',
                        )
                      else
                        ..._overview!.upcoming.map(
                          (lottery) => Padding(
                            padding: EdgeInsets.only(bottom: 12.h),
                            child: _LotteryCard(
                              lottery: lottery,
                              formatDate: _formatDate,
                              formatDateTime: _formatDateTime,
                              formatPrice: _formatPrice,
                              countdownValue: _formatCountdown(
                                _parseDate(lottery.startDate),
                              ),
                              countdownLabel:
                                  _parseDate(lottery.startDate)?.isBefore(_now) ==
                                          true
                                      ? 'Running now'
                                      : 'Starts in',
                              accentColors: const [
                                Color(0xff6366F1),
                                Color(0xff8B5CF6),
                              ],
                            ),
                          ),
                        ),
                      SizedBox(height: 6.h),
                      const _HowItWorksSection(),
                      SizedBox(height: 16.h),
                      const _ImportantNotesSection(),
                      SizedBox(height: 18.h),
                    ],
                  ),
      ),
    );
  }
}

class _LotteryHero extends StatelessWidget {
  final int upcomingCount;
  final int drawnCount;
  final bool isLoggedIn;

  const _LotteryHero({
    required this.upcomingCount,
    required this.drawnCount,
    required this.isLoggedIn,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(18.w),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xffFFF7ED), Color(0xffFFEDD5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22.r),
        boxShadow: [
          BoxShadow(
            color: const Color(0xffF97316).withValues(alpha: 0.10),
            blurRadius: 22,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 48.w,
                height: 48.w,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.75),
                  borderRadius: BorderRadius.circular(16.r),
                ),
                alignment: Alignment.center,
                child: Icon(
                  Icons.local_activity_rounded,
                  color: MyTheme.accent_color,
                  size: 24.sp,
                ),
              ),
              SizedBox(width: 12.w),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Our Lottery',
                      style: TextStyle(
                        fontSize: 22.sp,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xff111827),
                      ),
                    ),
                    SizedBox(height: 4.h),
                    Text(
                      'Win amazing prizes while you shop.',
                      style: TextStyle(
                        fontSize: 12.5.sp,
                        color: const Color(0xff6B7280),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          SizedBox(height: 16.h),
          Row(
            children: [
              Expanded(
                child: _HeroStatChip(
                  icon: Icons.calendar_today_outlined,
                  label: 'Upcoming',
                  value: '$upcomingCount',
                ),
              ),
              SizedBox(width: 10.w),
              Expanded(
                child: _HeroStatChip(
                  icon: Icons.emoji_events_outlined,
                  label: 'Drawn',
                  value: '$drawnCount',
                ),
              ),
            ],
          ),
          SizedBox(height: 14.h),
          Container(
            padding: EdgeInsets.all(14.w),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.70),
              borderRadius: BorderRadius.circular(16.r),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.info_outline_rounded,
                  color: const Color(0xffC2410C),
                  size: 18.sp,
                ),
                SizedBox(width: 10.w),
                Expanded(
                  child: Text(
                    isLoggedIn
                        ? 'Your ticket summary is synced below with your account data.'
                        : 'Login to see your recent tickets, wins, and pending claims.',
                    style: TextStyle(
                      fontSize: 11.5.sp,
                      height: 1.4,
                      color: const Color(0xff7C2D12),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroStatChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _HeroStatChip({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: 12.w, vertical: 12.h),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.78),
        borderRadius: BorderRadius.circular(16.r),
      ),
      child: Row(
        children: [
          Icon(icon, color: MyTheme.accent_color, size: 18.sp),
          SizedBox(width: 8.w),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 16.sp,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xff111827),
                  ),
                ),
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 11.sp,
                    color: const Color(0xff6B7280),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  final String title;
  final String subtitle;

  const _SectionTitle({required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: TextStyle(
            fontSize: 18.sp,
            fontWeight: FontWeight.w700,
            color: const Color(0xff101828),
          ),
        ),
        SizedBox(height: 4.h),
        Text(
          subtitle,
          style: TextStyle(
            fontSize: 12.sp,
            color: const Color(0xff667085),
          ),
        ),
      ],
    );
  }
}

class _SummaryGrid extends StatelessWidget {
  final LotterySummaryData summary;
  final void Function(String itemKey) onItemTap;

  const _SummaryGrid({
    required this.summary,
    required this.onItemTap,
  });

  @override
  Widget build(BuildContext context) {
    final items = [
      (
        'total_tickets',
        'Total Tickets',
        summary.totalTickets.toString(),
        Icons.sell_outlined,
      ),
      (
        'active_tickets',
        'Active',
        summary.activeTickets.toString(),
        Icons.bolt_rounded,
      ),
      (
        'total_wins',
        'Wins',
        summary.totalWins.toString(),
        Icons.emoji_events_outlined,
      ),
      (
        'pending_claims',
        'Pending Claim',
        summary.pendingClaims.toString(),
        Icons.inventory_2_outlined,
      ),
    ];

    return GridView.builder(
      itemCount: items.length,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 10.h,
        crossAxisSpacing: 10.w,
        childAspectRatio: 1.25,
      ),
      itemBuilder: (context, index) {
        final item = items[index];
        return Material(
          color: Colors.transparent,
          child: InkWell(
            borderRadius: BorderRadius.circular(16.r),
            onTap: () => onItemTap(item.$1),
            child: Container(
              padding: EdgeInsets.all(16.w),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16.r),
                border: Border.all(color: const Color(0xffEAECF0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 34.w,
                    height: 34.w,
                    decoration: BoxDecoration(
                      color: const Color(0xffFFF4ED),
                      borderRadius: BorderRadius.circular(11.r),
                    ),
                    alignment: Alignment.center,
                    child: Icon(
                      item.$4,
                      size: 18.sp,
                      color: MyTheme.accent_color,
                    ),
                  ),
                  SizedBox(height: 12.h),
                  Text(
                    item.$3,
                    style: TextStyle(
                      fontSize: 20.sp,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xff101828),
                    ),
                  ),
                  SizedBox(height: 6.h),
                  Text(
                    item.$2,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 12.sp,
                      height: 1.25,
                      color: const Color(0xff475467),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}

class _LotteryCard extends StatelessWidget {
  final LotteryItem lottery;
  final String Function(String?) formatDate;
  final String Function(String?) formatDateTime;
  final String Function(dynamic) formatPrice;
  final String countdownValue;
  final String countdownLabel;
  final List<Color> accentColors;

  const _LotteryCard({
    required this.lottery,
    required this.formatDate,
    required this.formatDateTime,
    required this.formatPrice,
    required this.countdownValue,
    required this.countdownLabel,
    required this.accentColors,
  });

  @override
  Widget build(BuildContext context) {
    final isCurrent = lottery.isActive;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20.r),
        border: Border.all(color: const Color(0xffEAECF0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.vertical(top: Radius.circular(20.r)),
                child: AspectRatio(
                  aspectRatio: 16 / 9,
                  child: Image.network(
                    lottery.photoUrl ?? '',
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      color: const Color(0xffF2F4F7),
                      alignment: Alignment.center,
                      child: Icon(
                        Icons.local_activity_outlined,
                        size: 38.sp,
                        color: const Color(0xff98A2B3),
                      ),
                    ),
                  ),
                ),
              ),
              Positioned(
                top: 12.h,
                left: 12.w,
                child: Container(
                  padding: EdgeInsets.symmetric(
                    horizontal: 12.w,
                    vertical: 7.h,
                  ),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(colors: accentColors),
                    borderRadius: BorderRadius.circular(999.r),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isCurrent
                            ? Icons.bolt_rounded
                            : Icons.calendar_month_rounded,
                        size: 14.sp,
                        color: Colors.white,
                      ),
                      SizedBox(width: 6.w),
                      Text(
                        isCurrent ? 'CURRENT LOTTERY' : 'UPCOMING LOTTERY',
                        style: TextStyle(
                          fontSize: 10.5.sp,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          Padding(
            padding: EdgeInsets.all(14.w),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: EdgeInsets.symmetric(
                              horizontal: 10.w,
                              vertical: 5.h,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(0xffF8FAFC),
                              borderRadius: BorderRadius.circular(999.r),
                            ),
                            child: Text(
                              'Lottery #${lottery.id}',
                              style: TextStyle(
                                fontSize: 10.5.sp,
                                fontWeight: FontWeight.w600,
                                color: const Color(0xff475467),
                              ),
                            ),
                          ),
                          SizedBox(height: 8.h),
                          Text(
                            lottery.title,
                            style: TextStyle(
                              fontSize: 17.sp,
                              fontWeight: FontWeight.w700,
                              color: const Color(0xff101828),
                            ),
                          ),
                          SizedBox(height: 6.h),
                          Text(
                            lottery.description.isEmpty
                                ? 'Amazing prizes are waiting in this draw.'
                                : lottery.description,
                            maxLines: 3,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 12.sp,
                              height: 1.45,
                              color: const Color(0xff667085),
                            ),
                          ),
                        ],
                      ),
                    ),
                    SizedBox(width: 12.w),
                    Container(
                      padding: EdgeInsets.symmetric(
                        horizontal: 12.w,
                        vertical: 10.h,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xffFFF7ED),
                        borderRadius: BorderRadius.circular(14.r),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            'Ticket Price',
                            style: TextStyle(
                              fontSize: 10.5.sp,
                              color: const Color(0xff9A3412),
                            ),
                          ),
                          SizedBox(height: 3.h),
                          Text(
                            formatPrice(lottery.price),
                            style: TextStyle(
                              fontSize: 15.sp,
                              fontWeight: FontWeight.w700,
                              color: MyTheme.accent_color,
                            ),
                          ),
                          SizedBox(height: 2.h),
                          Text(
                            'Per paid order',
                            style: TextStyle(
                              fontSize: 10.sp,
                              color: const Color(0xffC2410C),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 14.h),
                _DateHighlightCard(
                  icon: Icons.schedule_rounded,
                  label: isCurrent ? 'DRAW DATE & TIME' : 'START DATE & TIME',
                  date: formatDateTime(
                    isCurrent ? lottery.drewDate : lottery.startDate,
                  ),
                  countdownLabel: countdownLabel,
                  countdownValue: countdownValue,
                  gradient: accentColors,
                ),
                SizedBox(height: 10.h),
                Row(
                  children: [
                    Expanded(
                      child: _MiniInfo(
                        label: 'Draw date',
                        value: formatDate(lottery.drewDate),
                      ),
                    ),
                    SizedBox(width: 10.w),
                    Expanded(
                      child: _MiniInfo(
                        label: 'Prize slots',
                        value: '${lottery.prizeNumber}',
                      ),
                    ),
                    SizedBox(width: 10.w),
                    Expanded(
                      child: _MiniInfo(
                        label: 'Winners',
                        value: '${lottery.winnerNumber}',
                      ),
                    ),
                  ],
                ),
                if (lottery.prizes.isNotEmpty) ...[
                  SizedBox(height: 16.h),
                  Row(
                    children: [
                      Icon(
                        Icons.emoji_events_rounded,
                        color: MyTheme.accent_color,
                        size: 18.sp,
                      ),
                      SizedBox(width: 8.w),
                      Text(
                        'Amazing Prizes To Win',
                        style: TextStyle(
                          fontSize: 14.sp,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xff344054),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: 4.h),
                  Text(
                    'Guaranteed winners in every draw.',
                    style: TextStyle(
                      fontSize: 11.5.sp,
                      color: const Color(0xff667085),
                    ),
                  ),
                  SizedBox(height: 10.h),
                  ...lottery.prizes.map(
                    (prize) => Container(
                      margin: EdgeInsets.only(bottom: 8.h),
                      padding: EdgeInsets.all(10.w),
                      decoration: BoxDecoration(
                        color: const Color(0xffF8FAFC),
                        borderRadius: BorderRadius.circular(14.r),
                      ),
                      child: Row(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(12.r),
                            child: SizedBox(
                              width: 48.w,
                              height: 48.w,
                              child: prize.photoUrl != null &&
                                      prize.photoUrl!.isNotEmpty
                                  ? Image.network(
                                      prize.photoUrl!,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) =>
                                          const _PrizeFallbackIcon(),
                                    )
                                  : const _PrizeFallbackIcon(),
                            ),
                          ),
                          SizedBox(width: 12.w),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  prize.name,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 12.8.sp,
                                    fontWeight: FontWeight.w700,
                                    color: const Color(0xff101828),
                                  ),
                                ),
                                SizedBox(height: 3.h),
                                Text(
                                  prize.description.isEmpty
                                      ? 'Amazing prize waiting for you.'
                                      : prize.description,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 11.sp,
                                    color: const Color(0xff667085),
                                  ),
                                ),
                                SizedBox(height: 6.h),
                                Wrap(
                                  spacing: 6.w,
                                  runSpacing: 6.h,
                                  children: [
                                    _MetaChip(
                                      text: '${prize.prizeValue}',
                                      color: const Color(0xffFFF4ED),
                                      textColor: const Color(0xffC2410C),
                                    ),
                                    _MetaChip(
                                      text:
                                          '${prize.winnerNumber} winner${prize.winnerNumber > 1 ? 's' : ''}',
                                      color: const Color(0xffECFDF3),
                                      textColor: const Color(0xff027A48),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
                SizedBox(height: 8.h),
                Container(
                  width: double.infinity,
                  padding: EdgeInsets.symmetric(horizontal: 12.w, vertical: 12.h),
                  decoration: BoxDecoration(
                    color: const Color(0xffFFF7ED),
                    borderRadius: BorderRadius.circular(14.r),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        Icons.info_outline_rounded,
                        color: const Color(0xffC2410C),
                        size: 17.sp,
                      ),
                      SizedBox(width: 8.w),
                      Expanded(
                        child: Text(
                          'One lottery ticket is issued for each paid order. Tickets are not sold separately.',
                          style: TextStyle(
                            fontSize: 11.sp,
                            color: const Color(0xff9A3412),
                            height: 1.4,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _DateHighlightCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String date;
  final String countdownLabel;
  final String countdownValue;
  final List<Color> gradient;

  const _DateHighlightCard({
    required this.icon,
    required this.label,
    required this.date,
    required this.countdownLabel,
    required this.countdownValue,
    required this.gradient,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(12.w),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            gradient.first.withValues(alpha: 0.10),
            gradient.last.withValues(alpha: 0.10),
          ],
        ),
        borderRadius: BorderRadius.circular(16.r),
      ),
      child: Row(
        children: [
          Container(
            width: 40.w,
            height: 40.w,
            decoration: BoxDecoration(
              gradient: LinearGradient(colors: gradient),
              borderRadius: BorderRadius.circular(12.r),
            ),
            alignment: Alignment.center,
            child: Icon(icon, size: 20.sp, color: Colors.white),
          ),
          SizedBox(width: 12.w),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 10.5.sp,
                    color: const Color(0xff667085),
                  ),
                ),
                SizedBox(height: 3.h),
                Text(
                  date,
                  style: TextStyle(
                    fontSize: 12.4.sp,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xff101828),
                  ),
                ),
              ],
            ),
          ),
          SizedBox(width: 10.w),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                countdownLabel,
                style: TextStyle(
                  fontSize: 10.sp,
                  color: const Color(0xff667085),
                ),
              ),
              SizedBox(height: 3.h),
              Text(
                countdownValue,
                style: TextStyle(
                  fontSize: 12.5.sp,
                  fontWeight: FontWeight.w700,
                  color: gradient.first,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _PrizeFallbackIcon extends StatelessWidget {
  const _PrizeFallbackIcon();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      alignment: Alignment.center,
      child: Icon(
        Icons.card_giftcard_rounded,
        color: MyTheme.accent_color,
        size: 22.sp,
      ),
    );
  }
}

class _MetaChip extends StatelessWidget {
  final String text;
  final Color color;
  final Color textColor;

  const _MetaChip({
    required this.text,
    required this.color,
    required this.textColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: 8.w, vertical: 5.h),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(999.r),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 10.5.sp,
          fontWeight: FontWeight.w600,
          color: textColor,
        ),
      ),
    );
  }
}

class _MiniInfo extends StatelessWidget {
  final String label;
  final String value;

  const _MiniInfo({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(10.w),
      decoration: BoxDecoration(
        color: const Color(0xffF8FAFC),
        borderRadius: BorderRadius.circular(12.r),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 10.5.sp,
              color: const Color(0xff667085),
            ),
          ),
          SizedBox(height: 4.h),
          Text(
            value,
            style: TextStyle(
              fontSize: 11.8.sp,
              fontWeight: FontWeight.w600,
              color: const Color(0xff101828),
            ),
          ),
        ],
      ),
    );
  }
}

class _HowItWorksSection extends StatelessWidget {
  const _HowItWorksSection();

  @override
  Widget build(BuildContext context) {
    final steps = [
      (
        '1',
        'Shop Products',
        'Browse products, add them to your cart, and place an order.',
      ),
      (
        '2',
        'Complete Payment',
        'Online payment gives tickets instantly. COD tickets are issued after delivery confirmation.',
      ),
      (
        '3',
        'Get Lottery Ticket',
        'Each paid order generates one unique lottery ticket automatically.',
      ),
      (
        '4',
        'Wait for Draw',
        'Your ticket joins the current draw and winners are announced after the draw date.',
      ),
    ];

    return Container(
      padding: EdgeInsets.all(16.w),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18.r),
        border: Border.all(color: const Color(0xffEAECF0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 42.w,
                height: 42.w,
                decoration: BoxDecoration(
                  color: const Color(0xffFFF4ED),
                  borderRadius: BorderRadius.circular(14.r),
                ),
                alignment: Alignment.center,
                child: Icon(
                  Icons.card_giftcard_rounded,
                  color: MyTheme.accent_color,
                  size: 22.sp,
                ),
              ),
              SizedBox(width: 12.w),
              Expanded(
                child: Text(
                  'How To Get Lottery Tickets',
                  style: TextStyle(
                    fontSize: 16.sp,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xff101828),
                  ),
                ),
              ),
            ],
          ),
          SizedBox(height: 14.h),
          ...steps.map(
            (step) => Container(
              margin: EdgeInsets.only(bottom: 10.h),
              padding: EdgeInsets.all(12.w),
              decoration: BoxDecoration(
                color: const Color(0xffF8FAFC),
                borderRadius: BorderRadius.circular(14.r),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 28.w,
                    height: 28.w,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          MyTheme.accent_color,
                          const Color(0xffFB923C),
                        ],
                      ),
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      step.$1,
                      style: TextStyle(
                        fontSize: 11.sp,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  SizedBox(width: 10.w),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          step.$2,
                          style: TextStyle(
                            fontSize: 13.sp,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xff101828),
                          ),
                        ),
                        SizedBox(height: 4.h),
                        Text(
                          step.$3,
                          style: TextStyle(
                            fontSize: 11.5.sp,
                            color: const Color(0xff667085),
                            height: 1.45,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ImportantNotesSection extends StatelessWidget {
  const _ImportantNotesSection();

  @override
  Widget build(BuildContext context) {
    final notes = [
      'Each paid order gives you one lottery ticket.',
      'Tickets are issued only for paid orders.',
      'COD tickets are created after delivery confirmation.',
      'Every new order creates a new ticket.',
      'Tickets cannot be purchased directly without shopping.',
    ];

    return Container(
      padding: EdgeInsets.all(16.w),
      decoration: BoxDecoration(
        color: const Color(0xff111827),
        borderRadius: BorderRadius.circular(18.r),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.info_outline_rounded,
                color: const Color(0xffFDBA74),
                size: 20.sp,
              ),
              SizedBox(width: 8.w),
              Text(
                'Important Notes',
                style: TextStyle(
                  fontSize: 15.sp,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          SizedBox(height: 12.h),
          ...notes.map(
            (note) => Padding(
              padding: EdgeInsets.only(bottom: 10.h),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    margin: EdgeInsets.only(top: 3.h),
                    width: 7.w,
                    height: 7.w,
                    decoration: const BoxDecoration(
                      color: Color(0xffFDBA74),
                      shape: BoxShape.circle,
                    ),
                  ),
                  SizedBox(width: 10.w),
                  Expanded(
                    child: Text(
                      note,
                      style: TextStyle(
                        fontSize: 11.8.sp,
                        height: 1.45,
                        color: const Color(0xffE5E7EB),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TicketCtaSection extends StatelessWidget {
  final VoidCallback onMyTicketsTap;
  final VoidCallback onDrawnHistoryTap;

  const _TicketCtaSection({
    required this.onMyTicketsTap,
    required this.onDrawnHistoryTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(18.w),
      decoration: BoxDecoration(
        color: const Color(0xffFFF7F4),
        borderRadius: BorderRadius.circular(18.r),
        border: Border.all(color: const Color(0xffFFD7CC)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Check Your Lottery Tickets',
            style: TextStyle(
              fontSize: 16.sp,
              fontWeight: FontWeight.w700,
              color: const Color(0xff111827),
            ),
          ),
          SizedBox(height: 6.h),
          Text(
            'View your ticket activity, check draw dates, and track whether you have won.',
            style: TextStyle(
              fontSize: 12.sp,
              height: 1.45,
              color: const Color(0xff7A4A3A),
            ),
          ),
          SizedBox(height: 14.h),
          Row(
            children: [
              Expanded(
                child: _CtaButton(
                  title: 'My Tickets',
                  icon: Icons.confirmation_number_outlined,
                  backgroundColor: Color(0xFFFA3E00),
                  onTap: onMyTicketsTap,
                ),
              ),
              SizedBox(width: 10.w),
              Expanded(
                child: _CtaButton(
                  title: 'Drawn History',
                  icon: Icons.emoji_events_outlined,
                  backgroundColor: Color(0xFFFA3E00),
                  onTap: onDrawnHistoryTap,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _CtaButton extends StatelessWidget {
  final String title;
  final IconData icon;
  final Color backgroundColor;
  final VoidCallback onTap;

  const _CtaButton({
    required this.title,
    required this.icon,
    required this.backgroundColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14.r),
        child: Container(
          padding: EdgeInsets.symmetric(horizontal: 12.w, vertical: 12.h),
          decoration: BoxDecoration(
            color: backgroundColor,
            borderRadius: BorderRadius.circular(14.r),
            boxShadow: [
              BoxShadow(
                color: backgroundColor.withValues(alpha: 0.22),
                blurRadius: 18,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 18.sp, color: Colors.white),
              SizedBox(width: 8.w),
              Flexible(
                child: Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 11.8.sp,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TicketTile extends StatelessWidget {
  final LotteryTicketSummary ticket;
  final String Function(String?) formatDate;
  final VoidCallback onTap;

  const _TicketTile({
    required this.ticket,
    required this.formatDate,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14.r),
        child: Container(
          margin: EdgeInsets.only(bottom: 10.h),
          padding: EdgeInsets.all(12.w),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14.r),
            border: Border.all(color: const Color(0xffEAECF0)),
          ),
          child: Row(
            children: [
              Container(
                width: 42.w,
                height: 42.w,
                decoration: BoxDecoration(
                  color: const Color(0xffFFF4ED),
                  borderRadius: BorderRadius.circular(12.r),
                ),
                alignment: Alignment.center,
                child: Icon(
                  Icons.confirmation_number_outlined,
                  color: MyTheme.accent_color,
                  size: 20.sp,
                ),
              ),
              SizedBox(width: 12.w),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      ticket.ticketNumber,
                      style: TextStyle(
                        fontSize: 12.5.sp,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xff101828),
                      ),
                    ),
                    SizedBox(height: 4.h),
                    Text(
                      ticket.title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 11.5.sp,
                        color: const Color(0xff667085),
                      ),
                    ),
                  ],
                ),
              ),
              SizedBox(width: 10.w),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    ticket.status.toUpperCase(),
                    style: TextStyle(
                      fontSize: 10.5.sp,
                      fontWeight: FontWeight.w700,
                      color: ticket.status == 'drawn'
                          ? const Color(0xff175CD3)
                          : const Color(0xff027A48),
                    ),
                  ),
                  SizedBox(height: 4.h),
                  Text(
                    formatDate(ticket.drewDate),
                    style: TextStyle(
                      fontSize: 10.5.sp,
                      color: const Color(0xff667085),
                    ),
                  ),
                  SizedBox(height: 6.h),
                  Text(
                    'View',
                    style: TextStyle(
                      fontSize: 11.sp,
                      fontWeight: FontWeight.w700,
                      color: MyTheme.accent_color,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SoftInfoCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;

  const _SoftInfoCard({
    required this.icon,
    required this.title,
    required this.subtitle,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(18.w),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16.r),
        border: Border.all(color: const Color(0xffEAECF0)),
      ),
      child: Row(
        children: [
          Container(
            width: 48.w,
            height: 48.w,
            decoration: BoxDecoration(
              color: const Color(0xffF2F4F7),
              borderRadius: BorderRadius.circular(14.r),
            ),
            alignment: Alignment.center,
            child: Icon(icon, color: const Color(0xff667085), size: 22.sp),
          ),
          SizedBox(width: 12.w),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 13.sp,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xff101828),
                  ),
                ),
                SizedBox(height: 4.h),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 11.5.sp,
                    color: const Color(0xff667085),
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _LotteryErrorState extends StatelessWidget {
  final Future<void> Function() onRefresh;

  const _LotteryErrorState({required this.onRefresh});

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        SizedBox(height: 120.h),
        Icon(
          Icons.error_outline_rounded,
          size: 46.sp,
          color: const Color(0xff98A2B3),
        ),
        SizedBox(height: 12.h),
        Center(
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: 24.w),
            child: Text(
              'Lottery data load করা যায়নি। আবার refresh করে দেখুন.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13.sp,
                color: const Color(0xff667085),
              ),
            ),
          ),
        ),
        SizedBox(height: 16.h),
        Center(
          child: ElevatedButton(
            onPressed: onRefresh,
            style: ElevatedButton.styleFrom(
              backgroundColor: MyTheme.accent_color,
              foregroundColor: Colors.white,
              padding: EdgeInsets.symmetric(horizontal: 18.w, vertical: 12.h),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14.r),
              ),
            ),
            child: const Text('Try Again'),
          ),
        ),
      ],
    );
  }
}

class LotteryTicketsScreen extends StatefulWidget {
  final String initialFilter;

  const LotteryTicketsScreen({
    super.key,
    required this.initialFilter,
  });

  @override
  State<LotteryTicketsScreen> createState() => _LotteryTicketsScreenState();
}

class _LotteryTicketsScreenState extends State<LotteryTicketsScreen> {
  final LotteryRepository _repository = LotteryRepository();
  late String _filter;
  bool _loading = true;
  List<LotteryTicketItem> _tickets = [];

  @override
  void initState() {
    super.initState();
    _filter = widget.initialFilter;
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final response = await _repository.getTickets(filter: _filter);
      if (!mounted) return;
      setState(() {
        _tickets = response.data.tickets;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _tickets = [];
        _loading = false;
      });
    }
  }

  void _openDetails(String ticketNumber) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => LotteryTicketDetailsScreen(ticketNumber: ticketNumber),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filters = const [
      ('all', 'All Tickets'),
      ('active', 'Active'),
      ('completed', 'Completed'),
    ];

    return Scaffold(
      backgroundColor: const Color(0xffF7F8FA),
      appBar: AppBar(
        backgroundColor: Colors.white,
        title: const Text('My Lottery Tickets'),
      ),
      body: RefreshIndicator(
        onRefresh: _fetch,
        child: ListView(
          padding: EdgeInsets.all(16.w),
          children: [
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: filters.map((item) {
                  final active = _filter == item.$1;
                  return Padding(
                    padding: EdgeInsets.only(right: 8.w),
                    child: ChoiceChip(
                      label: Text(item.$2),
                      selected: active,
                      onSelected: (_) {
                        setState(() => _filter = item.$1);
                        _fetch();
                      },
                      selectedColor: const Color(0xffEEF2FF),
                      labelStyle: TextStyle(
                        color: active
                            ? const Color(0xff4338CA)
                            : const Color(0xff667085),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
            SizedBox(height: 14.h),
            if (_loading)
              const Center(child: CircularProgressIndicator())
            else if (_tickets.isEmpty)
              const _SoftInfoCard(
                icon: Icons.confirmation_number_outlined,
                title: 'No tickets found',
                subtitle: 'There are no tickets in this filter right now.',
              )
            else
              ..._tickets.map(
                (ticket) => Padding(
                  padding: EdgeInsets.only(bottom: 12.h),
                  child: _TicketListCard(
                    ticket: ticket,
                    onTap: () => _openDetails(ticket.ticketNumber),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class LotteryTicketDetailsScreen extends StatefulWidget {
  final String ticketNumber;

  const LotteryTicketDetailsScreen({
    super.key,
    required this.ticketNumber,
  });

  @override
  State<LotteryTicketDetailsScreen> createState() =>
      _LotteryTicketDetailsScreenState();
}

class _LotteryTicketDetailsScreenState extends State<LotteryTicketDetailsScreen> {
  final LotteryRepository _repository = LotteryRepository();
  LotteryTicketDetails? _ticket;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final response = await _repository.getTicketDetails(widget.ticketNumber);
      if (!mounted) return;
      setState(() {
        _ticket = response.data;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  String _formatDate(String? date) {
    if (date == null || date.isEmpty) return 'N/A';
    try {
      final parsed = DateTime.parse(date).toLocal();
      return '${parsed.day}/${parsed.month}/${parsed.year}';
    } catch (_) {
      return date;
    }
  }

  Future<void> _openClaimSheet() async {
    final winnerId = _ticket?.winner?.winnerId ?? 0;
    if (winnerId == 0) return;

    final updated = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _ClaimPrizeSheet(
        winnerId: winnerId,
        repository: _repository,
      ),
    );

    if (updated == true) {
      _fetch();
    }
  }

  @override
  Widget build(BuildContext context) {
    final ticket = _ticket;

    return Scaffold(
      backgroundColor: const Color(0xffF7F8FA),
      appBar: AppBar(
        backgroundColor: Colors.white,
        title: const Text('Ticket Summary'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ticket == null
              ? const Center(child: Text('Ticket not found'))
              : ListView(
                  padding: EdgeInsets.all(16.w),
                  children: [
                    Container(
                      padding: EdgeInsets.all(18.w),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20.r),
                        border: Border.all(color: const Color(0xffE5E7EB)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Wrap(
                            spacing: 10.w,
                            runSpacing: 10.h,
                            children: [
                              _MetaChip(
                                text: '#${ticket.ticketNumber}',
                                color: const Color(0xffEEF2FF),
                                textColor: const Color(0xff4338CA),
                              ),
                              _MetaChip(
                                text: ticket.isDrew ? 'Completed' : 'Pending',
                                color: ticket.isDrew
                                    ? const Color(0xffECFDF3)
                                    : const Color(0xffFEF3C7),
                                textColor: ticket.isDrew
                                    ? const Color(0xff027A48)
                                    : const Color(0xffB45309),
                              ),
                              if (ticket.winStatus == 'win')
                                _MetaChip(
                                  text: 'WIN',
                                  color: const Color(0xffDCFCE7),
                                  textColor: const Color(0xff15803D),
                                ),
                            ],
                          ),
                          SizedBox(height: 14.h),
                          Text(
                            ticket.title,
                            style: TextStyle(
                              fontSize: 18.sp,
                              fontWeight: FontWeight.w700,
                              color: const Color(0xff111827),
                            ),
                          ),
                          SizedBox(height: 8.h),
                          _DetailInfoGrid(
                            items: [
                              ('Ticket Holder', ticket.name),
                              ('Contact Email', ticket.email),
                              ('Draw Date', _formatDate(ticket.drewDate)),
                              ('Ticket Price', 'Free on Purchases'),
                              ('Got Ticket On', _formatDate(ticket.ticketBuyDate)),
                              ('Contact Phone', ticket.phone.isEmpty ? 'N/A' : ticket.phone),
                            ],
                          ),
                          if (ticket.fullAddress.isNotEmpty) ...[
                            SizedBox(height: 12.h),
                            Text(
                              'Address',
                              style: TextStyle(
                                fontSize: 11.sp,
                                color: const Color(0xff667085),
                              ),
                            ),
                            SizedBox(height: 4.h),
                            Text(
                              ticket.fullAddress,
                              style: TextStyle(
                                fontSize: 13.sp,
                                color: const Color(0xff111827),
                              ),
                            ),
                          ],
                          SizedBox(height: 16.h),
                          Container(
                            padding: EdgeInsets.all(16.w),
                            decoration: BoxDecoration(
                              color: const Color(0xffF8FAFC),
                              borderRadius: BorderRadius.circular(16.r),
                            ),
                            child: Column(
                              children: [
                                Icon(
                                  Icons.qr_code_2_rounded,
                                  size: 96.sp,
                                  color: const Color(0xff111827),
                                ),
                                SizedBox(height: 8.h),
                                Text(
                                  ticket.ticketNumber,
                                  style: TextStyle(
                                    fontSize: 12.sp,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                SizedBox(height: 4.h),
                                Text(
                                  'Scan QR to view ticket',
                                  style: TextStyle(
                                    fontSize: 11.sp,
                                    color: const Color(0xff667085),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          SizedBox(height: 16.h),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton(
                              onPressed: ticket.winner != null &&
                                      ticket.winner!.claimRequest == 0
                                  ? _openClaimSheet
                                  : null,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: MyTheme.accent_color,
                                foregroundColor: Colors.white,
                                padding: EdgeInsets.symmetric(vertical: 14.h),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14.r),
                                ),
                              ),
                              child: Text(
                                ticket.winner == null
                                    ? 'No claim available'
                                    : ticket.winner!.claimRequest == 1
                                        ? 'Claim Submitted'
                                        : 'Claim Prize',
                              ),
                            ),
                          ),
                          if ((ticket.winner?.claimCode ?? '').isNotEmpty) ...[
                            SizedBox(height: 10.h),
                            Text(
                              'Claim Code: ${ticket.winner!.claimCode}',
                              style: TextStyle(
                                fontSize: 12.sp,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xff4338CA),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}

class LotteryWinsScreen extends StatefulWidget {
  final String initialFilter;

  const LotteryWinsScreen({
    super.key,
    required this.initialFilter,
  });

  @override
  State<LotteryWinsScreen> createState() => _LotteryWinsScreenState();
}

class _LotteryWinsScreenState extends State<LotteryWinsScreen> {
  final LotteryRepository _repository = LotteryRepository();
  late String _filter;
  bool _loading = true;
  List<LotteryWinItem> _wins = [];

  @override
  void initState() {
    super.initState();
    _filter = widget.initialFilter;
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final response = await _repository.getWins(filter: _filter);
      if (!mounted) return;
      setState(() {
        _wins = response.data.wins;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _wins = [];
        _loading = false;
      });
    }
  }

  Future<void> _openClaimSheet(LotteryWinItem win) async {
    if (win.claim.claimRequest == 1) return;

    final updated = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _ClaimPrizeSheet(
        winnerId: win.winnerId,
        repository: _repository,
      ),
    );

    if (updated == true) {
      _fetch();
    }
  }

  @override
  Widget build(BuildContext context) {
    final filters = const [
      ('all', 'All Wins'),
      ('new', 'New'),
      ('old', 'Old'),
      ('claimed', 'Claimed'),
      ('unclaimed', 'Unclaimed'),
    ];

    return Scaffold(
      backgroundColor: const Color(0xffF7F8FA),
      appBar: AppBar(
        backgroundColor: Colors.white,
        title: const Text('My Lottery Wins'),
      ),
      body: RefreshIndicator(
        onRefresh: _fetch,
        child: ListView(
          padding: EdgeInsets.all(16.w),
          children: [
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: filters.map((item) {
                  final active = _filter == item.$1;
                  return Padding(
                    padding: EdgeInsets.only(right: 8.w),
                    child: ChoiceChip(
                      label: Text(item.$2),
                      selected: active,
                      onSelected: (_) {
                        setState(() => _filter = item.$1);
                        _fetch();
                      },
                    ),
                  );
                }).toList(),
              ),
            ),
            SizedBox(height: 14.h),
            if (_loading)
              const Center(child: CircularProgressIndicator())
            else if (_wins.isEmpty)
              const _SoftInfoCard(
                icon: Icons.emoji_events_outlined,
                title: 'No wins found',
                subtitle: 'There are no wins available in this filter.',
              )
            else
              ..._wins.map(
                (win) => Padding(
                  padding: EdgeInsets.only(bottom: 12.h),
                  child: _WinListCard(
                    win: win,
                    onClaimTap: () => _openClaimSheet(win),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _TicketListCard extends StatelessWidget {
  final LotteryTicketItem ticket;
  final VoidCallback onTap;

  const _TicketListCard({
    required this.ticket,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(18.r),
      child: InkWell(
        borderRadius: BorderRadius.circular(18.r),
        onTap: onTap,
        child: Container(
          padding: EdgeInsets.all(16.w),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18.r),
            border: Border.all(color: const Color(0xffE5E7EB)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Wrap(
                spacing: 8.w,
                runSpacing: 8.h,
                children: [
                  _MetaChip(
                    text: '#${ticket.ticketNumber}',
                    color: const Color(0xffEEF2FF),
                    textColor: const Color(0xff4338CA),
                  ),
                  _MetaChip(
                    text: ticket.isDrew ? 'Completed' : 'Pending',
                    color: ticket.isDrew
                        ? const Color(0xffECFDF3)
                        : const Color(0xffFEF3C7),
                    textColor: ticket.isDrew
                        ? const Color(0xff027A48)
                        : const Color(0xffB45309),
                  ),
                  if (ticket.winStatus == 'win')
                    _MetaChip(
                      text: 'WIN',
                      color: const Color(0xffDCFCE7),
                      textColor: const Color(0xff15803D),
                    ),
                ],
              ),
              SizedBox(height: 12.h),
              Text(
                ticket.title,
                style: TextStyle(
                  fontSize: 16.sp,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xff111827),
                ),
              ),
              SizedBox(height: 6.h),
              Text(
                ticket.email,
                style: TextStyle(
                  fontSize: 12.sp,
                  color: const Color(0xff667085),
                ),
              ),
              SizedBox(height: 10.h),
              Text(
                'Tap to view full ticket summary',
                style: TextStyle(
                  fontSize: 11.5.sp,
                  color: MyTheme.accent_color,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _WinListCard extends StatelessWidget {
  final LotteryWinItem win;
  final VoidCallback onClaimTap;

  const _WinListCard({
    required this.win,
    required this.onClaimTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(16.w),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18.r),
        border: Border.all(color: const Color(0xffE5E7EB)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            spacing: 8.w,
            runSpacing: 8.h,
            children: [
              _MetaChip(
                text: '#${win.ticketNumber}',
                color: const Color(0xffEEF2FF),
                textColor: const Color(0xff4338CA),
              ),
              _MetaChip(
                text: win.claim.claimRequest == 1 ? 'Claimed' : 'Unclaimed',
                color: win.claim.claimRequest == 1
                    ? const Color(0xffECFDF3)
                    : const Color(0xffFEF3C7),
                textColor: win.claim.claimRequest == 1
                    ? const Color(0xff027A48)
                    : const Color(0xffB45309),
              ),
            ],
          ),
          SizedBox(height: 12.h),
          Text(
            win.lottary.title,
            style: TextStyle(
              fontSize: 16.sp,
              fontWeight: FontWeight.w700,
              color: const Color(0xff111827),
            ),
          ),
          SizedBox(height: 6.h),
          Text(
            win.prize.name.isEmpty ? '${win.prize.prizeValue}' : win.prize.name,
            style: TextStyle(
              fontSize: 13.sp,
              color: const Color(0xff667085),
            ),
          ),
          if (win.claim.claimCode.isNotEmpty) ...[
            SizedBox(height: 8.h),
            Text(
              'Claim Code: ${win.claim.claimCode}',
              style: TextStyle(
                fontSize: 12.sp,
                fontWeight: FontWeight.w700,
                color: const Color(0xff4338CA),
              ),
            ),
          ],
          if (win.claim.claimRequest == 0) ...[
            SizedBox(height: 12.h),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: onClaimTap,
                style: ElevatedButton.styleFrom(
                  backgroundColor: MyTheme.accent_color,
                  foregroundColor: Colors.white,
                  padding: EdgeInsets.symmetric(vertical: 12.h),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12.r),
                  ),
                ),
                child: const Text('Claim Prize'),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _DetailInfoGrid extends StatelessWidget {
  final List<(String, String)> items;

  const _DetailInfoGrid({required this.items});

  @override
  Widget build(BuildContext context) {
    return GridView.builder(
      itemCount: items.length,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 10.w,
        mainAxisSpacing: 10.h,
        childAspectRatio: 1.7,
      ),
      itemBuilder: (_, index) {
        final item = items[index];
        return Container(
          padding: EdgeInsets.all(10.w),
          decoration: BoxDecoration(
            color: const Color(0xffF8FAFC),
            borderRadius: BorderRadius.circular(12.r),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                item.$1,
                style: TextStyle(fontSize: 10.5.sp, color: const Color(0xff667085)),
              ),
              SizedBox(height: 4.h),
              Text(
                item.$2,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 12.sp,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xff111827),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _ClaimPrizeSheet extends StatefulWidget {
  final int winnerId;
  final LotteryRepository repository;

  const _ClaimPrizeSheet({
    required this.winnerId,
    required this.repository,
  });

  @override
  State<_ClaimPrizeSheet> createState() => _ClaimPrizeSheetState();
}

class _ClaimPrizeSheetState extends State<_ClaimPrizeSheet> {
  final TextEditingController _mobileController = TextEditingController();
  final TextEditingController _addressController = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _mobileController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_mobileController.text.trim().isEmpty ||
        _addressController.text.trim().isEmpty) {
      return;
    }

    setState(() => _submitting = true);
    try {
      final response = await widget.repository.submitClaim(
        winnerId: widget.winnerId,
        mobile: _mobileController.text.trim(),
        address: _addressController.text.trim(),
      );
      if (!mounted) return;
      Navigator.pop(context, response.success);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(response.message)),
      );
    } catch (_) {
      if (!mounted) return;
      setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.only(bottom: bottomInset),
      child: Container(
        padding: EdgeInsets.all(18.w),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24.r)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Claim Prize',
              style: TextStyle(
                fontSize: 18.sp,
                fontWeight: FontWeight.w700,
              ),
            ),
            SizedBox(height: 14.h),
            TextField(
              controller: _mobileController,
              decoration: const InputDecoration(labelText: 'Mobile Number'),
            ),
            SizedBox(height: 12.h),
            TextField(
              controller: _addressController,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Delivery Address'),
            ),
            SizedBox(height: 16.h),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _submitting ? null : _submit,
                child: Text(_submitting ? 'Submitting...' : 'Submit Claim'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
