import 'package:active_ecommerce_cms_demo_app/data_model/product_mini_response.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shimmer_helper.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/product_repository.dart';
import 'package:active_ecommerce_cms_demo_app/ui_elements/product_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_staggered_grid_view/flutter_staggered_grid_view.dart';

enum HomeSectionType { featured, newArrival }

class HomeSectionProducts extends StatefulWidget {
  final String title;
  final HomeSectionType type;

  const HomeSectionProducts({
    super.key,
    required this.title,
    required this.type,
  });

  @override
  State<HomeSectionProducts> createState() => _HomeSectionProductsState();
}

class _HomeSectionProductsState extends State<HomeSectionProducts> {
  final List<Product> _products = [];
  final ScrollController _scrollController = ScrollController();
  int _page = 1;
  int _total = 0;
  bool _isInitialLoading = true;
  bool _isLoadingMore = false;

  @override
  void initState() {
    super.initState();
    _fetchProducts();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchProducts({bool reset = false}) async {
    if (_isLoadingMore) return;
    if (reset) {
      _page = 1;
      _products.clear();
      _total = 0;
    }

    setState(() {
      if (reset || _products.isEmpty) {
        _isInitialLoading = true;
      } else {
        _isLoadingMore = true;
      }
    });

    final response = widget.type == HomeSectionType.featured
        ? await ProductRepository().getFeaturedProducts(page: _page)
        : await ProductRepository().getFilteredProducts(
            page: _page,
            sortKey: "newest",
          );

    if (!mounted) return;

    setState(() {
      _products.addAll(response.products ?? []);
      _total = response.meta?.total ?? _products.length;
      _page++;
      _isInitialLoading = false;
      _isLoadingMore = false;
    });
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
            _scrollController.position.maxScrollExtent - 120 &&
        !_isLoadingMore &&
        _products.length < _total) {
      _fetchProducts();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: MyTheme.mainColor,
      appBar: AppBar(
        backgroundColor: MyTheme.mainColor,
        elevation: 0,
        scrolledUnderElevation: 0,
        titleSpacing: 0,
        title: Text(
          widget.title,
          style: TextStyle(
            fontSize: 16,
            color: MyTheme.dark_font_grey,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () => _fetchProducts(reset: true),
        child: _isInitialLoading
            ? SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: ShimmerHelper().buildProductGridShimmer(),
              )
            : MasonryGridView.count(
                controller: _scrollController,
                physics: const AlwaysScrollableScrollPhysics(),
                crossAxisCount: 2,
                mainAxisSpacing: 14,
                crossAxisSpacing: 14,
                itemCount: _products.length + (_isLoadingMore ? 2 : 0),
                padding: const EdgeInsets.only(
                  top: 20,
                  bottom: 16,
                  left: 18,
                  right: 18,
                ),
                itemBuilder: (context, index) {
                  if (index >= _products.length) {
                    return ShimmerHelper().buildBasicShimmer(height: 220, width: 160);
                  }

                  final product = _products[index];
                  return ProductCard(
                    id: product.id,
                    slug: product.slug ?? product.id.toString(),
                    image: product.thumbnailImage,
                    name: product.name,
                    mainPrice: product.mainPrice,
                    strokedPrice: product.strokedPrice,
                    hasDiscount: product.hasDiscount ?? false,
                    discount: product.discount?.toString(),
                    isWholesale: product.isWholesale,
                    rating: product.rating,
                    ratingCount: product.reviewCount,
                  );
                },
              ),
      ),
    );
  }
}
