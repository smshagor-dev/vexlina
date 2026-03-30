import 'dart:async';
import 'package:active_ecommerce_cms_demo_app/custom/toast_component.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/flash_deal_response.dart'
    hide Product;
import 'package:active_ecommerce_cms_demo_app/data_model/order_mini_response.dart'
    as order_mini;
import 'package:active_ecommerce_cms_demo_app/data_model/product_mini_response.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/category_response.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/slider_response.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/category_repository.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/flash_deal_repository.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/order_repository.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/product_repository.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/sliders_repository.dart';
import 'package:active_ecommerce_cms_demo_app/single_banner/model.dart';
import 'package:flutter/foundation.dart' hide Category;
import 'package:flutter/material.dart';

class HomePresenter extends ChangeNotifier {
  final GlobalKey<ScaffoldState> scaffoldKey = GlobalKey<ScaffoldState>();

  int current_slider = 0;

  ScrollController? allProductScrollController;
  ScrollController? featuredCategoryScrollController;
  ScrollController mainScrollController = ScrollController();

  late AnimationController pirated_logo_controller;
  late Animation<double> pirated_logo_animation;

  /// Slider & Banner
  List<AIZSlider> carouselImageList = [];
  List<AIZSlider> bannerOneImageList = [];
  List<AIZSlider> bannerTwoImageList = [];
  List<AIZSlider> bannerThreeImageList = [];
  List<AIZSlider> flashDealBannerImageList = [];

  /// Flash Deal
  List<FlashDealResponseDatum> flashDealList = [];

  List<FlashDealResponseDatum> _banners = [];
  List<FlashDealResponseDatum> get banners => [..._banners];

  final List<SingleBanner> _singleBanner = [];
  List<SingleBanner> get singleBanner => _singleBanner;

  /// Categories
  List<Category> featuredCategoryList = [];

  /// Products
  List<Product> todaysDealProductList = [];
  List<Product> featuredProductList = [];
  List<Product> newArrivalProductList = [];
  List<Product> allProductList = [];
  List<order_mini.Order> onTheWayOrderList = [];

  /// Flags
  bool isCategoryInitial = true;
  bool isCarouselInitial = true;
  bool isBannerOneInitial = true;
  bool isFlashDealInitial = true;
  bool isBannerTwoInitial = true;
  bool isBannerThreeInitial = true;
  bool isBannerFlashDeal = true;

  bool isFeaturedProductInitial = true;
  bool isTodaysDealProductInitial = true;
  bool isNewArrivalInitial = true;
  bool isAllProductInitial = true;

  bool isTodayDeal = false;
  bool isFlashDeal = false;

  /// Pagination
  int? totalFeaturedProductData = 0;
  int featuredProductPage = 1;
  bool showFeaturedLoadingContainer = false;

  int? totalNewArrivalProductData = 0;
  int newArrivalProductPage = 1;
  bool showNewArrivalLoadingContainer = false;

  int? totalAllProductData = 0;
  int allProductPage = 1;
  bool showAllLoadingContainer = false;

  int cartCount = 0;

  order_mini.Order? get firstOnTheWayOrder =>
      onTheWayOrderList.isNotEmpty ? onTheWayOrderList.first : null;

  /// ================= FETCH ALL =================

  fetchAll() {
    fetchCarouselImages();
    fetchBannerOneImages();
    fetchBannerTwoImages();
    fetchBannerThreeImages();
    fetchFeaturedCategories();
    fetchTodaysDealProducts();
    fetchOnTheWayOrders();
    fetchFeaturedProducts();
    fetchNewArrivalProducts();
    fetchAllProducts();
    fetchTodayDealData();
    fetchFlashDealData();
    fetchBannerFlashDeal();
    fetchFlashDealBannerImages();
  }

  /// ================= FLASH DEAL =================

  FlashDealResponseDatum? getFeaturedFlashDeal() {
    if (flashDealList.isEmpty) return null;
    try {
      return flashDealList.firstWhere((e) => e.isFeatured == 1);
    } catch (_) {
      return null;
    }
  }

  Future<void> fetchBannerFlashDeal() async {
    try {
      _banners = await SlidersRepository().fetchBanners();
      notifyListeners();
    } catch (e) {
      debugPrint('Error loading banners: $e');
    }
  }

  fetchFlashDealData() async {
    var deal = await FlashDealRepository().getFlashDeals();
    if (deal.success == true && deal.flashDeals!.isNotEmpty) {
      flashDealList = deal.flashDeals!;
      isFlashDeal = true;
    } else {
      isFlashDeal = false;
    }
    notifyListeners();
  }

  fetchTodayDealData() async {
    var deal = await ProductRepository().getTodaysDealProducts();
    isTodayDeal = deal.success == true && deal.products!.isNotEmpty;
    notifyListeners();
  }

  fetchTodaysDealProducts() async {
    try {
      var res = await ProductRepository().getTodaysDealProducts();
      todaysDealProductList = res.products ?? [];
      isTodaysDealProductInitial = false;
      notifyListeners();
    } catch (e) {
      debugPrint("Today's deal product error: $e");
    }
  }

  fetchOnTheWayOrders() async {
    if (!(is_logged_in.$) || (access_token.$?.isEmpty ?? true)) {
      onTheWayOrderList.clear();
      notifyListeners();
      return;
    }

    try {
      final res = await OrderRepository().getOrderList(
        page: 1,
        deliveryStatus: "on_the_way",
      );
      onTheWayOrderList = res.orders ?? [];
      notifyListeners();
    } catch (e) {
      debugPrint("On the way order error: $e");
      onTheWayOrderList.clear();
      notifyListeners();
    }
  }

  /// ================= SLIDERS =================

  fetchCarouselImages() async {
    var res = await SlidersRepository().getSliders();
    carouselImageList = res.sliders ?? [];
    isCarouselInitial = false;
    notifyListeners();
  }

  fetchBannerOneImages() async {
    var res = await SlidersRepository().getBannerOneImages();
    bannerOneImageList = res.sliders ?? [];
    isBannerOneInitial = false;
    notifyListeners();
  }

  fetchBannerTwoImages() async {
    var res = await SlidersRepository().getBannerTwoImages();
    bannerTwoImageList = res.sliders ?? [];
    isBannerTwoInitial = false;
    notifyListeners();
  }

  fetchBannerThreeImages() async {
    var res = await SlidersRepository().getBannerThreeImages();
    bannerThreeImageList = res.sliders ?? [];
    isBannerThreeInitial = false;
    notifyListeners();
  }

  fetchFlashDealBannerImages() async {
    var res = await SlidersRepository().getFlashDealBanner();
    flashDealBannerImageList = res.sliders ?? [];
    isFlashDealInitial = false;
    notifyListeners();
  }

  /// ================= CATEGORY =================

  fetchFeaturedCategories() async {
    var res = await CategoryRepository().getFeturedCategories();
    featuredCategoryList = res.categories ?? [];
    isCategoryInitial = false;
    notifyListeners();
  }

  /// ================= PRODUCTS =================

  fetchFeaturedProducts() async {
    try {
      var res = await ProductRepository().getFeaturedProducts(
        page: featuredProductPage,
      );
      featuredProductPage++;

      if (res.products != null) {
        featuredProductList.addAll(res.products!);
      }

      totalFeaturedProductData = res.meta?.total ?? 0;
      isFeaturedProductInitial = false;
      showFeaturedLoadingContainer = false;
      notifyListeners();
    } catch (e) {
      debugPrint("Featured product error: $e");
    }
  }

  fetchAllProducts() async {
    var res = await ProductRepository().getFilteredProducts(
      page: allProductPage,
    );

    if (res.products != null) {
      allProductList.addAll(res.products!);
    }

    totalAllProductData = res.meta?.total ?? 0;
    isAllProductInitial = false;
    showAllLoadingContainer = false;
    notifyListeners();
  }

  fetchNewArrivalProducts() async {
    try {
      var res = await ProductRepository().getFilteredProducts(
        page: newArrivalProductPage,
        sortKey: "newest",
      );
      newArrivalProductPage++;

      if (res.products != null) {
        newArrivalProductList.addAll(res.products!);
      }

      totalNewArrivalProductData = res.meta?.total ?? 0;
      isNewArrivalInitial = false;
      showNewArrivalLoadingContainer = false;
      notifyListeners();
    } catch (e) {
      debugPrint("New arrival product error: $e");
    }
  }

  /// ================= RESET =================

  reset() {
    carouselImageList.clear();
    bannerOneImageList.clear();
    bannerTwoImageList.clear();
    bannerThreeImageList.clear();
    featuredCategoryList.clear();
    flashDealList.clear();
    flashDealBannerImageList.clear();
    todaysDealProductList.clear();
    onTheWayOrderList.clear();

    isCarouselInitial = true;
    isBannerOneInitial = true;
    isBannerTwoInitial = true;
    isBannerThreeInitial = true;
    isCategoryInitial = true;
    isTodaysDealProductInitial = true;

    resetFeaturedProductList();
    resetNewArrivalProductList();
    resetAllProductList();
  }

  resetFeaturedProductList() {
    featuredProductList.clear();
    isFeaturedProductInitial = true;
    totalFeaturedProductData = 0;
    featuredProductPage = 1;
    showFeaturedLoadingContainer = false;
    notifyListeners();
  }

  resetAllProductList() {
    allProductList.clear();
    isAllProductInitial = true;
    totalAllProductData = 0;
    allProductPage = 1;
    showAllLoadingContainer = false;
    notifyListeners();
  }

  resetNewArrivalProductList() {
    newArrivalProductList.clear();
    isNewArrivalInitial = true;
    totalNewArrivalProductData = 0;
    newArrivalProductPage = 1;
    showNewArrivalLoadingContainer = false;
    notifyListeners();
  }

  /// ================= SCROLL =================

  mainScrollListener() {
    mainScrollController.addListener(() {
      final hasMoreProducts = totalAllProductData != allProductList.length;
      final reachedBottom =
          mainScrollController.position.pixels >=
          (mainScrollController.position.maxScrollExtent - 24);

      if (reachedBottom && hasMoreProducts && !showAllLoadingContainer) {
        allProductPage++;
        ToastComponent.showDialog("More Products Loading...");
        showAllLoadingContainer = true;
        notifyListeners();
        fetchAllProducts();
      }
    });
  }

  /// ================= ANIMATION =================

  initPiratedAnimation(vnc) {
    pirated_logo_controller = AnimationController(
      vsync: vnc,
      duration: Duration(milliseconds: 2000),
    );

    pirated_logo_animation = Tween<double>(begin: 40, end: 60).animate(
      CurvedAnimation(parent: pirated_logo_controller, curve: Curves.bounceOut),
    );

    pirated_logo_controller.repeat();
  }

  incrementCurrentSlider(index) {
    current_slider = index;
    notifyListeners();
  }

  @override
  void dispose() {
    pirated_logo_controller.dispose();
    super.dispose();
  }

  Future<void> onRefresh() async {
    reset();
    fetchAll();
  }
}
