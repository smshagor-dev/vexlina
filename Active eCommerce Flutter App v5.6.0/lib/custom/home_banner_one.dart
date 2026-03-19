import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart'; // Import ScreenUtil
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:go_router/go_router.dart';

import '../helpers/shimmer_helper.dart';
import '../my_theme.dart';
import '../presenter/home_presenter.dart';
import 'aiz_image.dart';

class HomeBannerOne extends StatelessWidget {
  final HomePresenter? homeData;
  final BuildContext? context;

  const HomeBannerOne({super.key, this.homeData, this.context});

  @override
  Widget build(BuildContext context) {
    if (homeData == null) {
      return SizedBox(
        height: 100.h,
        child: const Center(child: Text('No data available')),
      );
    }

    if (homeData!.isBannerOneInitial && homeData!.bannerOneImageList.isEmpty) {
      return Padding(
        padding: EdgeInsets.only(
          left: 18.w,
          right: 18.w,
          top: 10.h,
          bottom: 20.h,
        ),
        child: ShimmerHelper().buildBasicShimmer(height: 170.h),
      );
    } else if (homeData!.bannerOneImageList.isNotEmpty) {
      return CarouselSlider(
        options: CarouselOptions(
          height: 190.h,
          aspectRatio: 16 / 9,
          viewportFraction: 1.0,
          initialPage: 0,
          padEnds: true,
          enableInfiniteScroll: true,
          autoPlay: true,
          onPageChanged: (index, reason) {},
        ),
        items: homeData!.bannerOneImageList.map((bannerItem) {
          return Builder(
            builder: (BuildContext context) {
              return Padding(
                padding: EdgeInsets.only(
                  left: 12.w,
                  right: 12.w,
                  top: 0,
                  bottom: 10.h,
                ),
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10.r),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xff000000).withValues(alpha: 0.1),
                        spreadRadius: 2.r,
                        blurRadius: 5.r,
                        offset: Offset(0, 3.h),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(10.r),
                    child: InkWell(
                      onTap: () {
                        final String? fullUrl = bannerItem.url;

                        if (fullUrl == null || fullUrl.isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('No link available')),
                          );
                          return;
                        }

                        try {
                          final Uri uri = Uri.parse(fullUrl);
                          if (uri.pathSegments.isNotEmpty) {
                            final String slug = uri.pathSegments.last;
                            if (uri.path.contains('/category/')) {
                              GoRouter.of(context).push('/category/$slug');
                            } else if (uri.path.contains('/product/')) {
                              GoRouter.of(context).push('/product/$slug');
                            } else if (uri.path.contains('/brand/')) {
                              GoRouter.of(context).push('/brand/$slug');
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text('Unknown link type'),
                                ),
                              );
                            }
                          }
                        } catch (e) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('Invalid link: $e')),
                          );
                        }
                      },
                      child: SizedBox(
                        width: double.infinity,
                        child: AIZImage.radiusImage(bannerItem.photo, 10.r),
                      ),
                    ),
                  ),
                ),
              );
            },
          );
        }).toList(),
      );
    } else if (!homeData!.isBannerOneInitial &&
        homeData!.bannerOneImageList.isEmpty) {
      return SizedBox(
        height: 100.h,
        child: Center(
          child: Text(
            AppLocalizations.of(context)!.no_carousel_image_found,
            style: TextStyle(color: MyTheme.font_grey, fontSize: 12.sp),
          ),
        ),
      );
    } else {
      return Container(height: 100.h);
    }
  }
}
