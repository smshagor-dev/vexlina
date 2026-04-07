import 'package:active_flutter_delivery_app/custom/lang_text.dart';
import 'package:active_flutter_delivery_app/data_model/dashboard_summary_response.dart';
import 'package:active_flutter_delivery_app/helpers/connectivity_helper.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/dashboard_repository.dart';
import 'package:active_flutter_delivery_app/screens/assigned_delivery.dart';
import 'package:active_flutter_delivery_app/screens/cancelled_delivery.dart';
import 'package:active_flutter_delivery_app/screens/completed_delivery.dart';
import 'package:active_flutter_delivery_app/screens/earnings.dart';
import 'package:active_flutter_delivery_app/screens/on_the_way_delivery.dart';
import 'package:active_flutter_delivery_app/screens/pending.dart';
import 'package:active_flutter_delivery_app/screens/picked_delivery.dart';
import 'package:active_flutter_delivery_app/screens/reached_delivery.dart';
import 'package:active_flutter_delivery_app/ui_sections/drawer.dart';
import 'package:flutter/material.dart';

class Home extends StatefulWidget {
  @override
  _HomeState createState() => _HomeState();
}

class _HomeState extends State<Home> with WidgetsBindingObserver {
  ScrollController _mainScrollController = ScrollController();
  final GlobalKey<ScaffoldState> _scaffoldKey = new GlobalKey<ScaffoldState>();

  String _completedDelivery = ". . .";
  String _pendingDelivery = ". . .";
  String _totalCollection = ". . .";
  String _totalEarning = ". . .";
  String _returned = ". . .";
  String _onTheWay = ". . .";
  String _picked = ". . .";
  String _assigned = ". . .";
  String _reached = ". . .";
  DashboardSummaryResponse? _dashboardSummary;
  bool _isFetchingSummary = false;

  double mHeight = 0, mWidth = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    ConnectivityHelper().abortIfNotConnected(context, onPop);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refreshDashboardSummary();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _mainScrollController.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _refreshDashboardSummary();
    }
  }

  Future<void> fetchSummary() async {
    if (_isFetchingSummary || access_token.$ == null || access_token.$!.isEmpty) {
      return;
    }

    _isFetchingSummary = true;
    try {
      var dashboardSummaryResponse =
          await DashboardRepository().getDashboardSummaryResponse();
      _dashboardSummary = dashboardSummaryResponse;

      _completedDelivery =
          (dashboardSummaryResponse.completed_orders ??
                      dashboardSummaryResponse.completed_delivery)
                  ?.toString() ??
              ". . .";
      _pendingDelivery =
          dashboardSummaryResponse.pending_delivery?.toString() ?? ". . .";
      _totalCollection = dashboardSummaryResponse.total_collection ?? "...";
      _totalEarning = dashboardSummaryResponse.total_earning ?? "....";
      _returned = (dashboardSummaryResponse.return_orders ??
              dashboardSummaryResponse.cancelled)
          ?.toString() ??
          ". . .";
      _onTheWay = dashboardSummaryResponse.on_the_way?.toString() ?? ". . .";
      _picked = dashboardSummaryResponse.picked?.toString() ?? ". . .";
      _assigned = (dashboardSummaryResponse.upcoming_orders ??
                  dashboardSummaryResponse.assigned)
              ?.toString() ??
          ". . .";
      _reached = (dashboardSummaryResponse.reached_orders ??
                  dashboardSummaryResponse.reached)
              ?.toString() ??
          ". . .";

      if (mounted) {
        setState(() {});
      }
    } finally {
      _isFetchingSummary = false;
    }
  }

  Future<void> _onPageRefresh() async {
    reset();
    await fetchSummary();
  }

  Future<void> _refreshDashboardSummary() async {
    reset();
    await fetchSummary();
  }

  reset() {
    _completedDelivery = ". . .";
    _pendingDelivery = ". . .";
    _totalCollection = ". . .";
    _totalEarning = ". . .";
    _returned = ". . .";
    _onTheWay = ". . .";
    _picked = ". . .";
    _assigned = ". . .";
    _reached = ". . .";
    _dashboardSummary = null;
    setState(() {});
  }

  onPop(value) {
    ConnectivityHelper().abortIfNotConnected(context, onPop);
    _refreshDashboardSummary();
  }

  @override
  Widget build(BuildContext context) {
    mHeight = MediaQuery.of(context).size.height;
    mWidth = MediaQuery.of(context).size.width;
    return WillPopScope(
      onWillPop: () async => false,
      child: Scaffold(
        appBar: buildAppBar(context),
        key: _scaffoldKey,
        drawer: MainDrawer(),
        body: buildBody(context),
      ),
    );
  }

  AppBar buildAppBar(BuildContext context) {
    return AppBar(
      leading: GestureDetector(
        onTap: () {
          _scaffoldKey.currentState!.openDrawer();
        },
        child: Builder(
          builder: (context) => Padding(
            padding:
                const EdgeInsets.symmetric(vertical: 18.0, horizontal: 0.0),
            child: Container(
              child: Image.asset(
                "assets/hamburger.png",
                height: 16,
                color: MyTheme.grey_153,
              ),
            ),
          ),
        ),
      ),
      title: Text(
        LangText(context).local!.dashboard_ucf,
        style: TextStyle(fontSize: 16, color: Colors.white),
      ),
      elevation: 0.0,
      titleSpacing: 0,
      backgroundColor: Color.fromRGBO(39, 38, 43, 1),
    );
  }

  buildBody(context) {
    if (PortalHelper.isPickupPointApp) {
      return RefreshIndicator(
        color: MyTheme.accent_color,
        backgroundColor: Colors.white,
        onRefresh: _onPageRefresh,
        child: CustomScrollView(
          controller: _mainScrollController,
          physics: AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  children: [
                    _buildPickupOverviewCard(),
                    const SizedBox(height: 16),
                    if ((_dashboardSummary?.return_due_orders_count ?? 0) > 0) ...[
                      _buildReturnDueAlertCard(),
                      const SizedBox(height: 16),
                    ],
                    _buildPickupStatusGrid(context),
                  ],
                ),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      color: MyTheme.accent_color,
      backgroundColor: Colors.white,
      onRefresh: _onPageRefresh,
      child: CustomScrollView(
        controller: _mainScrollController,
        physics: AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverList(
            delegate: SliverChildListDelegate([
              buildTopContainer(),
              buildSecondContainer(),
              buildHomeMenuRow(context)
            ]),
          ),
        ],
      ),
    );
  }

  Widget _buildPickupOverviewCard() {
    final pickupPoint = _dashboardSummary?.pickup_point;
    final managerName =
        (user_name.$ != null && user_name.$!.trim().isNotEmpty)
            ? user_name.$!.trim()
            : "Pickup Point Manager";

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(18),
        gradient: LinearGradient(
          colors: [
            const Color.fromRGBO(39, 38, 43, 1),
            MyTheme.accent_color,
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: MyTheme.accent_color.withValues(alpha: .18),
            blurRadius: 24,
            offset: const Offset(0, 14),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "Greetings",
            style: TextStyle(
              color: Colors.white.withValues(alpha: .85),
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
          const SizedBox(height: 10),
          if ((pickupPoint?.address ?? "").isNotEmpty)
            Text(
              pickupPoint!.address!,
              style: TextStyle(
                color: Colors.white.withValues(alpha: .82),
                fontSize: 13,
              ),
            ),
          if ((pickupPoint?.phone ?? "").isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 3),
              child: Text(
                "Mobile: ${pickupPoint!.phone!}",
                style: TextStyle(
                  color: Colors.white.withValues(alpha: .82),
                  fontSize: 13,
                ),
              ),
            ),
          if ((pickupPoint?.internal_code ?? "").isNotEmpty ||
              (pickupPoint?.working_hours ?? "").isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 3),
              child: Text(
                [
                  if ((pickupPoint?.internal_code ?? "").isNotEmpty)
                    "Code: ${pickupPoint!.internal_code}",
                  if ((pickupPoint?.working_hours ?? "").isNotEmpty)
                    "Hours: ${pickupPoint!.working_hours}",
                ].join("  |  "),
                style: TextStyle(
                  color: Colors.white.withValues(alpha: .78),
                  fontSize: 12,
                ),
              ),
            ),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: .12),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.white.withValues(alpha: .12)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: _buildOverviewMetric(
                    "Upcoming",
                    _assigned,
                    onTap: () => _openPickupScreen(
                      AssignedDelivery(show_back_button: true),
                    ),
                  ),
                ),
                Expanded(
                  child: _buildOverviewMetric(
                    "Reached",
                    _reached,
                    onTap: () => _openPickupScreen(
                      ReachedDelivery(show_back_button: true),
                    ),
                  ),
                ),
                Expanded(
                  child: _buildOverviewMetric(
                    "Complete",
                    _completedDelivery,
                    onTap: () => _openPickupScreen(
                      CompletedDelivery(show_back_button: true),
                    ),
                  ),
                ),
                Expanded(
                  child: _buildOverviewMetric(
                    "Returns",
                    _returned,
                    onTap: () => _openPickupScreen(
                      CancelledDelivery(show_back_button: true),
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

  Widget _buildOverviewMetric(String label, String value, {VoidCallback? onTap}) {
    return InkWell(
      borderRadius: BorderRadius.circular(10),
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 4),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Text(
              label,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.white.withValues(alpha: .72),
                fontSize: 11,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              value,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildReturnDueAlertCard() {
    final dueOrders = _dashboardSummary?.return_due_orders ?? [];
    final dueCount = _dashboardSummary?.return_due_orders_count ?? dueOrders.length;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color.fromRGBO(255, 244, 237, 1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: const Color.fromRGBO(250, 62, 0, .18),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                height: 38,
                width: 38,
                decoration: BoxDecoration(
                  color: const Color.fromRGBO(250, 62, 0, .12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.assignment_return_outlined,
                  color: Color.fromRGBO(250, 62, 0, 1),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      "Return Reminder",
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: Color.fromRGBO(39, 38, 43, 1),
                      ),
                    ),
                    Text(
                      "$dueCount reached order${dueCount > 1 ? 's are' : ' is'} waiting for return action.",
                      style: TextStyle(
                        fontSize: 12,
                        color: MyTheme.grey_153,
                      ),
                    ),
                  ],
                ),
              ),
              TextButton(
                onPressed: () => _openPickupScreen(
                  CancelledDelivery(show_back_button: true),
                ),
                child: const Text("Open"),
              ),
            ],
          ),
          if (dueOrders.isNotEmpty) ...[
            const SizedBox(height: 10),
            ...dueOrders.map((order) {
              return Padding(
                padding: const EdgeInsets.only(top: 6),
                child: Text(
                  "Order ${order.code ?? '-'} reached on ${order.reached_at ?? '-'} and should be returned if the customer did not receive it.",
                  style: TextStyle(
                    fontSize: 12,
                    color: MyTheme.grey_153,
                    height: 1.4,
                  ),
                ),
              );
            }).toList(),
          ],
        ],
      ),
    );
  }

  void _openPickupScreen(Widget screen) {
    Navigator.push(context, MaterialPageRoute(builder: (_) {
      return screen;
    })).then((value) => onPop(value));
  }

  Widget _buildPickupStatusGrid(BuildContext context) {
    final cards = [
      _PickupDashboardCardData(
        title: PortalHelper.upcomingOrdersLabel,
        count: _assigned,
        color: MyTheme.blue,
        screen: AssignedDelivery(show_back_button: true),
      ),
      _PickupDashboardCardData(
        title: PortalHelper.pickedUpOrdersLabel,
        count: _picked,
        color: MyTheme.golden,
        screen: PickedDelivery(show_back_button: true),
      ),
      _PickupDashboardCardData(
        title: PortalHelper.onTheWayOrdersLabel,
        count: _onTheWay,
        color: MyTheme.red,
        screen: OnTheWayDelivery(show_back_button: true),
      ),
      _PickupDashboardCardData(
        title: PortalHelper.reachedOrdersLabel,
        count: _reached,
        color: MyTheme.lime,
        screen: ReachedDelivery(show_back_button: true),
      ),
      _PickupDashboardCardData(
        title: PortalHelper.completedLabel,
        count: _completedDelivery,
        color: const Color.fromRGBO(10, 132, 94, 1),
        screen: CompletedDelivery(show_back_button: true),
      ),
      _PickupDashboardCardData(
        title: PortalHelper.returnOrdersLabel,
        count: _returned,
        color: const Color.fromRGBO(209, 44, 44, 1),
        screen: CancelledDelivery(show_back_button: true),
      ),
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: cards.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 12,
        crossAxisSpacing: 12,
        childAspectRatio: 1.25,
      ),
      itemBuilder: (context, index) {
        final card = cards[index];
        return InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () {
            Navigator.push(context, MaterialPageRoute(builder: (_) {
              return card.screen;
            })).then((value) => onPop(value));
          },
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: card.color.withValues(alpha: .14)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: .04),
                  blurRadius: 14,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  height: 40,
                  width: 40,
                  decoration: BoxDecoration(
                    color: card.color.withValues(alpha: .12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(Icons.inventory_2_outlined, color: card.color),
                ),
                const Spacer(),
                Text(
                  card.title,
                  maxLines: 2,
                  style: TextStyle(
                    fontSize: 13,
                    color: MyTheme.font_grey,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  card.count,
                  style: TextStyle(
                    fontSize: 26,
                    color: card.color,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  buildTopContainer() {
    return Container(
      width: double.infinity,
      height: 350,
      color: Color.fromRGBO(39, 38, 43, 1),
      child: Padding(
        padding: const EdgeInsets.only(
            top: 8.0, bottom: 16.0, left: 8.0, right: 8.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildHeroCard(
                  color: MyTheme.lime,
                  icon: "assets/delivery_moving.png",
                  label: PortalHelper.completedLabel,
                  value: _completedDelivery,
                  onTap: () {
                    Navigator.push(context,
                        MaterialPageRoute(builder: (context) {
                      return CompletedDelivery(show_back_button: true);
                    })).then((value) {
                      onPop(value);
                    });
                  },
                ),
                _buildHeroCard(
                  color: MyTheme.red,
                  icon: "assets/clock.png",
                  label: PortalHelper.pendingLabel,
                  value: _pendingDelivery,
                  onTap: () {
                    Navigator.push(context,
                        MaterialPageRoute(builder: (context) {
                      return Pending();
                    })).then((value) {
                      onPop(value);
                    });
                  },
                ),
              ],
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildHeroCard(
                  color: MyTheme.orange,
                  icon: "assets/delivery_moving.png",
                  label: PortalHelper.collectionOrReturnLabel,
                  value: PortalHelper.isPickupPointApp
                      ? _returned
                      : _totalCollection,
                  onTap: () {
                    Navigator.push(context,
                        MaterialPageRoute(builder: (context) {
                      return CancelledDelivery(show_back_button: true);
                    })).then((value) {
                      onPop(value);
                    });
                  },
                ),
                _buildHeroCard(
                  color: MyTheme.blue,
                  icon: "assets/dollar.png",
                  label: PortalHelper.earningsLabel,
                  value: _totalEarning,
                  onTap: () {
                    Navigator.push(context,
                        MaterialPageRoute(builder: (context) {
                      return Earnings(show_back_button: true);
                    })).then((value) {
                      onPop(value);
                    });
                  },
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroCard({
    required Color color,
    required String icon,
    required String label,
    required String value,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Container(
        height: 145,
        width: 170,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.all(Radius.circular(12)),
        ),
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.only(top: 24.0),
              child: Container(
                height: 50,
                width: 50,
                child: Image.asset(
                  icon,
                  color: Colors.grey.shade300,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(top: 4.0),
              child: Text(
                label,
                style: TextStyle(color: Colors.grey.shade300),
                textAlign: TextAlign.center,
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(top: 4.0),
              child: Text(
                value,
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 24,
                    fontWeight: FontWeight.w600),
              ),
            )
          ],
        ),
      ),
    );
  }

  buildSecondContainer() {
    return InkWell(
      onTap: () {
        Navigator.push(context, MaterialPageRoute(builder: (context) {
          return CancelledDelivery(show_back_button: true);
        }));
      },
      child: Container(
        width: double.infinity,
        height: 70,
        color: MyTheme.red,
        child: Row(
          children: [
            Padding(
              padding: const EdgeInsets.only(left: 16.0),
              child: Container(
                height: 28,
                width: 28,
                child: Image.asset(
                  "assets/cross_in_a_box.png",
                  color: Colors.white,
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(left: 16.0),
              child: Text(
                PortalHelper.returnedLabel,
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w600),
              ),
            ),
            Spacer(),
            Padding(
              padding: const EdgeInsets.only(right: 20.0),
              child: Text(
                _returned,
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 24,
                    fontWeight: FontWeight.w600),
              ),
            )
          ],
        ),
      ),
    );
  }

  buildHomeMenuRow(context) {
    return Padding(
      padding: EdgeInsets.only(
          top: 24.0, left: 16.0, right: 16.0, bottom: mHeight > 600 ? 0 : 100),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          InkWell(
            onTap: () {
              Navigator.push(context, MaterialPageRoute(builder: (context) {
                return PortalHelper.isPickupPointApp
                    ? ReachedDelivery(show_back_button: true)
                    : Pending(index: 0);
              })).then((value) {
                onPop(value);
              });
            },
            child: _buildMiniCircle(
              color: MyTheme.red,
              icon: PortalHelper.isPickupPointApp
                  ? null
                  : "assets/human_run.png",
              label: PortalHelper.isPickupPointApp
                  ? "Reached ($_reached)"
                  : "${LangText(context).local!.on_the_way_ucf} ($_onTheWay)",
              fallbackIcon: PortalHelper.isPickupPointApp
                  ? const Icon(Icons.task_alt, color: Colors.white)
                  : null,
            ),
          ),
          InkWell(
            onTap: () {
              Navigator.push(context, MaterialPageRoute(builder: (context) {
                return Pending(index: PortalHelper.isPickupPointApp ? 2 : 1);
              })).then((value) {
                onPop(value);
              });
            },
            child: _buildMiniCircle(
              color: MyTheme.golden,
              icon: "assets/press.png",
              label: "${PortalHelper.pickedLabel} ($_picked)",
            ),
          ),
          InkWell(
            onTap: () {
              Navigator.push(context, MaterialPageRoute(builder: (context) {
                return Pending(index: PortalHelper.isPickupPointApp ? 3 : 2);
              })).then((value) {
                onPop(value);
              });
            },
            child: _buildMiniCircle(
              color: MyTheme.blue,
              icon: "assets/sandclock.png",
              label: "${PortalHelper.upcomingLabel} ($_assigned)",
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMiniCircle({
    required Color color,
    String? icon,
    Widget? fallbackIcon,
    required String label,
  }) {
    return Column(
      children: [
        Container(
          height: 60,
          width: 60,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
          ),
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: fallbackIcon ??
                Image.asset(
                  icon!,
                  color: Colors.white,
                ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.only(top: 8),
          child: SizedBox(
            width: 92,
            child: Text(
              label,
              textAlign: TextAlign.center,
              style: TextStyle(
                  fontSize: 14, color: color, fontWeight: FontWeight.w600),
            ),
          ),
        )
      ],
    );
  }
}

class _PickupDashboardCardData {
  _PickupDashboardCardData({
    required this.title,
    required this.count,
    required this.color,
    required this.screen,
  });

  final String title;
  final String count;
  final Color color;
  final Widget screen;
}
