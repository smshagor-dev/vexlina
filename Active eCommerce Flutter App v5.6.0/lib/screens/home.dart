import 'package:active_ecommerce_cms_demo_app/custom/flash%20deals%20banner/flash_deal_banner.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/business_setting_helper.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shimmer_helper.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/home_presenter.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/unRead_notification_counter.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/address_repository.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/clubpoint_repository.dart';
import 'package:active_ecommerce_cms_demo_app/screens/address.dart';
import 'package:active_ecommerce_cms_demo_app/screens/chat/messenger_list.dart';
import 'package:active_ecommerce_cms_demo_app/screens/club_point.dart';
import 'package:active_ecommerce_cms_demo_app/screens/filter.dart';
import 'package:active_ecommerce_cms_demo_app/screens/notification/notification_list.dart';
import 'package:active_ecommerce_cms_demo_app/screens/orders/order_details.dart';
import 'package:active_ecommerce_cms_demo_app/screens/orders/order_list.dart';
import 'package:active_ecommerce_cms_demo_app/screens/profile.dart';
import 'package:active_ecommerce_cms_demo_app/screens/flash_deal/flash_deal_list.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/todays_deal_products.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/home_section_products.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/top_selling_products.dart';
import 'package:active_ecommerce_cms_demo_app/screens/top_sellers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_countdown_timer/index.dart';
import 'package:provider/provider.dart';
import '../custom/home_all_products_2.dart';
import '../custom/home_banner_one.dart';
import '../custom/home_banner_three.dart';
import '../custom/home_carousel_slider.dart';
import '../custom/home_search_box.dart';
import '../data_model/flash_deal_response.dart' hide Product;
import '../data_model/product_mini_response.dart' show Product;
import '../single_banner/sincle_banner_page.dart';
import '../ui_elements/home_product_card.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class Home extends StatefulWidget {
  const Home({
    super.key,
    this.title,
    this.showBackButton = false,
    this.goBack = true,
  });

  final String? title;
  final bool showBackButton;
  final bool goBack;

  @override
  State<Home> createState() => _HomeState();
}

class _HomeState extends State<Home> with TickerProviderStateMixin {
  final HomePresenter homeData = HomePresenter();
  final FlashDealResponseDatum flashDealResponseDatum =
      FlashDealResponseDatum();
  String _defaultAddressLabel = 'Add Address';
  String _clubPointLabel = '0';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchData();
      precacheImage(const AssetImage("assets/todays_deal.png"), context);
      precacheImage(const AssetImage("assets/flash_deal.png"), context);
      precacheImage(const AssetImage("assets/brands.png"), context);
      precacheImage(const AssetImage("assets/top_sellers.png"), context);
    });
    homeData.mainScrollListener();
    homeData.initPiratedAnimation(this);
  }

  Future<void> _fetchData() async {
    await BusinessSettingHelper().setBusinessSettingData();
    if (mounted) {
      setState(() {});
    }
    await homeData.onRefresh();
    await _fetchHeaderQuickActions();
  }

  @override
  void dispose() {
    homeData.dispose();
    super.dispose();
  }

  Future<void> _fetchHeaderQuickActions() async {
    if (!mounted) return;

    if (!is_logged_in.$ || (access_token.$?.isEmpty ?? true)) {
      setState(() {
        _defaultAddressLabel = 'Add Address';
        _clubPointLabel = '0';
      });
      return;
    }

    context.read<UnReadNotificationCounter>().getCount();

    try {
      final addressResponse = await AddressRepository().getAddressList();
      final List<dynamic> addresses = addressResponse.addresses ?? [];
      dynamic selectedAddress;

      for (final address in addresses) {
        if (address.setDefault == 1) {
          selectedAddress = address;
          break;
        }
      }

      selectedAddress ??= addresses.isNotEmpty ? addresses.first : null;

      String addressLabel = 'Add Address';
      if (selectedAddress != null) {
        addressLabel = _buildAddressLabel(selectedAddress);
      }

      String pointLabel = '0';
      if (club_point_addon_installed.$) {
        final clubPoints = <dynamic>[];
        int currentPage = 1;
        int lastPage = 1;

        do {
          final clubPointResponse = await ClubpointRepository()
              .getClubPointListResponse(page: currentPage);
          clubPoints.addAll(clubPointResponse.clubpoints ?? []);
          lastPage = clubPointResponse.meta?.lastPage ?? currentPage;
          currentPage++;
        } while (currentPage <= lastPage);

        final totalPoints = clubPoints.fold<double>(0, (sum, item) {
          final isConverted = item.convertStatus == 1;
          if (isConverted) {
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

        pointLabel = totalPoints % 1 == 0
            ? totalPoints.toInt().toString()
            : totalPoints.toStringAsFixed(1);
      }

      if (!mounted) return;
      setState(() {
        _defaultAddressLabel = addressLabel;
        _clubPointLabel = pointLabel;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _defaultAddressLabel = 'Add Address';
        _clubPointLabel = '0';
      });
    }
  }

  String _buildAddressLabel(dynamic address) {
    final parts = <String>[
      address.address?.toString().trim() ?? '',
      address.cityName?.toString().trim() ?? '',
      address.areaName?.toString().trim() ?? '',
    ].where((part) => part.isNotEmpty).toList();

    if (parts.isEmpty) {
      return 'Add Address';
    }

    return parts.join(', ');
  }

  void _openProfileLinkedPage(Widget screen) {
    if (!is_logged_in.$) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const Profile(showBackButton: true)),
      );
      return;
    }

    Navigator.push(context, MaterialPageRoute(builder: (_) => screen));
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: widget.goBack,
      child: Directionality(
        textDirection: app_language_rtl.$!
            ? TextDirection.rtl
            : TextDirection.ltr,
        child: SafeArea(
          child: Scaffold(
            backgroundColor: Colors.white,
            body: RefreshIndicator(
              color: MyTheme.accent_color,
              backgroundColor: Colors.white,
              onRefresh: _fetchData,
              displacement: 0,
              child: CustomScrollView(
                controller: homeData.mainScrollController,
                physics: const BouncingScrollPhysics(
                  parent: AlwaysScrollableScrollPhysics(),
                ),
                slivers: <Widget>[
                  SliverAppBar(
                    floating: false,
                    snap: false,
                    pinned: true,
                    backgroundColor: Colors.white,
                    elevation: 0,
                    scrolledUnderElevation: 0.0,
                    automaticallyImplyLeading: false,
                    toolbarHeight: 88.h,
                    title: Padding(
                      padding: EdgeInsets.zero,
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          _buildTopQuickActions(context),
                          SizedBox(height: 8.h),
                          GestureDetector(
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (context) => const Filter(),
                                ),
                              );
                            },
                            child: HomeSearchBox(context: context),
                          ),
                        ],
                      ),
                    ),
                  ),

                  SliverList(
                    delegate: SliverChildListDelegate([
                      const SizedBox(height: 0),
                      ListenableBuilder(
                        listenable: homeData,
                        builder: (context, child) => HomeCarouselSlider(
                          homeData: homeData,
                          context: context,
                        ),
                      ),
                      SizedBox(height: 8.h),
                    ]),
                  ),

                  // Sticky Menu
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) {
                      return SliverPersistentHeader(
                        pinned: true,
                        delegate: StickyMenuDelegate(homeData: homeData),
                      );
                    },
                  ),
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildOnTheWayQrSection(context, homeData),
                  ),
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildTodaysDealSection(context, homeData),
                  ),
                  //Second banner
                  SliverList(
                    delegate: SliverChildListDelegate([
                      SizedBox(height: 5.h),
                      ListenableBuilder(
                        listenable: homeData,
                        builder: (context, child) =>
                            HomeBannerOne(context: context, homeData: homeData),
                      ),
                    ]),
                  ),
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildFeaturedProductsSection(context, homeData),
                  ),
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildInlineBannerSection(context, homeData),
                  ),
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildNewArrivalSection(context, homeData),
                  ),
                  //Flash Deal
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) {
                      final featuredDeal = homeData.getFeaturedFlashDeal();
                      final bool hasActiveFlashDeal =
                          featuredDeal != null &&
                          featuredDeal.date != null &&
                          DateTime.fromMillisecondsSinceEpoch(
                            featuredDeal.date! * 1000,
                          ).isAfter(DateTime.now());

                      if (!hasActiveFlashDeal) {
                        return const SliverToBoxAdapter(
                          child: SizedBox.shrink(),
                        );
                      }

                      return _buildFlashDealSection(context, homeData);
                    },
                  ),
                  //Single Banner
                  const SliverList(
                    delegate: SliverChildListDelegate.fixed([PhotoWidget()]),
                  ),
                  //All Products
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildAllProductsSection(context, homeData),
                  ),
                  ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildProductLoadingContainer(context, homeData),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  SliverList _buildFlashDealSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    var featuredDeal = homeData.getFeaturedFlashDeal();

    String sectionTitle = (featuredDeal != null && featuredDeal.title != null)
        ? featuredDeal.title!
        : AppLocalizations.of(context)!.flash_deal_ucf;

    return SliverList(
      delegate: SliverChildListDelegate([
        Container(
          color: Colors.blue.shade50,
          child: Column(
            children: [
              Padding(
                padding: EdgeInsets.fromLTRB(16.w, 8.h, 16.w, 0.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      children: [
                        Text(sectionTitle, style: MyTheme.homeText_heding()),
                      ],
                    ),
                    GestureDetector(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) {
                              return FlashDealList();
                            },
                          ),
                        );
                      },
                      child: Row(
                        children: [
                          Text(
                            AppLocalizations.of(context)!.see_all_ucf,
                            style: TextStyle(
                              fontSize: 10.sp,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          SizedBox(width: 3.w),
                          Icon(
                            Icons.arrow_forward_ios,
                            size: 12.sp,
                            color: MyTheme.font_grey,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              // Part 2: The Banner
              FlashDealBanner(homeData: homeData),
            ],
          ),
        ),
      ]),
    );
  }

  Widget _buildTopQuickActions(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: InkWell(
            borderRadius: BorderRadius.circular(18.r),
            onTap: () => _openProfileLinkedPage(const Address()),
            child: Container(
              height: 30.h,
              padding: EdgeInsets.symmetric(horizontal: 10.w),
              decoration: BoxDecoration(
                color: const Color(0xffFFF3EC),
                borderRadius: BorderRadius.circular(18.r),
                border: Border.all(
                  color: MyTheme.accent_color.withValues(alpha: .12),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.location_on_outlined,
                    color: MyTheme.accent_color,
                    size: 16.sp,
                  ),
                  SizedBox(width: 6.w),
                  Expanded(
                    child: Text(
                      _defaultAddressLabel,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: MyTheme.dark_font_grey,
                        fontSize: 11.sp,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        SizedBox(width: 8.w),
        _buildTopStatAction(
          icon: Icons.stars_rounded,
          label: 'Pts $_clubPointLabel',
          onTap: () => _openProfileLinkedPage(const Clubpoint()),
        ),
        SizedBox(width: 6.w),
        _buildTopIconAction(
          icon: Icons.chat_bubble_outline_rounded,
          onTap: () => _openProfileLinkedPage(const MessengerList()),
        ),
        SizedBox(width: 6.w),
        Consumer<UnReadNotificationCounter>(
          builder: (context, notification, child) {
            return _buildTopIconAction(
              icon: Icons.notifications_none_rounded,
              onTap: () => _openProfileLinkedPage(const NotificationList()),
              badgeCount: notification.unReadNotificationCounter,
            );
          },
        ),
      ],
    );
  }

  Widget _buildTopStatAction({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return InkWell(
      borderRadius: BorderRadius.circular(18.r),
      onTap: onTap,
      child: Container(
        height: 30.h,
        padding: EdgeInsets.symmetric(horizontal: 10.w),
        decoration: BoxDecoration(
          color: const Color(0xffFFF3EC),
          borderRadius: BorderRadius.circular(18.r),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: MyTheme.accent_color, size: 15.sp),
            SizedBox(width: 4.w),
            Text(
              label,
              style: TextStyle(
                color: MyTheme.dark_font_grey,
                fontSize: 11.sp,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTopIconAction({
    required IconData icon,
    required VoidCallback onTap,
    int badgeCount = 0,
  }) {
    return InkWell(
      borderRadius: BorderRadius.circular(18.r),
      onTap: onTap,
      child: Container(
        width: 30.h,
        height: 30.h,
        decoration: BoxDecoration(
          color: const Color(0xffF7F7FA),
          borderRadius: BorderRadius.circular(18.r),
          border: Border.all(color: const Color(0xffE8E9EE)),
        ),
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Center(
              child: Icon(icon, color: MyTheme.dark_font_grey, size: 16.sp),
            ),
            if (badgeCount > 0)
              Positioned(
                top: -3,
                right: -1,
                child: Container(
                  constraints: const BoxConstraints(minWidth: 14),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 3,
                    vertical: 1,
                  ),
                  decoration: BoxDecoration(
                    color: MyTheme.accent_color,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    badgeCount > 9 ? '9+' : '$badgeCount',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 8,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  SliverList _buildTodaysDealSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    return _buildProductShowcaseSection(
      context,
      title: "Today's Sale",
      leadingIcon: Icons.local_offer_outlined,
      products: homeData.todaysDealProductList,
      isLoading: homeData.isTodaysDealProductInitial,
      trailing: _buildTodaysDealCountdown(),
      onViewAll: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const TodaysDealProducts()),
        );
      },
    );
  }

  SliverToBoxAdapter _buildOnTheWayQrSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    final activePickupOrder = homeData.firstPickupReachedOrder;
    final activeOrder = activePickupOrder ?? homeData.firstOnTheWayOrder;
    if (activeOrder == null) {
      return const SliverToBoxAdapter(child: SizedBox.shrink());
    }

    final isPickupReached =
        activePickupOrder != null &&
        activeOrder.shippingType == 'pickup_point' &&
        activeOrder.deliveryStatus == 'reached';
    final qrUrl = isPickupReached
        ? (activeOrder.customerPickupQrImage ??
              _buildOrderQrUrl(activeOrder.customerPickupQrPayload, size: 180))
        : _buildOrderQrUrl(activeOrder.code, size: 180);
    const darkCard = Color(0xff283246);
    const darkCardAlt = Color(0xff313C52);
    const textMuted = Color(0xffB7C0CC);
    const textSoft = Color(0xffF7F8FA);
    const qrBorder = Color(0xffE7DDD3);
    final surface = MyTheme.mainColor;

    return SliverToBoxAdapter(
      child: Container(
        color: surface,
        padding: EdgeInsets.fromLTRB(0, 4.h, 16.w, 12.h),
        child: LayoutBuilder(
          builder: (context, constraints) {
            final maxWidth = constraints.maxWidth;
            final gap = (maxWidth * .022).clamp(6.0, 10.0);
            final cardHeight = (maxWidth * .20).clamp(72.0, 84.0);
            final qrCardWidth = (cardHeight * .92).clamp(62.0, 74.0);
            final qrVisibleWidth = qrCardWidth * .52;
            final actionCardWidth = (maxWidth * .22).clamp(82.0, 100.0);
            final leftSpacing = (qrVisibleWidth + gap).clamp(34.0, 48.0);
            final innerTileSize = (cardHeight * .48).clamp(36.0, 42.0);
            final titleSize = (cardHeight * .22).clamp(13.5, 17.0);
            final metaSize = (cardHeight * .145).clamp(10.0, 12.0);
            final codeSize = (cardHeight * .16).clamp(11.0, 13.0);
            final qrIconSize = (qrCardWidth * .34).clamp(24.0, 28.0);

            return SizedBox(
              height: cardHeight,
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  Padding(
                    padding: EdgeInsets.only(left: leftSpacing),
                    child: Row(
                      children: [
                        Expanded(
                          child: GestureDetector(
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) =>
                                      OrderDetails(id: activeOrder.id),
                                ),
                              );
                            },
                            child: Container(
                              height: cardHeight,
                              padding: EdgeInsets.symmetric(horizontal: 12.w),
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                  colors: [darkCardAlt, darkCard],
                                ),
                                borderRadius: BorderRadius.circular(22.r),
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(
                                      0xff283246,
                                    ).withValues(alpha: .12),
                                    blurRadius: 14,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: innerTileSize,
                                    height: innerTileSize,
                                    decoration: BoxDecoration(
                                      color: MyTheme.accent_color.withValues(
                                        alpha: .16,
                                      ),
                                      borderRadius: BorderRadius.circular(14.r),
                                    ),
                                    child: Icon(
                                      Icons.local_shipping_outlined,
                                      color: MyTheme.accent_color,
                                      size: (cardHeight * .25).clamp(
                                        18.0,
                                        22.0,
                                      ),
                                    ),
                                  ),
                                  SizedBox(width: 10.w),
                                  Expanded(
                                    child: Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          isPickupReached
                                              ? 'Pickup Ready'
                                              : 'On The Way',
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                            color: MyTheme.accent_color,
                                            fontSize: titleSize,
                                            fontWeight: FontWeight.w800,
                                            height: 1.0,
                                          ),
                                        ),
                                        SizedBox(height: 4.h),
                                        Text(
                                          isPickupReached
                                              ? _buildPickupWindowText(
                                                  activeOrder.pickupPoint,
                                                )
                                              : _displayText(activeOrder.date),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                            color: textMuted,
                                            fontSize: metaSize,
                                            fontWeight: FontWeight.w500,
                                            height: 1.0,
                                          ),
                                        ),
                                        SizedBox(height: 4.h),
                                        Text(
                                          isPickupReached
                                              ? _displayText(
                                                  activeOrder.pickupPoint?.name,
                                                  fallback: _displayText(
                                                    activeOrder.code,
                                                  ),
                                                )
                                              : _displayText(activeOrder.code),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                            color: textSoft,
                                            fontSize: codeSize,
                                            fontWeight: FontWeight.w700,
                                            height: 1.0,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        SizedBox(width: gap),
                        GestureDetector(
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const OrderList(),
                              ),
                            );
                          },
                          child: Container(
                            width: actionCardWidth,
                            height: cardHeight,
                            padding: EdgeInsets.symmetric(
                              horizontal: 8.w,
                              vertical: 10.h,
                            ),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                                colors: [darkCardAlt, darkCard],
                              ),
                              borderRadius: BorderRadius.circular(22.r),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(
                                    0xff283246,
                                  ).withValues(alpha: .10),
                                  blurRadius: 12,
                                  offset: const Offset(0, 6),
                                ),
                              ],
                            ),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.arrow_forward_rounded,
                                  color: textSoft,
                                  size: (cardHeight * .28).clamp(20.0, 26.0),
                                ),
                                SizedBox(height: 6.h),
                                Text(
                                  'All Orders',
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    color: textSoft,
                                    fontSize: metaSize,
                                    fontWeight: FontWeight.w600,
                                    height: 1.15,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Positioned(
                    left: -qrCardWidth * .48,
                    child: GestureDetector(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) =>
                                OrderDetails(id: activeOrder.id),
                          ),
                        );
                      },
                      child: Container(
                        width: qrCardWidth,
                        height: cardHeight,
                        padding: EdgeInsets.all(
                          (qrCardWidth * .15).clamp(8.0, 10.0),
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(22.r),
                          border: Border.all(color: qrBorder),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: .05),
                              blurRadius: 18,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: qrUrl == null
                            ? Center(
                                child: Icon(
                                  Icons.qr_code_scanner_rounded,
                                  size: qrIconSize,
                                  color: const Color(0xff212226),
                                ),
                              )
                            : ClipRRect(
                                borderRadius: BorderRadius.circular(14.r),
                                child: Image.network(
                                  qrUrl,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Center(
                                    child: Icon(
                                      Icons.qr_code_scanner_rounded,
                                      size: qrIconSize,
                                      color: const Color(0xff212226),
                                    ),
                                  ),
                                ),
                              ),
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  SliverList _buildFeaturedProductsSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    return _buildProductShowcaseSection(
      context,
      title: AppLocalizations.of(context)!.featured_products_ucf,
      leadingIcon: Icons.workspace_premium_outlined,
      products: homeData.featuredProductList,
      isLoading: homeData.isFeaturedProductInitial,
      onViewAll: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => const HomeSectionProducts(
              title: 'Featured Products',
              type: HomeSectionType.featured,
            ),
          ),
        );
      },
    );
  }

  SliverList _buildInlineBannerSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    return SliverList(
      delegate: SliverChildListDelegate([
        Container(
          color: const Color(0xffF2F1F6),
          child: HomeBannerThree(homeData: homeData),
        ),
      ]),
    );
  }

  SliverList _buildNewArrivalSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    return _buildProductShowcaseSection(
      context,
      title: AppLocalizations.of(context)!.new_arrival_ucf,
      leadingIcon: Icons.auto_awesome_outlined,
      products: homeData.newArrivalProductList,
      isLoading: homeData.isNewArrivalInitial,
      onViewAll: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => const HomeSectionProducts(
              title: 'New Arrival',
              type: HomeSectionType.newArrival,
            ),
          ),
        );
      },
    );
  }

  SliverList _buildProductShowcaseSection(
    BuildContext context, {
    required String title,
    required IconData leadingIcon,
    required List<Product> products,
    required bool isLoading,
    required VoidCallback onViewAll,
    Widget? trailing,
  }) {
    return SliverList(
      delegate: SliverChildListDelegate([
        Container(
          color: const Color(0xffF2F1F6),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: EdgeInsets.fromLTRB(16.w, 12.h, 16.w, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          _buildSectionTitle(title: title, icon: leadingIcon),
                          if (trailing != null) ...[
                            SizedBox(width: 10.w),
                            trailing,
                          ],
                        ],
                      ),
                    ),
                    SizedBox(width: 12.w),
                    GestureDetector(
                      onTap: onViewAll,
                      child: Row(
                        children: [
                          Text(
                            AppLocalizations.of(context)!.see_all_ucf,
                            style: TextStyle(
                              fontSize: 11.sp,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xff475467),
                            ),
                          ),
                          SizedBox(width: 4.w),
                          Icon(
                            Icons.arrow_forward_ios,
                            size: 12.sp,
                            color: const Color(0xff475467),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(16.w, 8.h, 0, 0),
                child: _buildProductStrip(products, isLoading),
              ),
            ],
          ),
        ),
      ]),
    );
  }

  Widget _buildTodaysDealCountdown() {
    final now = DateTime.now();
    final endOfDay = DateTime(now.year, now.month, now.day, 23, 59, 59);
    final endTime = endOfDay.millisecondsSinceEpoch;

    return CountdownTimer(
      endTime: endTime,
      widgetBuilder: (_, CurrentRemainingTime? time) {
        if (time == null) {
          return Container(
            padding: EdgeInsets.symmetric(horizontal: 10.w, vertical: 6.h),
            decoration: BoxDecoration(
              color: const Color(0xffFFE2E0),
              borderRadius: BorderRadius.circular(999),
            ),
            child: Text(
              AppLocalizations.of(context)!.ended_ucf,
              style: TextStyle(
                color: const Color(0xffC43228),
                fontSize: 11.sp,
                fontWeight: FontWeight.w700,
              ),
            ),
          );
        }

        final days = time.days ?? 0;
        final hours = time.hours ?? 0;
        final minutes = time.min ?? 0;
        final seconds = time.sec ?? 0;

        return Container(
          padding: EdgeInsets.symmetric(horizontal: 8.w, vertical: 5.h),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xffFF6A3D), Color(0xffFF8C42)],
            ),
            borderRadius: BorderRadius.circular(999),
            boxShadow: [
              BoxShadow(
                color: const Color(0xffFF6A3D).withValues(alpha: 0.22),
                blurRadius: 16,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.timer_outlined, size: 14.sp, color: Colors.white),
              SizedBox(width: 6.w),
              Text(
                '${_formatCountdownUnit(days)}:${_formatCountdownUnit(hours)}:${_formatCountdownUnit(minutes)}:${_formatCountdownUnit(seconds)}',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 10.sp,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  String _formatCountdownUnit(int value) {
    return value.toString().padLeft(2, '0');
  }

  String _displayText(String? value, {String fallback = '-'}) {
    final normalized = value?.trim();
    if (normalized == null || normalized.isEmpty) {
      return fallback;
    }
    return normalized;
  }

  String? _buildOrderQrUrl(String? code, {int size = 180}) {
    final normalized = code?.trim();
    if (normalized == null || normalized.isEmpty) {
      return null;
    }

    final encoded = Uri.encodeComponent(normalized);
    return "https://api.qrserver.com/v1/create-qr-code/?size=${size}x$size&data=$encoded&format=png&color=000000&bgcolor=ffffff";
  }

  String _buildPickupWindowText(dynamic pickupPoint) {
    final int? daysLeft = pickupPoint?.pickupWindowDaysLeft;
    final bool isReturnDue = pickupPoint?.isReturnDue == true;
    final String deadline = _displayText(
      pickupPoint?.pickupWindowDeadline,
      fallback: '',
    );

    if (isReturnDue) {
      return deadline.isEmpty ? 'Pickup window expired' : 'Expired on $deadline';
    }

    if (daysLeft != null) {
      if (daysLeft <= 0) {
        return deadline.isEmpty ? 'Pickup today' : 'Collect by $deadline';
      }
      return deadline.isEmpty
          ? '$daysLeft day${daysLeft > 1 ? 's' : ''} left'
          : '$daysLeft day${daysLeft > 1 ? 's' : ''} left · $deadline';
    }

    return deadline.isEmpty ? 'Ready for pickup' : 'Collect by $deadline';
  }

  Widget _buildProductStrip(List<Product> products, bool isLoading) {
    final cardWidth = ((MediaQuery.of(context).size.width - 44.w) / 2).clamp(
      140.w,
      190.w,
    );

    if (isLoading) {
      return SizedBox(
        height: 286.h,
        child: ListView.separated(
          padding: EdgeInsets.only(right: 16.w),
          scrollDirection: Axis.horizontal,
          physics: const BouncingScrollPhysics(
            parent: AlwaysScrollableScrollPhysics(),
          ),
          itemCount: 4,
          separatorBuilder: (context, index) => SizedBox(width: 12.w),
          itemBuilder: (context, index) {
            return ShimmerHelper().buildBasicShimmer(
              height: 286.h,
              width: cardWidth,
            );
          },
        ),
      );
    }

    if (products.isEmpty) {
      return SizedBox(
        height: 110.h,
        child: Center(
          child: Text(
            AppLocalizations.of(context)!.no_product_is_available,
            style: TextStyle(color: MyTheme.font_grey),
          ),
        ),
      );
    }

    return SizedBox(
      height: 286.h,
      child: ListView.separated(
        padding: EdgeInsets.only(right: 16.w),
        scrollDirection: Axis.horizontal,
        physics: const BouncingScrollPhysics(
          parent: AlwaysScrollableScrollPhysics(),
        ),
        itemCount: products.length,
        separatorBuilder: (context, index) => SizedBox(width: 12.w),
        itemBuilder: (context, index) {
          final product = products[index];
          return HomeProductCard(
            id: product.id,
            slug: product.slug ?? product.id.toString(),
            width: cardWidth,
            image: product.thumbnailImage,
            name: product.name,
            mainPrice: product.mainPrice,
            strokedPrice: product.strokedPrice,
            hasDiscount: product.hasDiscount,
            isWholesale: product.isWholesale,
            discount: product.discount,
            rating: product.rating ?? 0,
            reviewCount: product.reviewCount ?? 0,
          );
        },
      ),
    );
  }

  SliverList _buildAllProductsSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    return SliverList(
      delegate: SliverChildListDelegate([
        Container(
          color: const Color(0xffF2F1F6),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: EdgeInsets.fromLTRB(16.w, 5.0, 16.w, 0.0),
                child: _buildSectionTitle(
                  title: AppLocalizations.of(context)!.all_products_ucf,
                  icon: Icons.grid_view_rounded,
                ),
              ),
              HomeAllProducts2(homeData: homeData),
            ],
          ),
        ),
        SizedBox(height: 80.h),
      ]),
    );
  }

  Widget _buildSectionTitle({required String title, required IconData icon}) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 28.w,
          height: 28.w,
          decoration: BoxDecoration(
            color: MyTheme.accent_color.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(999),
          ),
          child: Icon(icon, size: 16.sp, color: MyTheme.accent_color),
        ),
        SizedBox(width: 8.w),
        Flexible(child: Text(title, style: MyTheme.homeText_heding())),
      ],
    );
  }

  SliverToBoxAdapter _buildProductLoadingContainer(
    BuildContext context,
    HomePresenter homeData,
  ) {
    final bool hasMoreProducts =
        homeData.totalAllProductData != homeData.allProductList.length;

    return SliverToBoxAdapter(
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        height: homeData.showAllLoadingContainer ? 36.h : 0,
        width: double.infinity,
        color: const Color(0xffF2F1F6),
        child: Center(
          child: Text(
            hasMoreProducts
                ? AppLocalizations.of(context)!.loading_more_products_ucf
                : AppLocalizations.of(context)!.no_more_products_ucf,
          ),
        ),
      ),
    );
  }
}

class _HomeMenu extends StatelessWidget {
  final HomePresenter homeData;

  const _HomeMenu({required this.homeData});

  @override
  Widget build(BuildContext context) {
    if (homeData.isCarouselInitial) {
      return Padding(
        padding: EdgeInsets.fromLTRB(14.w, 0, 14.w, 4.h),
        child: Container(
          padding: EdgeInsets.fromLTRB(8.w, 8.h, 8.w, 8.h),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(22.r),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.08),
                blurRadius: 18.r,
                offset: Offset(0, 8.h),
              ),
            ],
          ),
          child: SizedBox(
            height: 42.h,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              physics: const BouncingScrollPhysics(),
              padding: EdgeInsets.symmetric(horizontal: 8.w),
              child: Row(
                children: List.generate(5, (index) {
                  return Padding(
                    padding: EdgeInsets.only(right: index == 4 ? 0 : 12.w),
                    child: ShimmerHelper().buildBasicShimmer(
                      height: 42.h,
                      width: 112.w,
                      radius: 20.r,
                    ),
                  );
                }),
              ),
            ),
          ),
        ),
      );
    }

    final List<Map<String, dynamic>> menuItems = _getMenuItems(context);

    if (menuItems.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: EdgeInsets.fromLTRB(14.w, 0, 14.w, 4.h),
      child: Container(
        padding: EdgeInsets.fromLTRB(8.w, 8.h, 8.w, 8.h),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22.r),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.08),
              blurRadius: 18.r,
              offset: Offset(0, 8.h),
            ),
          ],
        ),
        child: SizedBox(
          height: 42.h,
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            padding: EdgeInsets.symmetric(horizontal: 8.w),
            child: Row(
              children: List.generate(menuItems.length, (index) {
                final item = menuItems[index];

                Color containerColor;
                Color textAndIconColor;
                BoxBorder? border;

                if (index == 0) {
                  containerColor = const Color(0xff0F172A);
                  textAndIconColor = Colors.white;
                  border = null;
                } else if (index == 1) {
                  containerColor = MyTheme.accent_color;
                  textAndIconColor = Colors.white;
                  border = null;
                } else {
                  containerColor = Colors.white;
                  textAndIconColor = const Color(0xff263140);
                  border = Border.all(color: const Color(0xffE6E8EC));
                }

                return Padding(
                  padding: EdgeInsets.only(
                    right: index == menuItems.length - 1 ? 0 : 12.w,
                  ),
                  child: GestureDetector(
                    onTap: item['onTap'],
                    child: Container(
                      padding: EdgeInsets.symmetric(
                        horizontal: 16.w,
                        vertical: 10.h,
                      ),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(20.r),
                        color: containerColor,
                        border: border,
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Image.asset(
                            item['image'],
                            color: textAndIconColor,
                            height: 16.w,
                            width: 16.w,
                          ),
                          SizedBox(width: 8.w),
                          Text(
                            item['title'],
                            style: TextStyle(
                              color: textAndIconColor,
                              fontWeight: FontWeight.w600,
                              fontSize: 10.5.sp,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }

  List<Map<String, dynamic>> _getMenuItems(BuildContext context) {
    return [
      if (homeData.isTodayDeal)
        {
          "title": "Today's Sale",
          "image": "assets/todays_deal.png",
          "icon": Icons.calendar_today_outlined,
          "onTap": () => Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const TodaysDealProducts()),
          ),
        },
      if (homeData.isFlashDeal)
        {
          "title": AppLocalizations.of(context)!.flash_deal_ucf,
          "image": "assets/flash_deal.png",
          "icon": Icons.bolt_rounded,
          "onTap": () => Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const FlashDealList()),
          ),
        },
      {
        "title": 'Top selling',
        "image": "assets/products.png",
        "icon": Icons.inventory_2_outlined,
        "onTap": () => Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => TopSellingProducts()),
        ),
      },
      if (vendor_system.$)
        {
          "title": AppLocalizations.of(context)!.top_sellers_ucf,
          "image": "assets/top_sellers.png",
          "icon": Icons.storefront_outlined,
          "onTap": () => Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const TopSellers()),
          ),
        },
    ];
  }
}

class StickyMenuDelegate extends SliverPersistentHeaderDelegate {
  final HomePresenter homeData;

  StickyMenuDelegate({required this.homeData});

  @override
  Widget build(
    BuildContext context,
    double shrinkOffset,
    bool overlapsContent,
  ) {
    return Container(
      color: Colors.transparent,
      alignment: Alignment.centerLeft,
      padding: EdgeInsets.fromLTRB(0, 8.h, 0, 6.h),
      child: _HomeMenu(homeData: homeData),
    );
  }

  @override
  double get maxExtent => 68.h;

  @override
  double get minExtent => 68.h;

  @override
  bool shouldRebuild(covariant StickyMenuDelegate oldDelegate) {
    return true;
  }
}
