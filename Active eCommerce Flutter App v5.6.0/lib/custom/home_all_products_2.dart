import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';

import '../helpers/shimmer_helper.dart';
import '../presenter/home_presenter.dart';
import '../ui_elements/home_product_card.dart';

class HomeAllProducts2 extends StatelessWidget {
  final HomePresenter homeData;

  const HomeAllProducts2({super.key, required this.homeData});

  @override
  Widget build(BuildContext context) {
    if (homeData.isAllProductInitial) {
      return SingleChildScrollView(
        child: ShimmerHelper().buildProductGridShimmer(
          scontroller: homeData.allProductScrollController,
        ),
      );
    } else if (homeData.allProductList.isNotEmpty) {
      return GridView.builder(
        itemCount: homeData.allProductList.length,
        shrinkWrap: true,
        cacheExtent: 500,
        padding: EdgeInsets.symmetric(horizontal: 16.w, vertical: 8.h),
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          mainAxisSpacing: 14.h,
          crossAxisSpacing: 14.w,
          childAspectRatio: 0.56,
        ),
        itemBuilder: (context, index) {
          final product = homeData.allProductList[index];
          return HomeProductCard(
            id: product.id,
            slug: product.slug ?? product.id.toString(),
            width: double.infinity,
            image: product.thumbnailImage,
            name: product.name,
            mainPrice: product.mainPrice,
            strokedPrice: product.strokedPrice,
            hasDiscount: product.hasDiscount ?? false,
            discount: product.discount,
            isWholesale: product.isWholesale,
            rating: product.rating ?? 0,
            reviewCount: product.reviewCount ?? 0,
          );
        },
      );
    } else if (homeData.totalAllProductData == 0) {
      return Center(
        child: Text(AppLocalizations.of(context)!.no_product_is_available),
      );
    } else {
      return const SizedBox.shrink();
    }
  }
}
