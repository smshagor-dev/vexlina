import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_spinkit/flutter_spinkit.dart';

import '../helpers/shimmer_helper.dart';
import '../my_theme.dart';
import '../presenter/home_presenter.dart';
import '../ui_elements/mini_product_card.dart';

class NewArrivalHorizontalListWidget extends StatelessWidget {
  final HomePresenter homeData;

  const NewArrivalHorizontalListWidget({super.key, required this.homeData});

  @override
  Widget build(BuildContext context) {
    if (homeData.isNewArrivalInitial && homeData.newArrivalProductList.isEmpty) {
      return Row(
        children: [
          Expanded(
            child: Padding(
              padding: EdgeInsets.all(16.r),
              child: ShimmerHelper().buildBasicShimmer(
                height: 120.h,
                width: (1.sw - 64.w) / 3,
              ),
            ),
          ),
          Expanded(
            child: Padding(
              padding: EdgeInsets.all(16.r),
              child: ShimmerHelper().buildBasicShimmer(
                height: 120.h,
                width: (1.sw - 64.w) / 3,
              ),
            ),
          ),
          Expanded(
            child: Padding(
              padding: EdgeInsets.all(16.r),
              child: ShimmerHelper().buildBasicShimmer(
                height: 120.h,
                width: (1.sw - 160.w) / 3,
              ),
            ),
          ),
        ],
      );
    } else if (homeData.newArrivalProductList.isNotEmpty) {
      return SizedBox(
        height: 210.h,
        child: NotificationListener<ScrollNotification>(
          onNotification: (scrollInfo) {
            if (scrollInfo.metrics.pixels == scrollInfo.metrics.maxScrollExtent) {
              homeData.fetchNewArrivalProducts();
            }
            return true;
          },
          child: ListView.separated(
            padding: EdgeInsets.zero,
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(
              parent: AlwaysScrollableScrollPhysics(),
            ),
            separatorBuilder: (context, index) => SizedBox(width: 12.w),
            itemCount: homeData.totalNewArrivalProductData! >
                    homeData.newArrivalProductList.length
                ? homeData.newArrivalProductList.length + 1
                : homeData.newArrivalProductList.length,
            itemBuilder: (context, index) {
              if (index == homeData.newArrivalProductList.length) {
                return SpinKitFadingFour(
                  itemBuilder: (context, index) {
                    return const DecoratedBox(
                      decoration: BoxDecoration(color: Colors.white),
                    );
                  },
                );
              }

              final product = homeData.newArrivalProductList[index];
              return MiniProductCard(
                id: product.id,
                slug: product.slug ?? product.id.toString(),
                image: product.thumbnailImage,
                name: product.name,
                mainPrice: product.mainPrice,
                strokedPrice: product.strokedPrice,
                hasDiscount: product.hasDiscount,
                isWholesale: product.isWholesale,
                discount: product.discount,
              );
            },
          ),
        ),
      );
    } else {
      return SizedBox(
        height: 100.h,
        child: Center(
          child: Text(
            AppLocalizations.of(context)!.no_new_arrivals,
            style: TextStyle(color: MyTheme.font_grey),
          ),
        ),
      );
    }
  }
}
