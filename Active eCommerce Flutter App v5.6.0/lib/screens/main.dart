import 'dart:io';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:active_ecommerce_cms_demo_app/main.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/bottom_appbar_index.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/cart_counter.dart';
import 'package:active_ecommerce_cms_demo_app/screens/auth/login.dart';
import 'package:active_ecommerce_cms_demo_app/screens/category_list_n_product/category_list.dart';
import 'package:active_ecommerce_cms_demo_app/screens/checkout/cart.dart';
import 'package:active_ecommerce_cms_demo_app/screens/home.dart';
import 'package:active_ecommerce_cms_demo_app/screens/profile.dart';
import 'package:active_ecommerce_cms_demo_app/screens/reals.dart';
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
  late final List<Widget> _children;
  final CartCounter counter = CartCounter();
  final BottomAppbarIndex bottomAppbarIndex = BottomAppbarIndex();

  bool _dialogShowing = false;

  @override
  void initState() {
    super.initState();

    _children = [
      const Home(),
      const RealsScreen(),
      Cart(hasBottomnav: true, fromNavigation: true, counter: counter),
      CategoryList(slug: "", isBaseCategory: true),
      const Profile(),
    ];

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
    if (!guest_checkout_status.$ && index == 2 && !is_logged_in.$) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => const Login()));
      return;
    }
    if (index == 4) {
      routes.push("/dashboard");
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
        ? Colors.white
        : Colors.white.withValues(alpha: 0.62);
  }

  Widget _navIcon(String assetPath, int index, {double? size}) {
    return Padding(
      padding: EdgeInsets.only(bottom: 3.h),
      child: Image.asset(
        assetPath,
        height: size ?? 17.h,
        color: _itemColor(index),
      ),
    );
  }

  BottomNavigationBarItem _navItem({
    required String assetPath,
    required String label,
    required int index,
    double? iconSize,
  }) {
    return BottomNavigationBarItem(
      icon: _navIcon(assetPath, index, size: iconSize),
      activeIcon: _navIcon(assetPath, index, size: iconSize),
      label: label,
    );
  }

  BottomNavigationBarItem _cartNavItem(BuildContext context) {
    final badgeColor = Color.lerp(MyTheme.accent_color, Colors.white, 0.1)!;

    Widget buildCartIcon() {
      return Padding(
        padding: EdgeInsets.only(bottom: 1.h),
        child: badges.Badge(
          position: badges.BadgePosition.topEnd(top: -8.h, end: -10.w),
          badgeStyle: badges.BadgeStyle(
            shape: badges.BadgeShape.circle,
            badgeColor: badgeColor,
            padding: EdgeInsets.all(4.r),
          ),
          badgeAnimation: const badges.BadgeAnimation.slide(toAnimate: false),
          badgeContent: Consumer<CartCounter>(
            builder: (context, cart, child) {
              return Text(
                "${cart.cartCounter}",
                style: TextStyle(
                  fontSize: 9.sp,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              );
            },
          ),
          child: Container(
            width: 34.w,
            height: 34.w,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: _currentIndex == 2
                  ? Colors.white.withValues(alpha: 0.10)
                  : Colors.transparent,
              shape: BoxShape.circle,
            ),
            child: Image.asset(
              "assets/cart.png",
              height: 18.h,
              color: _itemColor(2),
            ),
          ),
        ),
      );
    }

    return BottomNavigationBarItem(
      icon: buildCartIcon(),
      activeIcon: buildCartIcon(),
      label: AppLocalizations.of(context)!.cart_ucf,
    );
  }

  @override
  Widget build(BuildContext context) {
    return PopScope<Object?>(
      canPop: false,
      onPopInvokedWithResult: _handlePop,
      child: Directionality(
        textDirection: app_language_rtl.$!
            ? TextDirection.rtl
            : TextDirection.ltr,
        child: Scaffold(
          extendBody: true,
          body: _children[_currentIndex],
          bottomNavigationBar: SafeArea(
            minimum: EdgeInsets.fromLTRB(20.w, 0, 20.w, 10.h),
            child: Container(
              height: 82.h,
              padding: EdgeInsets.symmetric(horizontal: 10.w, vertical: 8.h),
              decoration: BoxDecoration(
                color: const Color(0xff111318),
                borderRadius: BorderRadius.circular(26.r),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.22),
                    blurRadius: 18.r,
                    offset: Offset(0, 8.h),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(22.r),
                child: Theme(
                  data: Theme.of(context).copyWith(
                    splashColor: Colors.transparent,
                    highlightColor: Colors.transparent,
                    bottomNavigationBarTheme: BottomNavigationBarThemeData(
                      backgroundColor: const Color(0xff111318),
                      selectedItemColor: Colors.white,
                      unselectedItemColor: Colors.white.withValues(alpha: 0.62),
                      selectedIconTheme: const IconThemeData(color: Colors.white),
                      unselectedIconTheme: IconThemeData(
                        color: Colors.white.withValues(alpha: 0.62),
                      ),
                      selectedLabelStyle: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 10.sp,
                        height: 1.25,
                      ),
                      unselectedLabelStyle: TextStyle(
                        fontWeight: FontWeight.w500,
                        fontSize: 10.sp,
                        height: 1.25,
                      ),
                    ),
                  ),
                  child: BottomNavigationBar(
                    type: BottomNavigationBarType.fixed,
                    currentIndex: _currentIndex,
                    onTap: _onTapped,
                    backgroundColor: const Color(0xff111318),
                    elevation: 0,
                    iconSize: 18.sp,
                    showSelectedLabels: true,
                    showUnselectedLabels: true,
                    selectedFontSize: 10.sp,
                    unselectedFontSize: 10.sp,
                    selectedItemColor: Colors.white,
                    unselectedItemColor: Colors.white.withValues(alpha: 0.62),
                    landscapeLayout: BottomNavigationBarLandscapeLayout.centered,
                    items: [
                      _navItem(
                        assetPath: "assets/home.png",
                        index: 0,
                        label: AppLocalizations.of(context)!.home_ucf,
                      ),
                      _navItem(
                        assetPath: "assets/shorts_logo.png",
                        index: 1,
                        iconSize: 16.h,
                        label: "Reals",
                      ),
                      _cartNavItem(context),
                      _navItem(
                        assetPath: "assets/categories.png",
                        index: 3,
                        label: AppLocalizations.of(context)!.categories_ucf,
                      ),
                      _navItem(
                        assetPath: "assets/profile.png",
                        index: 4,
                        label: AppLocalizations.of(context)!.profile_ucf,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
