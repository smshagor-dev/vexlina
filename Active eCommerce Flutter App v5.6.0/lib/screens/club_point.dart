import 'package:active_ecommerce_cms_demo_app/custom/btn.dart';
import 'package:active_ecommerce_cms_demo_app/custom/device_info.dart';
import 'package:active_ecommerce_cms_demo_app/custom/lang_text.dart';
import 'package:active_ecommerce_cms_demo_app/custom/toast_component.dart';
import 'package:active_ecommerce_cms_demo_app/custom/useful_elements.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shimmer_helper.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/clubpoint_repository.dart';
import 'package:active_ecommerce_cms_demo_app/screens/wallet.dart';
import 'package:flutter/material.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';

class Clubpoint extends StatefulWidget {
  const Clubpoint({super.key});

  @override
  State<Clubpoint> createState() => _ClubpointState();
}

class _ClubpointState extends State<Clubpoint> {
  final ScrollController _xcrollController = ScrollController();

  final List<dynamic> _list = [];
  final List<dynamic> _summaryList = [];
  final List<dynamic> _convertedIds = [];
  bool _isInitial = true;
  int _page = 1;
  int? _totalData = 0;
  bool _showLoadingContainer = false;
  bool _isBulkConverting = false;
  bool _isSummaryLoading = true;
  double? _exchangeRate;
  double? _exchangeWalletAmount;

  @override
  void initState() {
    super.initState();
    fetchData();
    _fetchSummaryData();
    _xcrollController.addListener(() {
      if (_xcrollController.position.pixels ==
          _xcrollController.position.maxScrollExtent) {
        setState(() {
          _page++;
        });
        _showLoadingContainer = true;
        fetchData();
      }
    });
  }

  Future<void> fetchData() async {
    var clubpointResponse = await ClubpointRepository()
        .getClubPointListResponse(page: _page);
    setState(() {
      _list.addAll(clubpointResponse.clubpoints ?? []);
      _isInitial = false;
      _totalData = clubpointResponse.meta?.total ?? 0;
      _showLoadingContainer = false;
    });
  }

  reset() {
    setState(() {
      _list.clear();
      _summaryList.clear();
      _convertedIds.clear();
      _isInitial = true;
      _totalData = 0;
      _page = 1;
      _showLoadingContainer = false;
      _isBulkConverting = false;
      _isSummaryLoading = true;
      _exchangeRate = null;
      _exchangeWalletAmount = null;
    });
  }

  Future<void> _onRefresh() async {
    reset();
    await fetchData();
    await _fetchSummaryData();
  }

  onPressConvert(itemId, SnackBar convertedSnackbar) async {
    if (itemId == null) return;

    var clubpointToWalletResponse = await ClubpointRepository()
        .getClubpointToWalletResponse(itemId);
    if (!mounted) return;
    if (clubpointToWalletResponse.result == false) {
      ToastComponent.showDialog(clubpointToWalletResponse.message);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(convertedSnackbar);
      setState(() {
        _convertedIds.add(itemId);
      });
    }
  }

  onPopped(value) async {
    reset();
    await fetchData();
    await _fetchSummaryData();
  }

  Future<void> _fetchSummaryData() async {
    final summaryItems = <dynamic>[];
    int currentPage = 1;
    int lastPage = 1;

    do {
      final clubpointResponse = await ClubpointRepository()
          .getClubPointListResponse(page: currentPage);
      summaryItems.addAll(clubpointResponse.clubpoints ?? []);
      lastPage = clubpointResponse.meta?.lastPage ?? currentPage;
      _exchangeRate ??= clubpointResponse.exchangeRate;
      _exchangeWalletAmount ??= clubpointResponse.exchangeWalletAmount;
      currentPage++;
    } while (currentPage <= lastPage);

    if (!mounted) return;

    setState(() {
      _summaryList
        ..clear()
        ..addAll(summaryItems);
      _isSummaryLoading = false;
    });
  }

  String? get _exchangeRateText {
    if (_exchangeRate == null || _exchangeRate! <= 0) {
      return null;
    }

    final rate = _exchangeRate! % 1 == 0
        ? _exchangeRate!.toInt().toString()
        : _exchangeRate!.toStringAsFixed(2);
    final walletAmount = (_exchangeWalletAmount ?? 1) % 1 == 0
        ? (_exchangeWalletAmount ?? 1).toInt().toString()
        : (_exchangeWalletAmount ?? 1).toStringAsFixed(2);

    return '$rate Points = $walletAmount Wallet Money';
  }

  List<dynamic> get _pointSourceList =>
      _summaryList.isNotEmpty ? _summaryList : _list;

  double get _totalUnconvertedPoint {
    return _pointSourceList.fold<double>(0, (sum, item) {
      final alreadyConverted =
          item.convertStatus == 1 || _convertedIds.contains(item.id);
      if (alreadyConverted) {
        return sum;
      }

      final rawValue = item.convertibleClubPoint ?? item.points ?? 0;
      final numericValue = rawValue is num
          ? rawValue.toDouble()
          : double.tryParse(rawValue.toString()) ?? 0;

      if (numericValue <= 0) {
        return sum;
      }

      return sum + numericValue;
    });
  }

  String get _totalUnconvertedPointText {
    final total = _totalUnconvertedPoint;
    return total % 1 == 0 ? total.toInt().toString() : total.toStringAsFixed(1);
  }

  bool get _hasConvertiblePoint {
    return _pointSourceList.any((item) {
      final alreadyConverted =
          item.convertStatus == 1 || _convertedIds.contains(item.id);
      if (alreadyConverted) {
        return false;
      }

      final rawValue = item.convertibleClubPoint ?? item.points ?? 0;
      final numericValue = rawValue is num
          ? rawValue.toDouble()
          : double.tryParse(rawValue.toString()) ?? 0;
      return numericValue > 0;
    });
  }

  Future<void> _onPressConvertAll(SnackBar convertedSnackbar) async {
    if (_isBulkConverting || !_hasConvertiblePoint) return;

    setState(() {
      _isBulkConverting = true;
    });

    int convertedCount = 0;
    String? failedMessage;

    for (final item in _pointSourceList) {
      final alreadyConverted =
          item.convertStatus == 1 || _convertedIds.contains(item.id);
      final rawValue = item.convertibleClubPoint ?? item.points ?? 0;
      final numericValue = rawValue is num
          ? rawValue.toDouble()
          : double.tryParse(rawValue.toString()) ?? 0;

      if (alreadyConverted || numericValue <= 0) {
        continue;
      }

      final response = await ClubpointRepository().getClubpointToWalletResponse(
        item.id,
      );

      if (response.result == false) {
        failedMessage = response.message?.toString() ?? "Convert failed";
        break;
      }

      convertedCount++;
      _convertedIds.add(item.id);
    }

    await _fetchSummaryData();

    if (!mounted) return;

    setState(() {
      _isBulkConverting = false;
    });

    if (convertedCount > 0) {
      ScaffoldMessenger.of(context).showSnackBar(convertedSnackbar);
    }

    if (failedMessage != null) {
      ToastComponent.showDialog(failedMessage);
    } else if (convertedCount == 0) {
      ToastComponent.showDialog("No convertible point found");
    }
  }

  @override
  Widget build(BuildContext context) {
    SnackBar convertedSnackbar = SnackBar(
      content: Text(
        AppLocalizations.of(context)?.points_converted_to_wallet ??
            "Points converted to wallet",
        style: TextStyle(color: MyTheme.font_grey),
      ),
      backgroundColor: MyTheme.soft_accent_color,
      duration: const Duration(seconds: 3),
      action: SnackBarAction(
        label:
            AppLocalizations.of(context)?.show_wallet_all_capital ??
            "SHOW WALLET",
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) {
                return Wallet();
              },
            ),
          ).then((value) {
            onPopped(value);
          });
        },
        textColor: MyTheme.accent_color,
        disabledTextColor: Colors.grey,
      ),
    );

    return Directionality(
      textDirection: app_language_rtl.$!
          ? TextDirection.rtl
          : TextDirection.ltr,
      child: Scaffold(
        backgroundColor: MyTheme.mainColor,
        appBar: buildAppBar(context),
        body: Stack(
          children: [
            RefreshIndicator(
              color: MyTheme.accent_color,
              backgroundColor: Colors.white,
              onRefresh: _onRefresh,
              displacement: 0,
              child: CustomScrollView(
                controller: _xcrollController,
                physics: const BouncingScrollPhysics(
                  parent: AlwaysScrollableScrollPhysics(),
                ),
                slivers: [
                  SliverList(
                    delegate: SliverChildListDelegate([
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        child: Column(
                          children: [
                            buildSummarySection(convertedSnackbar),
                            const SizedBox(height: 16),
                            buildList(convertedSnackbar),
                          ],
                        ),
                      ),
                    ]),
                  ),
                ],
              ),
            ),
            Align(
              alignment: Alignment.bottomCenter,
              child: buildLoadingContainer(),
            ),
          ],
        ),
      ),
    );
  }

  Container buildLoadingContainer() {
    return Container(
      height: _showLoadingContainer ? 36 : 0,
      width: double.infinity,
      color: Colors.white,
      child: Center(
        child: Text(
          _totalData == _list.length
              ? AppLocalizations.of(context)?.no_more_items_ucf ??
                    "No more items"
              : AppLocalizations.of(context)?.loading_more_items_ucf ??
                    "Loading more items",
        ),
      ),
    );
  }

  AppBar buildAppBar(BuildContext context) {
    return AppBar(
      backgroundColor: MyTheme.mainColor,
      scrolledUnderElevation: 0.0,
      centerTitle: false,
      leading: Builder(
        builder: (context) => IconButton(
          icon: UsefulElements.backButton(context),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      title: Text(
        AppLocalizations.of(context)?.earned_points_ucf ?? "Earned Points",
        style: TextStyle(
          fontSize: 16,
          color: MyTheme.dark_font_grey,
          fontWeight: FontWeight.bold,
        ),
      ),
      elevation: 0.0,
      titleSpacing: 0,
    );
  }

  buildList(SnackBar convertedSnackbar) {
    if (_isInitial && _list.isEmpty) {
      return SingleChildScrollView(
        child: ShimmerHelper().buildListShimmer(
          itemCount: 10,
          itemHeight: 100.0,
        ),
      );
    } else if (_list.isNotEmpty) {
      return SingleChildScrollView(
        child: ListView.separated(
          separatorBuilder: (context, index) => SizedBox(height: 16),
          itemCount: _list.length,
          scrollDirection: Axis.vertical,
          padding: EdgeInsets.all(0.0),
          physics: NeverScrollableScrollPhysics(),
          shrinkWrap: true,
          itemBuilder: (context, index) {
            return buildItemCard(index, convertedSnackbar);
          },
        ),
      );
    } else if (_totalData == 0) {
      return Center(
        child: Text(
          AppLocalizations.of(context)?.no_data_is_available ??
              "No data available",
        ),
      );
    } else {
      return Container();
    }
  }

  Widget buildSummarySection(SnackBar convertedSnackbar) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            MyTheme.accent_color.withValues(alpha: .96),
            const Color(0xffFF7A2F),
          ],
        ),
        boxShadow: [
          BoxShadow(
            color: MyTheme.accent_color.withValues(alpha: .18),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Available Points',
            style: TextStyle(
              color: Colors.white.withValues(alpha: .92),
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 6),
          _isSummaryLoading
              ? const SizedBox(
                  width: 74,
                  height: 30,
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.4,
                        color: Colors.white,
                      ),
                    ),
                  ),
                )
              : Text(
                  _totalUnconvertedPointText,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 28,
                    fontWeight: FontWeight.w800,
                  ),
                ),
          const SizedBox(height: 4),
          Text(
            _isSummaryLoading
                ? 'Calculating all available points...'
                : 'Total points that are not converted yet',
            style: TextStyle(
              color: Colors.white.withValues(alpha: .88),
              fontSize: 12,
            ),
          ),
          if (_exchangeRateText != null) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: .14),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.currency_exchange_rounded,
                    size: 14,
                    color: Colors.white.withValues(alpha: .92),
                  ),
                  const SizedBox(width: 6),
                  Flexible(
                    child: Text(
                      'Exchange Rate: $_exchangeRateText',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: .92),
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 14),
          InkWell(
            borderRadius: BorderRadius.circular(14),
            onTap:
                _hasConvertiblePoint && !_isBulkConverting && !_isSummaryLoading
                ? () => _onPressConvertAll(convertedSnackbar)
                : null,
            child: Container(
              height: 42,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: _hasConvertiblePoint && !_isSummaryLoading
                    ? Colors.white
                    : Colors.white.withValues(alpha: .25),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.white.withValues(alpha: .35)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: _hasConvertiblePoint && !_isSummaryLoading
                          ? MyTheme.accent_color.withValues(alpha: .12)
                          : Colors.white.withValues(alpha: .18),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: _isBulkConverting
                        ? Center(
                            child: SizedBox(
                              width: 14,
                              height: 14,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: MyTheme.accent_color,
                              ),
                            ),
                          )
                        : Icon(
                            Icons.sync_alt_rounded,
                            size: 15,
                            color: _hasConvertiblePoint && !_isSummaryLoading
                                ? MyTheme.accent_color
                                : Colors.white,
                          ),
                  ),
                  const SizedBox(width: 10),
                  Text(
                    _isBulkConverting ? 'Converting...' : 'Convert All',
                    style: TextStyle(
                      color: _hasConvertiblePoint && !_isSummaryLoading
                          ? MyTheme.accent_color
                          : Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Icon(
                    Icons.arrow_forward_rounded,
                    size: 16,
                    color: _hasConvertiblePoint && !_isSummaryLoading
                        ? MyTheme.accent_color
                        : Colors.white,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget buildItemCard(int index, SnackBar convertedSnackbar) {
    final item = _list[index];
    return Container(
      height: 91,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14.0),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            SizedBox(
              width: DeviceInfo(context).width! / 2.5,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    item.orderCode ?? "",
                    style: TextStyle(
                      color: MyTheme.dark_font_grey,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Row(
                    children: [
                      Text(
                        "${AppLocalizations.of(context)?.converted_ucf ?? "Converted"} - ",
                        style: TextStyle(
                          fontSize: 12,
                          color: MyTheme.dark_font_grey,
                        ),
                      ),
                      Text(
                        (item.convertStatus == 1 ||
                                _convertedIds.contains(item.id))
                            ? LangText(context).local.yes_ucf
                            : LangText(context).local.no_ucf,
                        style: TextStyle(
                          fontSize: 12,
                          color: item.convertStatus == 1
                              ? Colors.green
                              : Colors.blue,
                        ),
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      Text(
                        "${AppLocalizations.of(context)?.date_ucf ?? "Date"} : ",
                        style: TextStyle(
                          fontSize: 12,
                          color: MyTheme.dark_font_grey,
                        ),
                      ),
                      Text(
                        item.date ?? "",
                        style: TextStyle(
                          fontSize: 12,
                          color: MyTheme.dark_font_grey,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            SizedBox(
              width: DeviceInfo(context).width! / 2.5,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    item.convertibleClubPoint?.toString() ?? "0",
                    style: TextStyle(
                      color: MyTheme.accent_color,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 10),
                  item.convertStatus == 1 || _convertedIds.contains(item.id)
                      ? Text(
                          AppLocalizations.of(context)?.done_all_capital ??
                              "DONE",
                          style: TextStyle(
                            color: MyTheme.grey_153,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        )
                      : (item.convertibleClubPoint ?? 0) <= 0
                      ? Text(
                          AppLocalizations.of(context)?.refunded_ucf ??
                              "Refunded",
                          style: TextStyle(
                            color: MyTheme.grey_153,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        )
                      : SizedBox(
                          height: 24,
                          width: 80,
                          child: Btn.basic(
                            color: MyTheme.accent_color,
                            child: Text(
                              AppLocalizations.of(context)?.convert_now_ucf ??
                                  "CONVERT NOW",
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 10,
                              ),
                            ),
                            onPressed: () {
                              onPressConvert(item.id, convertedSnackbar);
                            },
                          ),
                        ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
