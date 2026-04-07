import 'package:active_flutter_delivery_app/custom/lang_text.dart';
import 'package:active_flutter_delivery_app/data_model/dashboard_summary_response.dart';
import 'package:active_flutter_delivery_app/data_model/earning_or_collection_response.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shimmer_helper.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/dashboard_repository.dart';
import 'package:active_flutter_delivery_app/repositories/delivery_repository.dart';
import 'package:active_flutter_delivery_app/screens/order_details.dart';
import 'package:active_flutter_delivery_app/screens/pickup_payout_info.dart';
import 'package:active_flutter_delivery_app/screens/pickup_payouts.dart';
import 'package:active_flutter_delivery_app/ui_sections/drawer.dart';
import 'package:flutter/material.dart';

class Earnings extends StatefulWidget {
  Earnings({Key? key, this.show_back_button = false}) : super(key: key);

  bool show_back_button;

  @override
  _EarningsState createState() => _EarningsState();
}

class _EarningsState extends State<Earnings> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final ScrollController _scrollController = ScrollController();

  final List<Datum> _list = [];
  bool _isInitial = true;
  int _page = 1;
  int _totalData = 0;
  bool _showLoadingContainer = false;
  bool _isLoadingSummary = false;

  String _todayDate = ". . .";
  String _yesterdayDate = ". . .";
  String _todayEarning = ". . .";
  String _yesterdayEarning = ". . .";
  DashboardSummaryResponse? _dashboardSummary;

  @override
  void initState() {
    super.initState();
    fetchAll();

    _scrollController.addListener(() {
      if (_scrollController.position.pixels ==
          _scrollController.position.maxScrollExtent &&
          _list.length < _totalData) {
        setState(() {
          _page++;
          _showLoadingContainer = true;
        });
        fetchList();
      }
    });
  }

  Future<void> fetchAll() async {
    await Future.wait([
      fetchSummary(),
      fetchList(),
    ]);
  }

  Future<void> fetchSummary() async {
    if (_isLoadingSummary) return;
    _isLoadingSummary = true;

    try {
      if (PortalHelper.isPickupPointApp) {
        _dashboardSummary = await DashboardRepository().getDashboardSummaryResponse();
      } else {
        final earningSummaryResponse =
            await DeliveryRepository().getEarningSummaryResponse();

        _todayDate = earningSummaryResponse.today_date.toString();
        _yesterdayDate = earningSummaryResponse.yesterday_date.toString();
        _todayEarning = earningSummaryResponse.today_earning.toString();
        _yesterdayEarning = earningSummaryResponse.yesterday_earning.toString();
      }
    } finally {
      _isLoadingSummary = false;
      if (mounted) {
        setState(() {});
      }
    }
  }

  Future<void> fetchList() async {
    final listResponse = await DeliveryRepository().getEarningResponse(page: _page);
    _list.addAll(listResponse.data ?? []);
    _isInitial = false;
    _totalData = listResponse.meta?.total ?? 0;
    _showLoadingContainer = false;
    if (mounted) {
      setState(() {});
    }
  }

  void reset() {
    _list.clear();
    _isInitial = true;
    _page = 1;
    _totalData = 0;
    _showLoadingContainer = false;
    _todayDate = ". . .";
    _yesterdayDate = ". . .";
    _todayEarning = ". . .";
    _yesterdayEarning = ". . .";
    _dashboardSummary = null;
    setState(() {});
  }

  Future<void> _onRefresh() async {
    reset();
    await fetchAll();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async => widget.show_back_button,
      child: Scaffold(
        backgroundColor: const Color.fromRGBO(248, 249, 251, 1),
        appBar: buildAppBar(context),
        key: _scaffoldKey,
        drawer: MainDrawer(),
        body: RefreshIndicator(
          color: MyTheme.accent_color,
          backgroundColor: Colors.white,
          onRefresh: _onRefresh,
          child: CustomScrollView(
            controller: _scrollController,
            physics:
                const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
            slivers: [
              SliverToBoxAdapter(
                child: Column(
                  children: [
                    PortalHelper.isPickupPointApp
                        ? _buildPickupHeader()
                        : _buildDeliveryHeader(),
                    _buildSectionTitle(),
                    _buildList(),
                    const SizedBox(height: 90),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  PreferredSize buildAppBar(BuildContext context) {
    return PreferredSize(
      preferredSize: const Size.fromHeight(92.0),
      child: AppBar(
        centerTitle: false,
        backgroundColor: Colors.white,
        automaticallyImplyLeading: false,
        elevation: 0.0,
        titleSpacing: 0,
        flexibleSpace: Padding(
          padding: const EdgeInsets.fromLTRB(0.0, 16.0, 0.0, 0.0),
          child: Column(
            children: [
              Padding(
                padding: MediaQuery.of(context).viewPadding.top > 30
                    ? const EdgeInsets.only(top: 36.0)
                    : const EdgeInsets.only(top: 14.0),
                child: Row(
                  children: [
                    widget.show_back_button
                        ? IconButton(
                            icon: Icon(Icons.arrow_back, color: MyTheme.dark_grey),
                            onPressed: () => Navigator.of(context).pop(),
                          )
                        : GestureDetector(
                            onTap: () => _scaffoldKey.currentState!.openDrawer(),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(
                                  vertical: 18.0, horizontal: 12.0),
                              child: Image.asset(
                                'assets/hamburger.png',
                                height: 16,
                                color: MyTheme.dark_grey,
                              ),
                            ),
                          ),
                    Text(
                      PortalHelper.earningsLabel,
                      style: TextStyle(fontSize: 16, color: MyTheme.accent_color),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPickupHeader() {
    final pickupPoint = _dashboardSummary?.pickup_point;
    final earnings = _dashboardSummary?.earning_summary ?? [];
    final managerName = (user_name.$ != null && user_name.$!.trim().isNotEmpty)
        ? user_name.$!.trim()
        : (pickupPoint?.name ?? "Pickup Point Earnings");

    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 8),
      child: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(22),
              gradient: const LinearGradient(
                colors: [
                  Color.fromRGBO(39, 38, 43, 1),
                  Color.fromRGBO(250, 62, 0, 1),
                ],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color.fromRGBO(250, 62, 0, .18),
                  blurRadius: 24,
                  offset: const Offset(0, 12),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "Commission Overview",
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: .82),
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  managerName,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  "All delivery and return commissions are calculated directly from your pickup point setup.",
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: .78),
                    fontSize: 13,
                    height: 1.45,
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: MyTheme.accent_color_2,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 13),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        onPressed: () {
                          Navigator.push(context,
                              MaterialPageRoute(builder: (context) {
                            return const PickupPayouts(showBackButton: true);
                          }));
                        },
                        child: const Text(
                          "Make Payout",
                          style: TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: OutlinedButton(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white,
                          side: BorderSide(
                            color: Colors.white.withValues(alpha: .55),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 13),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        onPressed: () {
                          Navigator.push(context,
                              MaterialPageRoute(builder: (context) {
                            return const PickupPayoutInfo();
                          }));
                        },
                        child: const Text(
                          "Edit Payout Info",
                          style: TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: earnings.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.12,
            ),
            itemBuilder: (context, index) {
              final item = earnings[index];
              return Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: index.isEven
                        ? const Color.fromRGBO(250, 62, 0, .12)
                        : const Color.fromRGBO(17, 90, 255, .08),
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: .04),
                      blurRadius: 16,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.label ?? "",
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: Color.fromRGBO(39, 38, 43, 1),
                      ),
                    ),
                    const SizedBox(height: 14),
                    _buildSummaryLine("Delivery", item.delivery_earning_string ?? "--"),
                    const SizedBox(height: 6),
                    _buildSummaryLine("Return", item.return_earning_string ?? "--"),
                    const SizedBox(height: 10),
                    Container(
                      padding: const EdgeInsets.only(top: 10),
                      decoration: BoxDecoration(
                        border: Border(
                          top: BorderSide(
                            color: Colors.black.withValues(alpha: .06),
                          ),
                        ),
                      ),
                      child: _buildSummaryLine(
                        "Total",
                        item.total_earning_string ?? "--",
                        emphasize: true,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildDeliveryHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 8),
      child: Row(
        children: [
          Expanded(
            child: _buildCompactSummaryCard(
              title: LangText(context).local!.today_ucf,
              value: _todayEarning,
              caption: _todayDate,
              color: MyTheme.blue,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: _buildCompactSummaryCard(
              title: LangText(context).local!.yesterday_ucf,
              value: _yesterdayEarning,
              caption: _yesterdayDate,
              color: MyTheme.grey_153,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCompactSummaryCard({
    required String title,
    required String value,
    required String caption,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: .18),
            blurRadius: 16,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(
              color: Colors.white.withValues(alpha: .84),
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            caption,
            style: TextStyle(
              color: Colors.white.withValues(alpha: .84),
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryLine(String label, String value, {bool emphasize = false}) {
    return Row(
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: emphasize ? 13 : 12,
            color: emphasize ? MyTheme.dark_grey : MyTheme.font_grey,
            fontWeight: emphasize ? FontWeight.w700 : FontWeight.w500,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: TextStyle(
            fontSize: emphasize ? 13 : 12,
            color: emphasize ? MyTheme.dark_grey : MyTheme.font_grey,
            fontWeight: emphasize ? FontWeight.w700 : FontWeight.w600,
          ),
        ),
      ],
    );
  }

  Widget _buildSectionTitle() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 10),
      child: Row(
        children: [
          const Text(
            "Recent Earnings",
            style: TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w700,
              color: Color.fromRGBO(39, 38, 43, 1),
            ),
          ),
          const Spacer(),
          Text(
            "${_totalData > 0 ? _totalData : _list.length} entries",
            style: TextStyle(
              fontSize: 12,
              color: MyTheme.font_grey,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildList() {
    if (_isInitial && _list.isEmpty) {
      return SingleChildScrollView(
        child: ShimmerHelper().buildListShimmer(item_count: 5, item_height: 100.0),
      );
    }

    if (_list.isEmpty) {
      return Padding(
        padding: const EdgeInsets.only(top: 40),
        child: Center(child: Text(LangText(context).local!.no_data_is_available)),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      itemCount: _list.length + 1,
      scrollDirection: Axis.vertical,
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      itemBuilder: (context, index) {
        if (index == _list.length) {
          return _buildLoadingContainer();
        }

        final entry = _list[index];
        final badgeColor =
            entry.delivery_status == "returned" || entry.delivery_status == "Returned"
                ? const Color.fromRGBO(209, 44, 44, 1)
                : const Color.fromRGBO(10, 132, 94, 1);

        return Padding(
          padding: const EdgeInsets.only(bottom: 10.0),
          child: GestureDetector(
            onTap: () {
              Navigator.push(context, MaterialPageRoute(builder: (context) {
                return OrderDetails(id: entry.order_id);
              }));
            },
            child: Card(
              shape: RoundedRectangleBorder(
                side: BorderSide(color: MyTheme.light_grey, width: 1.0),
                borderRadius: BorderRadius.circular(16.0),
              ),
              elevation: 0.0,
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            entry.order_code ?? "--",
                            style: TextStyle(
                              color: MyTheme.dark_grey,
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: badgeColor.withValues(alpha: .10),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            entry.delivery_status == "returned" ||
                                    entry.delivery_status == "Returned"
                                ? "Return"
                                : "Delivery",
                            style: TextStyle(
                              color: badgeColor,
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _buildMetaBlock("Date", entry.date),
                        ),
                        Expanded(
                          child: _buildMetaBlock("Earning", entry.earning,
                              valueColor: MyTheme.accent_color),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildMetaBlock(String label, String? value, {Color? valueColor}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: MyTheme.font_grey,
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value ?? "--",
          style: TextStyle(
            color: valueColor ?? MyTheme.dark_grey,
            fontSize: 14,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }

  Widget _buildLoadingContainer() {
    return Container(
      height: _showLoadingContainer ? 36 : 0,
      width: double.infinity,
      color: Colors.transparent,
      child: Center(
        child: Text(_totalData == _list.length
            ? LangText(context).local!.no_more_items_ucf
            : LangText(context).local!.loading_more_items_ucf),
      ),
    );
  }
}
