import 'package:active_flutter_delivery_app/custom/lang_text.dart';
import 'package:active_flutter_delivery_app/custom/toast_component.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shimmer_helper.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/delivery_repository.dart';
import 'package:active_flutter_delivery_app/screens/delivery_qr_scanner.dart';
import 'package:active_flutter_delivery_app/screens/order_details.dart';
import 'package:active_flutter_delivery_app/ui_sections/drawer.dart';
import 'package:flutter/material.dart';
import 'package:toast/toast.dart';

class ReachedDelivery extends StatefulWidget {
  final bool show_back_button;

  const ReachedDelivery({Key? key, this.show_back_button = false})
      : super(key: key);

  @override
  State<ReachedDelivery> createState() => _ReachedDeliveryState();
}

class _ReachedDeliveryState extends State<ReachedDelivery> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final ScrollController _scrollController = ScrollController();

  List<dynamic> _list = [];
  bool _isInitial = true;
  int _page = 1;
  int _totalData = 0;
  bool _showLoadingContainer = false;
  final List<int> _processedIds = [];

  @override
  void initState() {
    super.initState();
    fetchData();
    _scrollController.addListener(() {
      if (_scrollController.position.pixels ==
          _scrollController.position.maxScrollExtent) {
        setState(() {
          _page++;
          _showLoadingContainer = true;
        });
        fetchData();
      }
    });
  }

  Future<void> fetchData() async {
    var listResponse = await DeliveryRepository().getDeliveryListResponse(
      page: _page,
      type: "reached",
    );
    _list.addAll(listResponse.orders ?? []);
    _isInitial = false;
    _totalData = listResponse.meta?.total ?? 0;
    _showLoadingContainer = false;
    setState(() {});
  }

  Future<void> _onRefresh() async {
    _list.clear();
    _processedIds.clear();
    _isInitial = true;
    _page = 1;
    _totalData = 0;
    _showLoadingContainer = false;
    setState(() {});
    await fetchData();
  }

  Future<void> _changeStatus(int orderId, String status) async {
    final response = await DeliveryRepository().getDeliveryStatusChangeResponse(
      status: status,
      order_id: orderId,
    );

    ToastComponent.showDialog(
      response.message ?? "Status updated",
      context,
      gravity: Toast.center,
      duration: Toast.lengthLong,
    );

    if (response.result == true) {
      _processedIds.add(orderId);
      setState(() {});
    }
  }

  Future<void> _scanAndReachOrder() async {
    final scannedCode = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => const DeliveryQrScanner(
          title: 'Scan QR to Reach',
          description:
              'Scan the order QR code to move an on-the-way order into reached status.',
        ),
      ),
    );

    if (scannedCode == null || scannedCode.toString().trim().isEmpty) {
      return;
    }

    final response = await DeliveryRepository().getDeliveryStatusChangeResponse(
      status: "reached",
      delivery_verification_code: scannedCode.toString().trim(),
    );

    ToastComponent.showDialog(
      response.message ?? "Status updated",
      context,
      gravity: Toast.center,
      duration: Toast.lengthLong,
    );

    if (response.result == true) {
      await _onRefresh();
    }
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async => widget.show_back_button,
      child: Scaffold(
        backgroundColor: Colors.white,
        appBar: buildAppBar(context),
        key: _scaffoldKey,
        drawer: MainDrawer(),
        body: Stack(
          children: [
            RefreshIndicator(
              color: MyTheme.accent_color,
              backgroundColor: Colors.white,
              onRefresh: _onRefresh,
              child: _buildList(),
            ),
            Align(
              alignment: Alignment.bottomCenter,
              child: Container(
                height: _showLoadingContainer ? 36 : 0,
                width: double.infinity,
                color: Colors.white,
                child: Center(
                  child: Text(_totalData == _list.length
                      ? "No More Items"
                      : "Loading More Items ..."),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  PreferredSize buildAppBar(BuildContext context) {
    return PreferredSize(
      preferredSize: Size.fromHeight(96.0),
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
                      PortalHelper.reachedOrdersLabel,
                      style: TextStyle(
                          fontSize: 16, color: MyTheme.accent_color),
                    ),
                    const Spacer(),
                    if (PortalHelper.isPickupPointApp)
                      Padding(
                        padding: const EdgeInsets.only(right: 12),
                        child: TextButton.icon(
                          style: TextButton.styleFrom(
                            backgroundColor: MyTheme.accent_color,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                          ),
                          onPressed: _scanAndReachOrder,
                          icon: const Icon(Icons.qr_code_scanner, size: 18),
                          label: const Text(
                            "Scan QR",
                            style: TextStyle(fontWeight: FontWeight.w700),
                          ),
                        ),
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

  Widget _buildList() {
    if (_isInitial && _list.isEmpty) {
      return SingleChildScrollView(
        child: ShimmerHelper().buildListShimmer(item_count: 5, item_height: 100),
      );
    }

    if (_list.isEmpty) {
      return Center(child: Text(LangText(context).local!.no_data_is_available));
    }

    return SingleChildScrollView(
      controller: _scrollController,
      physics: const BouncingScrollPhysics(
          parent: AlwaysScrollableScrollPhysics()),
      child: ListView.builder(
        padding: const EdgeInsets.all(8.0),
        itemCount: _list.length,
        scrollDirection: Axis.vertical,
        physics: NeverScrollableScrollPhysics(),
        shrinkWrap: true,
        itemBuilder: (context, index) {
          final order = _list[index];
          final processed = _processedIds.contains(order.id);
          return Padding(
            padding: const EdgeInsets.only(bottom: 8.0),
            child: Card(
              shape: RoundedRectangleBorder(
                side: BorderSide(color: MyTheme.light_grey, width: 1.0),
                borderRadius: BorderRadius.circular(8.0),
              ),
              elevation: 0.0,
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(order.code ?? '',
                        style: TextStyle(
                            color: MyTheme.blue,
                            fontSize: 14,
                            fontWeight: FontWeight.w700)),
                    SizedBox(height: 8),
                    Row(
                      children: [
                        Text(order.date ?? '',
                            style: TextStyle(
                                color: MyTheme.font_grey, fontSize: 13)),
                        Spacer(),
                        Text(order.grand_total ?? '',
                            style: TextStyle(
                                color: MyTheme.blue,
                                fontSize: 14,
                                fontWeight: FontWeight.w700)),
                      ],
                    ),
                    SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () {
                              Navigator.push(context,
                                  MaterialPageRoute(builder: (context) {
                                return OrderDetails(id: order.id);
                              }));
                            },
                            child: Text("View Details"),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 8),
                    if (!processed)
                      Row(
                        children: [
                          Expanded(
                            child: TextButton(
                              style: TextButton.styleFrom(
                                backgroundColor: MyTheme.lime,
                              ),
                              onPressed: () => _changeStatus(order.id, "delivered"),
                              child: Text(
                                "Complete",
                                style: TextStyle(color: Colors.white),
                              ),
                            ),
                          ),
                          SizedBox(width: 8),
                          Expanded(
                            child: TextButton(
                              style: TextButton.styleFrom(
                                backgroundColor: MyTheme.red,
                              ),
                              onPressed: () => _changeStatus(order.id, "returned"),
                              child: Text(
                                "Return",
                                style: TextStyle(color: Colors.white),
                              ),
                            ),
                          ),
                        ],
                      )
                    else
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: MyTheme.light_grey,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          "Updated",
                          style: TextStyle(
                            color: MyTheme.font_grey,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
