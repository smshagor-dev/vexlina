import 'dart:io';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/cart_counter.dart';
import 'package:active_ecommerce_cms_demo_app/screens/auth/login.dart';
import 'package:active_ecommerce_cms_demo_app/screens/category_list_n_product/category_list.dart';
import 'package:active_ecommerce_cms_demo_app/screens/checkout/cart.dart';
import 'package:active_ecommerce_cms_demo_app/screens/home.dart';
import 'package:active_ecommerce_cms_demo_app/screens/profile.dart';
import 'package:active_ecommerce_cms_demo_app/screens/reals.dart';
import 'package:active_ecommerce_cms_demo_app/screens/wallet.dart';
import 'package:badges/badges.dart' as badges;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:provider/provider.dart';

class Main extends StatefulWidget {
  final bool goBack;
  const Main({super.key, this.goBack = true});

  @override
  State<Main> createState() => _MainState();
}

class _MainState extends State<Main> {
  int _currentIndex = 0;
  final CartCounter counter = CartCounter();

  bool _dialogShowing = false;

  @override
  void initState() {
    super.initState();

    _fetchAll();

    SystemChrome.setEnabledSystemUIMode(
      SystemUiMode.manual,
      overlays: [SystemUiOverlay.top, SystemUiOverlay.bottom],
    );
  }

  void _fetchAll() {
    Provider.of<CartCounter>(context, listen: false).getCount();
  }

  void _onTapped(int index) {
    _fetchAll();
    if (!is_logged_in.$ && (index == 2 || index == 3 || index == 5)) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => const Login()));
      return;
    }
    setState(() {
      _currentIndex = index;
    });
  }

  Future<void> _handlePop(bool didPop, Object? result) async {
    if (didPop) return;

    if (_currentIndex != 0) {
      setState(() => _currentIndex = 0);
      _fetchAll();
      return;
    }

    if (_dialogShowing) return;

    _dialogShowing = true;

    final shouldExit =
        await showDialog<bool>(
          context: context,
          barrierDismissible: false,
          builder: (_) => Directionality(
            textDirection: app_language_rtl.$!
                ? TextDirection.rtl
                : TextDirection.ltr,
            child: AlertDialog(
              content: Text(
                AppLocalizations.of(context)!.do_you_want_close_the_app,
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context, true),
                  child: Text(AppLocalizations.of(context)!.yes_ucf),
                ),
                TextButton(
                  onPressed: () => Navigator.pop(context, false),
                  child: Text(AppLocalizations.of(context)!.no_ucf),
                ),
              ],
            ),
          ),
        ) ??
        false;

    _dialogShowing = false;

    if (shouldExit) {
      if (Platform.isAndroid) {
        SystemNavigator.pop();
      } else {
        exit(0);
      }
    }
  }

  Color _itemColor(int index) {
    return _currentIndex == index
        ? const Color(0xFFFA3E00)
        : const Color(0xFF8E8E93);
  }

  Widget _navIcon(String assetPath, int index, {double? size}) {
    return Image.asset(
      assetPath,
      height: size ?? 22.h,
      color: _itemColor(index),
    );
  }

  Widget _navButton({
    required String assetPath,
    required String label,
    required int index,
    double? iconSize,
  }) {
    return Expanded(
      child: InkWell(
        borderRadius: BorderRadius.circular(14.r),
        onTap: () => _onTapped(index),
        child: Container(
          padding: EdgeInsets.only(top: 10.h, bottom: 6.h),
          decoration: BoxDecoration(
            border: Border(
              top: BorderSide(
                color: _currentIndex == index
                    ? const Color(0xFFFA3E00)
                    : Colors.transparent,
                width: 2.5,
              ),
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _navIcon(assetPath, index, size: iconSize),
              SizedBox(height: 5.h),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 10.sp,
                  fontWeight: _currentIndex == index
                      ? FontWeight.w700
                      : FontWeight.w500,
                  color: _itemColor(index),
                  height: 1.0,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _cartNavButton(BuildContext context) {
    return Expanded(
      child: InkWell(
        borderRadius: BorderRadius.circular(14.r),
        onTap: () => _onTapped(3),
        child: Container(
          padding: EdgeInsets.only(top: 10.h, bottom: 6.h),
          decoration: BoxDecoration(
            border: Border(
              top: BorderSide(
                color: _currentIndex == 3
                    ? const Color(0xFFFA3E00)
                    : Colors.transparent,
                width: 2.5,
              ),
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              badges.Badge(
                position: badges.BadgePosition.topEnd(top: -5.h, end: -8.w),
                badgeStyle: badges.BadgeStyle(
                  shape: badges.BadgeShape.circle,
                  badgeColor: const Color(0xFFFA3E00),
                  padding: EdgeInsets.all(4.r),
                ),
                badgeAnimation: const badges.BadgeAnimation.slide(
                  toAnimate: false,
                ),
                badgeContent: Consumer<CartCounter>(
                  builder: (context, cart, child) {
                    return Text(
                      "${cart.cartCounter}",
                      style: TextStyle(
                        fontSize: 8.sp,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    );
                  },
                ),
                child: _navIcon("assets/cart.png", 3, size: 22.h),
              ),
              SizedBox(height: 5.h),
              Text(
                AppLocalizations.of(context)!.cart_ucf,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 10.sp,
                  fontWeight: _currentIndex == 3
                      ? FontWeight.w700
                      : FontWeight.w500,
                  color: _itemColor(3),
                  height: 1.0,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _bottomNavigation(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(
          top: BorderSide(color: const Color(0xFFE9E4DD), width: 1),
        ),
        boxShadow: [
          BoxShadow(
            blurRadius: 16.r,
            offset: Offset(0, -4.h),
            color: Colors.black.withValues(alpha: 0.04),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 66.h,
          child: Row(
            children: [
              _navButton(
                assetPath: "assets/home.png",
                index: 0,
                label: AppLocalizations.of(context)!.home_ucf,
              ),
              _navButton(
                assetPath: "assets/shorts_logo.png",
                index: 1,
                iconSize: 22.h,
                label: "Reels",
              ),
              _navButton(
                assetPath: "assets/wallet.png",
                index: 2,
                label: "Wallet",
              ),
              _cartNavButton(context),
              _navButton(
                assetPath: "assets/categories.png",
                index: 4,
                label: AppLocalizations.of(context)!.categories_ucf,
              ),
              _navButton(
                assetPath: "assets/profile.png",
                index: 5,
                label: AppLocalizations.of(context)!.profile_ucf,
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final children = [
      const Home(),
      RealsScreen(isVisible: _currentIndex == 1),
      const Wallet(),
      Cart(hasBottomnav: true, fromNavigation: true, counter: counter),
      CategoryList(slug: "", isBaseCategory: true),
      const Profile(),
    ];

    return PopScope<Object?>(
      canPop: false,
      onPopInvokedWithResult: _handlePop,
      child: Directionality(
        textDirection: app_language_rtl.$!
            ? TextDirection.rtl
            : TextDirection.ltr,
        child: Scaffold(
          backgroundColor: const Color(0xFFF5F2EE),
          resizeToAvoidBottomInset: false,
          body: SafeArea(
            bottom: false,
            child: IndexedStack(index: _currentIndex, children: children),
          ),
          bottomNavigationBar: _bottomNavigation(context),
        ),
      ),
    );
  }
}
