import 'package:active_ecommerce_cms_demo_app/custom/btn.dart';
import 'package:active_ecommerce_cms_demo_app/custom/enum_classes.dart';
import 'package:active_ecommerce_cms_demo_app/custom/toast_component.dart';
import 'package:active_ecommerce_cms_demo_app/custom/useful_elements.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/reg_ex_inpur_formatter.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shimmer_helper.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/wallet_repository.dart';
import 'package:active_ecommerce_cms_demo_app/screens/checkout/checkout.dart';
import 'package:active_ecommerce_cms_demo_app/screens/main.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:local_auth/local_auth.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../helpers/main_helpers.dart';

class Wallet extends StatefulWidget {
  const Wallet({super.key, this.fromRecharge = false});
  final bool fromRecharge;

  @override
  State<Wallet> createState() => _WalletState();
}

class _WalletState extends State<Wallet> {
  final _amountValidator = RegExInputFormatter.withRegex(
    '^\$|^(0|([1-9][0-9]{0,}))(\\.[0-9]{0,})?\$',
  );
  final ScrollController _mainScrollController = ScrollController();
  final TextEditingController _amountController = TextEditingController();

  GlobalKey appBarKey = GlobalKey();

  dynamic _balanceDetails;

  final List<dynamic> _rechargeList = [];
  bool _rechargeListInit = true;
  int _rechargePage = 1;
  int? _totalRechargeData = 0;
  bool _showRechageLoadingContainer = false;
  bool _showCardBack = false;
  bool _walletSecretsVisible = false;
  final LocalAuthentication _localAuthentication = LocalAuthentication();

  @override
  void initState() {
    super.initState();
    fetchAll();
    _mainScrollController.addListener(() {
      if (_mainScrollController.position.pixels ==
          _mainScrollController.position.maxScrollExtent) {
        setState(() {
          _rechargePage++;
        });
        _showRechageLoadingContainer = true;
        fetchRechargeList();
      }
    });
  }

  @override
  void dispose() {
    _mainScrollController.dispose();
    super.dispose();
  }

  fetchAll() {
    fetchBalanceDetails();
    fetchRechargeList();
  }

  fetchBalanceDetails() async {
    var balanceDetailsResponse = await WalletRepository().getBalance();

    _balanceDetails = balanceDetailsResponse;

    setState(() {});
  }

  fetchRechargeList() async {
    var rechageListResponse = await WalletRepository().getRechargeList(
      page: _rechargePage,
    );

    if (rechageListResponse.result == true) {
      _rechargeList.addAll(rechageListResponse.recharges ?? []);
      _totalRechargeData = rechageListResponse.meta?.total ?? 0;
    } else {}
    _rechargeListInit = false;
    _showRechageLoadingContainer = false;

    setState(() {});
  }

  reset() {
    _balanceDetails = null;
    _rechargeList.clear();
    _rechargeListInit = true;
    _rechargePage = 1;
    _totalRechargeData = 0;
    _showRechageLoadingContainer = false;
    setState(() {});
  }

  Future<void> _onPageRefresh() async {
    reset();
    fetchAll();
  }

  String get _displayName {
    final name = (user_name.$ ?? "").trim();
    if (name.isNotEmpty) {
      return name.toUpperCase();
    }
    return "VEXLINA USER";
  }

  String get _walletBalanceText =>
      convertPrice(_balanceDetails?.balance ?? "0");

  String get _cardSeed {
    final stored = (wallet_card_number.$).replaceAll(RegExp(r'\D'), '');
    final raw = "${user_id.$ ?? 0}${user_phone.$}${user_email.$}";
    final digits = stored.isNotEmpty
        ? stored
        : raw.replaceAll(RegExp(r'\D'), '');
    return digits.padRight(16, '8').substring(0, 16);
  }

  String get _formattedCardNumber {
    final seed = _cardSeed;
    return "${seed.substring(0, 4)} ${seed.substring(4, 8)} ${seed.substring(8, 12)} ${seed.substring(12, 16)}";
  }

  String get _maskedCardNumber =>
      "${_cardSeed.substring(0, 4)}****${_cardSeed.substring(12, 16)}";

  String get _expiryText {
    final month = wallet_card_expiry_month.$.padLeft(2, '0');
    final year = wallet_card_expiry_year.$.padLeft(2, '0');
    return "$month/$year";
  }

  String get _ccvText => wallet_card_cvv.$.padLeft(3, '0');

  Future<void> _copyCardNumber() async {
    if (!await _ensureWalletSecretsVisible()) return;
    await _copyWalletValue(_formattedCardNumber, "Card number copied");
  }

  Future<void> _copyWalletValue(String value, String successMessage) async {
    await Clipboard.setData(ClipboardData(text: value));
    if (!mounted) return;
    ToastComponent.showDialog(successMessage);
  }

  Future<void> _copyWalletExpiry() async {
    if (!await _ensureWalletSecretsVisible()) return;
    await _copyWalletValue(_expiryText, "MM/YY copied");
  }

  Future<void> _copyWalletCvv() async {
    if (!await _ensureWalletSecretsVisible()) return;
    await _copyWalletValue(_ccvText, "CVV copied");
  }

  Future<bool> _ensureWalletSecretsVisible() async {
    if (_walletSecretsVisible) {
      return true;
    }

    await _handleWalletSecretToggle();
    return _walletSecretsVisible;
  }

  Future<void> _handleWalletSecretToggle() async {
    if (_walletSecretsVisible) {
      setState(() {
        _walletSecretsVisible = false;
      });
      return;
    }

    bool canUseBiometric = false;

    try {
      final isSupported = await _localAuthentication.isDeviceSupported();
      final canCheckBiometrics = await _localAuthentication.canCheckBiometrics;
      final availableBiometrics = canCheckBiometrics
          ? await _localAuthentication.getAvailableBiometrics()
          : <BiometricType>[];
      canUseBiometric = isSupported && availableBiometrics.isNotEmpty;
    } catch (_) {
      canUseBiometric = false;
    }

    if (!canUseBiometric) {
      setState(() {
        _walletSecretsVisible = true;
      });
      return;
    }

    try {
      final authenticated = await _localAuthentication.authenticate(
        localizedReason: 'Authenticate to view card details',
        options: const AuthenticationOptions(
          biometricOnly: true,
          stickyAuth: false,
        ),
      );

      if (authenticated && mounted) {
        setState(() {
          _walletSecretsVisible = true;
        });
      }
    } catch (_) {
      if (!mounted) return;
      ToastComponent.showDialog("Biometric authentication failed");
    }
  }

  void _openPayQrScanner() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.black,
      builder: (context) {
        bool handled = false;

        return StatefulBuilder(
          builder: (context, setModalState) {
            return SafeArea(
              child: SizedBox(
                height: MediaQuery.of(context).size.height * .78,
                child: Stack(
                  children: [
                    MobileScanner(
                      onDetect: (capture) {
                        if (handled) return;
                        final String? rawValue =
                            capture.barcodes.first.rawValue;
                        if (rawValue == null || rawValue.isEmpty) return;
                        handled = true;
                        Navigator.pop(context);
                        _showActionInfo(
                          "QR Scanned",
                          "Scanned value:\n$rawValue",
                        );
                      },
                    ),
                    Positioned(
                      top: 18,
                      left: 18,
                      right: 18,
                      child: Row(
                        children: [
                          InkWell(
                            onTap: () => Navigator.pop(context),
                            borderRadius: BorderRadius.circular(999),
                            child: Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: .45),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.close_rounded,
                                color: Colors.white,
                              ),
                            ),
                          ),
                          const Spacer(),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 10,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.black.withValues(alpha: .45),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: const Text(
                              "Pay QR Scanner",
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    Center(
                      child: Container(
                        width: 240,
                        height: 240,
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.white, width: 3),
                          borderRadius: BorderRadius.circular(24),
                        ),
                      ),
                    ),
                    Positioned(
                      left: 24,
                      right: 24,
                      bottom: 24,
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: .55),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          "Align the QR code inside the frame to scan and pay.",
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            height: 1.45,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showActionInfo(String title, String message) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: MyTheme.dark_font_grey,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  message,
                  style: TextStyle(
                    fontSize: 13,
                    color: MyTheme.dark_grey,
                    height: 1.5,
                  ),
                ),
                const SizedBox(height: 18),
                SizedBox(
                  width: double.infinity,
                  child: Btn.basic(
                    color: MyTheme.accent_color,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Text(
                      "Close",
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    onPressed: () => Navigator.pop(context),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  onPressProceed() {
    var amountString = _amountController.text.toString();

    if (amountString == "") {
      ToastComponent.showDialog(
        AppLocalizations.of(context)!.amount_cannot_be_empty,
      );
      return;
    }

    var amount = double.parse(amountString);

    Navigator.of(context, rootNavigator: true).pop();
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) {
          return Checkout(
            paymentFor: PaymentFor.walletRecharge,
            rechargeAmount: amount,
            title: AppLocalizations.of(context)!.recharge_wallet_ucf,
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (widget.fromRecharge) {
          Navigator.of(context).pushAndRemoveUntil(
            MaterialPageRoute(builder: (_) => Main()),
            (route) => false,
          );
        } else {
          Navigator.of(context).pop();
        }
      },
      child: Directionality(
        textDirection: app_language_rtl.$!
            ? TextDirection.rtl
            : TextDirection.ltr,
        child: Scaffold(
          backgroundColor: const Color(0xFFF5F6FA),
          appBar: buildAppBar(context),
          body: RefreshIndicator(
            color: MyTheme.accent_color,
            backgroundColor: const Color(0xFFF5F6FA),
            onRefresh: _onPageRefresh,
            displacement: 10,
            child: ListView(
              controller: _mainScrollController,
              padding: const EdgeInsets.fromLTRB(16, 10, 16, 24),
              children: [
                _balanceDetails != null
                    ? buildTopSection(context)
                    : ShimmerHelper().buildBasicShimmer(height: 220),
                const SizedBox(height: 18),
                buildCardDetailsSection(),
                const SizedBox(height: 18),
                buildRechargeList(),
                buildLoadingContainer(),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Container buildLoadingContainer() {
    return Container(
      height: _showRechageLoadingContainer ? 36 : 20,
      width: double.infinity,
      color: Colors.transparent,
      child: Center(
        child: Text(
          _totalRechargeData == _rechargeList.length
              ? AppLocalizations.of(context)!.no_more_histories_ucf
              : AppLocalizations.of(context)!.loading_more_histories_ucf,
        ),
      ),
    );
  }

  AppBar buildAppBar(BuildContext context) {
    return AppBar(
      scrolledUnderElevation: 0.0,
      key: appBarKey,
      backgroundColor: const Color(0xFFF5F6FA),
      centerTitle: false,
      leading: Builder(
        builder: (context) => IconButton(
          icon: UsefulElements.backButton(context),
          onPressed: () {
            if (widget.fromRecharge) {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) {
                    return Main();
                  },
                ),
              );
            } else {
              return Navigator.of(context).pop();
            }
          },
        ),
      ),
      title: Text(
        AppLocalizations.of(context)!.my_wallet_ucf,
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

  buildRechargeList() {
    if (_rechargeListInit && _rechargeList.isEmpty) {
      return buildRechargeListShimmer();
    } else if (_rechargeList.isNotEmpty) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(bottom: 14.0),
            child: Text(
              "Transaction History",
              style: TextStyle(
                color: MyTheme.dark_font_grey,
                fontSize: 16,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          ListView.builder(
            padding: EdgeInsets.zero,
            itemCount: _rechargeList.length,
            scrollDirection: Axis.vertical,
            physics: const NeverScrollableScrollPhysics(),
            shrinkWrap: true,
            itemBuilder: (context, index) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 12.0),
                child: buildRechargeListItemCard(index),
              );
            },
          ),
        ],
      );
    } else if (_totalRechargeData == 0) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 26, horizontal: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Column(
          children: [
            Icon(Icons.receipt_long_rounded, color: MyTheme.grey_153, size: 34),
            const SizedBox(height: 10),
            Text(
              "Transaction History",
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: MyTheme.dark_font_grey,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              AppLocalizations.of(context)!.no_recharges_yet,
              style: TextStyle(color: MyTheme.dark_grey, fontSize: 13),
            ),
          ],
        ),
      );
    } else {
      return Container();
    }
  }

  Widget buildCardDetailsSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: .04),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "Card Details",
            style: TextStyle(
              color: MyTheme.dark_font_grey,
              fontSize: 16,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 14),
          Align(
            alignment: Alignment.centerRight,
            child: InkWell(
              onTap: _handleWalletSecretToggle,
              borderRadius: BorderRadius.circular(999),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: MyTheme.accent_color.withValues(alpha: .10),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  _walletSecretsVisible ? "Hide Details" : "Show Details",
                  style: TextStyle(
                    color: MyTheme.accent_color,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 14),
          buildCardDetailItem(
            label: "Card Number",
            value: _walletSecretsVisible ? _formattedCardNumber : _maskedCardNumber,
            onCopy: _copyCardNumber,
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: buildCardDetailItem(
                  label: "MM/YY",
                  value: _walletSecretsVisible ? _expiryText : "**/**",
                  onCopy: _copyWalletExpiry,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: buildCardDetailItem(
                  label: "CVV",
                  value: _walletSecretsVisible ? _ccvText : "***",
                  onCopy: _copyWalletCvv,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget buildCardDetailItem({
    required String label,
    required String value,
    required Future<void> Function() onCopy,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: const Color(0xFFFBFCFE),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFEEF1F5)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label.toUpperCase(),
                  style: TextStyle(
                    color: MyTheme.dark_grey,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    letterSpacing: .6,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  value,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: MyTheme.dark_font_grey,
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    letterSpacing: .8,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          InkWell(
            onTap: onCopy,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: MyTheme.accent_color.withValues(alpha: .10),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                Icons.copy_rounded,
                color: MyTheme.accent_color,
                size: 18,
              ),
            ),
          ),
        ],
      ),
    );
  }

  buildRechargeListShimmer() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(bottom: 14.0),
          child: Text(
            "Transaction History",
            style: TextStyle(
              color: MyTheme.dark_font_grey,
              fontSize: 16,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.only(bottom: 12.0),
          child: ShimmerHelper().buildBasicShimmer(height: 88.0),
        ),
        Padding(
          padding: const EdgeInsets.only(bottom: 12.0),
          child: ShimmerHelper().buildBasicShimmer(height: 88.0),
        ),
        Padding(
          padding: const EdgeInsets.only(bottom: 12.0),
          child: ShimmerHelper().buildBasicShimmer(height: 88.0),
        ),
      ],
    );
  }

  //main Container
  Widget buildRechargeListItemCard(int index) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: .04),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: MyTheme.accent_color.withValues(alpha: .10),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(
                Icons.account_balance_wallet_rounded,
                color: MyTheme.accent_color,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _rechargeList[index].paymentMethod ?? "Wallet transaction",
                    style: TextStyle(
                      color: MyTheme.dark_font_grey,
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _rechargeList[index].date ?? "-",
                    style: TextStyle(color: MyTheme.dark_grey, fontSize: 12),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _rechargeList[index].transactionNumber ?? "-",
                    style: TextStyle(color: MyTheme.dark_grey, fontSize: 12),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF5F6FA),
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: Text(
                      _rechargeList[index].approvalString ?? "-",
                      style: TextStyle(
                        color: MyTheme.dark_grey,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  convertPrice(_rechargeList[index].amount ?? "0"),
                  style: TextStyle(
                    color: MyTheme.accent_color,
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  getFormattedRechargeListIndex(index),
                  style: TextStyle(
                    color: MyTheme.grey_153,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget buildTopSection(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        GestureDetector(
          onTap: () {
            setState(() {
              _showCardBack = !_showCardBack;
            });
          },
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 280),
            curve: Curves.easeOutCubic,
            width: double.infinity,
            height: 248,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(28),
              gradient: _showCardBack
                  ? const LinearGradient(
                      colors: [Color(0xFF141B34), Color(0xFF28345E)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    )
                  : const LinearGradient(
                      colors: [Color(0xFFFA5A1E), Color(0xFFFE8B4B)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFFFA5A1E).withValues(alpha: .24),
                  blurRadius: 22,
                  offset: const Offset(0, 12),
                ),
              ],
            ),
            child: _showCardBack ? buildCardBack() : buildCardFront(),
          ),
        ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: buildActionButton(
                icon: Icons.qr_code_scanner_rounded,
                label: "Pay QR",
                onTap: _openPayQrScanner,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: buildActionButton(
                icon: Icons.add_circle_outline_rounded,
                label: "Add Money",
                onTap: () => buildShowAddFormDialog(context),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: buildActionButton(
                icon: Icons.swap_horiz_rounded,
                label: "Send Money",
                onTap: () => _showActionInfo(
                  "Send Money",
                  "Internal wallet transfer screen will be connected here.",
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget buildCardFront() {
    return Stack(
      children: [
        Positioned(
          right: -8,
          top: 26,
          child: SvgPicture.asset(
            'assets/wallet_card_shopping.svg',
            width: 150,
            height: 102,
          ),
        ),
        Positioned(
          left: -26,
          bottom: -26,
          child: Container(
            width: 110,
            height: 110,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.white.withValues(alpha: .08),
            ),
          ),
        ),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        "Vexlina Pay",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        "Available Balance",
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: .78),
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: .18),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: const Text(
                    "Virtual Card",
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              _walletBalanceText,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 32,
                fontWeight: FontWeight.w800,
                height: 1.1,
              ),
            ),
            const Spacer(),
            Text(
              _walletSecretsVisible ? _formattedCardNumber : _maskedCardNumber,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.8,
              ),
            ),
            const SizedBox(height: 14),
            Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "CARD HOLDER",
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: .72),
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 1.0,
                        ),
                      ),
                      const SizedBox(height: 5),
                      Text(
                        _displayName,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ],
                  ),
                ),
                InkWell(
                  onTap: _copyCardNumber,
                  borderRadius: BorderRadius.circular(999),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: .18),
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.copy_rounded, size: 14, color: Colors.white),
                        SizedBox(width: 7),
                        Text(
                          "Copy",
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ],
    );
  }

  Widget buildCardBack() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          height: 46,
          margin: const EdgeInsets.only(top: 8),
          decoration: BoxDecoration(
            color: Colors.black.withValues(alpha: .82),
            borderRadius: BorderRadius.circular(8),
          ),
        ),
        const SizedBox(height: 22),
        Align(
          alignment: Alignment.centerRight,
          child: Container(
            width: 150,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "MM/YY  ${_walletSecretsVisible ? _expiryText : '**/**'}",
                  style: TextStyle(
                    color: MyTheme.dark_font_grey,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  "CCV  ${_walletSecretsVisible ? _ccvText : '***'}",
                  style: TextStyle(
                    color: MyTheme.dark_font_grey,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ),
        const Spacer(),
        Text(
          "Tap card to flip back",
          style: TextStyle(
            color: Colors.white.withValues(alpha: .82),
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          _walletSecretsVisible ? _formattedCardNumber : _maskedCardNumber,
          style: TextStyle(
            color: Colors.white.withValues(alpha: .95),
            fontSize: 16,
            fontWeight: FontWeight.w700,
            letterSpacing: 1.8,
          ),
        ),
      ],
    );
  }

  Widget buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: .04),
              blurRadius: 16,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: MyTheme.accent_color.withValues(alpha: .10),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: MyTheme.accent_color),
            ),
            const SizedBox(height: 10),
            Text(
              label,
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: MyTheme.dark_font_grey,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  getFormattedRechargeListIndex(int index) {
    int num = index + 1;
    var txt = num.toString().length == 1 ? "# 0$num" : "#$num";
    return txt;
  }

  //   AlartDialog
  Future buildShowAddFormDialog(BuildContext context) {
    return showDialog(
      context: context,
      builder: (_) => Directionality(
        textDirection: app_language_rtl.$!
            ? TextDirection.rtl
            : TextDirection.ltr,
        child: AlertDialog(
          backgroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(6.0),
          ),
          insetPadding: EdgeInsets.symmetric(horizontal: 10),
          contentPadding: EdgeInsets.only(
            top: 36.0,
            left: 20.0,
            right: 22.0,
            bottom: 2.0,
          ),
          content: SizedBox(
            width: 400,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8.0),
                    child: Text(
                      AppLocalizations.of(context)!.amount_ucf,
                      style: TextStyle(
                        color: MyTheme.dark_font_grey,
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8.0),
                    child: SizedBox(
                      height: 40,
                      child: TextField(
                        controller: _amountController,
                        autofocus: false,
                        keyboardType: TextInputType.numberWithOptions(
                          decimal: true,
                        ),
                        inputFormatters: [_amountValidator],
                        decoration: InputDecoration(
                          fillColor: MyTheme.light_grey,
                          filled: true,
                          hintText: AppLocalizations.of(
                            context,
                          )!.enter_amount_ucf,
                          hintStyle: TextStyle(
                            fontSize: 12.0,
                            color: MyTheme.textfield_grey,
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color: MyTheme.noColor,
                              width: 0.0,
                            ),
                            borderRadius: const BorderRadius.all(
                              Radius.circular(8.0),
                            ),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderSide: BorderSide(
                              color: MyTheme.noColor,
                              width: 0.0,
                            ),
                            borderRadius: const BorderRadius.all(
                              Radius.circular(8.0),
                            ),
                          ),
                          contentPadding: EdgeInsets.symmetric(horizontal: 8.0),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          actions: [
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                //  Expanded(child: SizedBox()),
                Btn.minWidthFixHeight(
                  minWidth: 75,
                  height: 30,
                  color: Color.fromRGBO(253, 253, 253, 1),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(6.0),
                    side: BorderSide(color: MyTheme.accent_color, width: 1.0),
                  ),
                  child: Text(
                    AppLocalizations.of(context)!.close_ucf,
                    style: TextStyle(fontSize: 10, color: MyTheme.accent_color),
                  ),
                  onPressed: () {
                    Navigator.of(context, rootNavigator: true).pop();
                  },
                ),
                SizedBox(width: 14),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 0.0),
                  child: Btn.minWidthFixHeight(
                    minWidth: 75,
                    height: 30,
                    color: MyTheme.accent_color,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(6.0),
                    ),
                    child: Text(
                      AppLocalizations.of(context)!.proceed_ucf,
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.normal,
                      ),
                    ),
                    onPressed: () {
                      onPressProceed();
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
