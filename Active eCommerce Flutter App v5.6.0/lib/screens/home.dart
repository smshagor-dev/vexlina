import 'package:active_ecommerce_cms_demo_app/app_config.dart';
import 'package:active_ecommerce_cms_demo_app/custom/flash%20deals%20banner/flash_deal_banner.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shimmer_helper.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/home_presenter.dart';
import 'package:active_ecommerce_cms_demo_app/screens/filter.dart';
import 'package:active_ecommerce_cms_demo_app/screens/flash_deal/flash_deal_list.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/todays_deal_products.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/top_selling_products.dart';
import 'package:active_ecommerce_cms_demo_app/screens/top_sellers.dart';
import 'package:flutter/material.dart';
import '../custom/featured_product_horizontal_list_widget.dart';
import '../custom/home_all_products_2.dart';
import '../custom/home_banner_one.dart';
import '../custom/home_banner_two.dart';
import '../custom/home_carousel_slider.dart';
import '../custom/home_search_box.dart';
import '../custom/new_arrival_horizontal_list_widget.dart';
import '../custom/pirated_widget.dart';
import '../data_model/flash_deal_response.dart';
import '../single_banner/sincle_banner_page.dart';
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

  Future<void> _fetchData() {
    return homeData.onRefresh();
  }

  @override
  void dispose() {
    homeData.pirated_logo_controller.dispose();
    homeData.mainScrollController.dispose();
    super.dispose();
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
            body: Stack(
              children: [
                RefreshIndicator(
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
                        toolbarHeight: 50.h,
                        title: Padding(
                          padding: EdgeInsets.zero,
                          child: GestureDetector(
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (context) => const Filter(),
                                ),
                              );
                            },
                            child: HomeSearchBox(context: context),
                          ),
                        ),
                      ),

                      SliverList(
                        delegate: SliverChildListDelegate([
                          if (AppConfig.purchase_code == "")
                            PiratedWidget(homeData: homeData),
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
                      //Featured Products under sticky menu
                      ListenableBuilder(
                        listenable: homeData,
                        builder: (context, child) =>
                            _buildFeaturedProductsSection(context, homeData),
                      ),
                      //Second banner
                      SliverList(
                        delegate: SliverChildListDelegate([
                          SizedBox(height: 5.h),
                          ListenableBuilder(
                            listenable: homeData,
                            builder: (context, child) => HomeBannerOne(
                              context: context,
                              homeData: homeData,
                            ),
                          ),
                        ]),
                      ),
                      ListenableBuilder(
                        listenable: homeData,
                        builder: (context, child) =>
                            _buildNewArrivalSection(context, homeData),
                      ),
                      SliverList(
                        delegate: SliverChildListDelegate([
                          ListenableBuilder(
                            listenable: homeData,
                            builder: (context, child) => HomeBannerTwo(
                              context: context,
                              homeData: homeData,
                            ),
                          ),
                        ]),
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
                        delegate: SliverChildListDelegate.fixed([
                          PhotoWidget(),
                        ]),
                      ),
                      //All Products
                      ListenableBuilder(
                        listenable: homeData,
                        builder: (context, child) =>
                            _buildAllProductsSection(context, homeData),
                      ),
                    ],
                  ),
                ),
                Align(
                  alignment: Alignment.bottomCenter,
                  child: ListenableBuilder(
                    listenable: homeData,
                    builder: (context, child) =>
                        _buildProductLoadingContainer(context, homeData),
                  ),
                ),
              ],
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
                            'View all',
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

  SliverList _buildFeaturedProductsSection(
    BuildContext context,
    HomePresenter homeData,
  ) {
    return SliverList(
      delegate: SliverChildListDelegate([
        Container(
          color: Color(0xffF2F1F6),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: EdgeInsets.fromLTRB(16.w, 12.h, 0, 0),
                child: Text(
                  AppLocalizations.of(context)!.featured_products_ucf,
                  style: MyTheme.homeText_heding(),
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(16.w, 8.h, 0.w, 0),
                child: FeaturedProductHorizontalListWidget(homeData: homeData),
              ),
            ],
          ),
        ),
      ]),
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
                child: Text(
                  AppLocalizations.of(context)!.all_products_ucf,
                  style: MyTheme.homeText_heding(),
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

  SliverList _buildNewArrivalSection(
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
                padding: EdgeInsets.fromLTRB(16.w, 12.h, 0, 0),
                child: Text(
                  AppLocalizations.of(context)!.new_arrival_ucf,
                  style: MyTheme.homeText_heding(),
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(16.w, 8.h, 0, 0),
                child: NewArrivalHorizontalListWidget(homeData: homeData),
              ),
            ],
          ),
        ),
      ]),
    );
  }

  Widget _buildProductLoadingContainer(
    BuildContext context,
    HomePresenter homeData,
  ) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      height: homeData.showAllLoadingContainer ? 36.h : 0,
      width: double.infinity,
      color: Colors.white,
      child: Center(
        child: Text(
          homeData.totalAllProductData == homeData.allProductList.length
              ? AppLocalizations.of(context)!.no_more_products_ucf
              : AppLocalizations.of(context)!.loading_more_products_ucf,
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
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 6.w),
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: 5,
                itemBuilder: (context, index) {
                  return Padding(
                    padding: EdgeInsets.only(right: 10.w),
                    child: ShimmerHelper().buildBasicShimmer(
                      height: 42.h,
                      width: 112.w,
                      radius: 14.r,
                    ),
                  );
                },
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
          child: ListView.builder(
            padding: EdgeInsets.symmetric(horizontal: 6.w),
            scrollDirection: Axis.horizontal,
            itemCount: menuItems.length,
            itemBuilder: (context, index) {
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
                containerColor = const Color(0xffF7F8FA);
                textAndIconColor = const Color(0xff263140);
                border = Border.all(color: const Color(0xffE6E8EC));
              }

              return GestureDetector(
                onTap: item['onTap'],
                child: Container(
                  margin: EdgeInsets.only(right: 10.w),
                  padding: EdgeInsets.symmetric(horizontal: 16.w),
                  constraints: BoxConstraints(minWidth: 116.w),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(14.r),
                    color: containerColor,
                    border: border,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
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
              );
            },
          ),
        ),
      ),
    );
  }

  List<Map<String, dynamic>> _getMenuItems(BuildContext context) {
    return [
      if (homeData.isTodayDeal)
        {
          "title": AppLocalizations.of(context)!.todays_deal_ucf,
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
