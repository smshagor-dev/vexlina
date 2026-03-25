import 'dart:io';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:active_ecommerce_cms_demo_app/main.dart';
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
    return index == 2
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
        borderRadius: BorderRadius.circular(18.r),
        onTap: () => _onTapped(index),
        child: Padding(
          padding: EdgeInsets.symmetric(vertical: 8.h),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _navIcon(assetPath, index, size: iconSize),
              SizedBox(height: 6.h),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 12.sp,
                  fontWeight: FontWeight.w500,
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

  Widget _cartButton(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(32.r),
      onTap: () => _onTapped(2),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          badges.Badge(
            position: badges.BadgePosition.topEnd(top: -4.h, end: -2.w),
            badgeStyle: badges.BadgeStyle(
              shape: badges.BadgeShape.circle,
              badgeColor: const Color(0xFFFA3E00),
              padding: EdgeInsets.all(4.r),
            ),
            badgeAnimation: const badges.BadgeAnimation.slide(toAnimate: false),
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
            child: Container(
              width: 64.w,
              height: 64.w,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [
                    Color(0xFFFA3E00),
                    Color(0xFFFF6A2A),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFFFA3E00).withValues(alpha: 0.30),
                    blurRadius: 25.r,
                    offset: Offset(0, 10.h),
                  ),
                ],
              ),
              child: Image.asset(
                "assets/cart.png",
                height: 28.h,
                color: Colors.white,
              ),
            ),
          ),
          SizedBox(height: 6.h),
          Text(
            AppLocalizations.of(context)!.cart_ucf,
            style: TextStyle(
              fontSize: 12.sp,
              fontWeight: FontWeight.w500,
              color: const Color(0xFFFA3E00),
              height: 1.0,
            ),
          ),
        ],
      ),
    );
  }

  Widget _bottomNavigation(BuildContext context) {
    return Container(
      height: 70.h,
      margin: EdgeInsets.symmetric(horizontal: 16.w),
      padding: EdgeInsets.symmetric(horizontal: 24.w),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.9),
        borderRadius: BorderRadius.circular(25.r),
        boxShadow: [
          BoxShadow(
            blurRadius: 20.r,
            offset: Offset(0, 8.h),
            color: Colors.black.withValues(alpha: 0.1),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
          SizedBox(width: 64.w),
          _navButton(
            assetPath: "assets/categories.png",
            index: 3,
            label: AppLocalizations.of(context)!.categories_ucf,
          ),
          _navButton(
            assetPath: "assets/profile.png",
            index: 4,
            label: AppLocalizations.of(context)!.profile_ucf,
          ),
        ],
      ),
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
          backgroundColor: const Color(0xFFF5F2EE),
          resizeToAvoidBottomInset: false,
          body: Stack(
            clipBehavior: Clip.none,
            children: [
              Positioned.fill(
                child: SafeArea(
                  bottom: false,
                  child: SizedBox.expand(child: _children[_currentIndex]),
                ),
              ),
              Positioned(
                left: 0,
                right: 0,
                bottom: 20.h,
                child: _bottomNavigation(context),
              ),
              Positioned(
                left: 0,
                right: 0,
                bottom: 42.h,
                child: Center(child: _cartButton(context)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
