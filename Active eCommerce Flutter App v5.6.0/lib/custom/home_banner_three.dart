import 'package:active_ecommerce_cms_demo_app/app_config.dart';
import 'package:active_ecommerce_cms_demo_app/custom/aiz_image.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shimmer_helper.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/home_presenter.dart';
import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:go_router/go_router.dart';

class HomeBannerThree extends StatelessWidget {
  final HomePresenter homeData;

  const HomeBannerThree({super.key, required this.homeData});

  @override
  Widget build(BuildContext context) {
    if (homeData.isBannerThreeInitial && homeData.bannerThreeImageList.isEmpty) {
      return Padding(
        padding: EdgeInsets.fromLTRB(16.w, 12.h, 16.w, 0),
        child: ShimmerHelper().buildBasicShimmer(height: 120.h),
      );
    }

    if (homeData.bannerThreeImageList.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: EdgeInsets.fromLTRB(12.w, 12.h, 12.w, 0),
      child: CarouselSlider(
        options: CarouselOptions(
          height: 132.h,
          viewportFraction: 1,
          enableInfiniteScroll: homeData.bannerThreeImageList.length > 1,
          autoPlay: homeData.bannerThreeImageList.length > 1,
        ),
        items: homeData.bannerThreeImageList.map((bannerItem) {
          return Builder(
            builder: (context) {
              return Padding(
                padding: EdgeInsets.symmetric(horizontal: 4.w),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(12.r),
                  child: InkWell(
                    onTap: () {
                      final url =
                          bannerItem.url?.split(AppConfig.DOMAIN_PATH).last ??
                          "";
                      if (url.isNotEmpty) {
                        GoRouter.of(context).go(url);
                      }
                    },
                    child: SizedBox(
                      width: double.infinity,
                      child: AIZImage.radiusImage(bannerItem.photo, 12.r),
                    ),
                  ),
                ),
              );
            },
          );
        }).toList(),
      ),
    );
  }
}
