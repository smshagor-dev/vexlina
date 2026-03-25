import 'package:active_ecommerce_cms_demo_app/custom/toast_component.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/cart_add_response.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/l10n/app_localizations.dart';
import 'package:active_ecommerce_cms_demo_app/my_theme.dart';
import 'package:active_ecommerce_cms_demo_app/presenter/cart_counter.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/cart_repository.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/wishlist_repository.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/product_details/product_details.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:provider/provider.dart';

import '../helpers/system_config.dart';

class HomeProductCard extends StatefulWidget {
  final int? id;
  final String slug;
  final double? width;
  final String? image;
  final String? name;
  final String? mainPrice;
  final String? strokedPrice;
  final bool? hasDiscount;
  final bool? isWholesale;
  final dynamic discount;
  final double rating;
  final int reviewCount;

  const HomeProductCard({
    super.key,
    this.id,
    required this.slug,
    this.width,
    this.image,
    this.name,
    this.mainPrice,
    this.strokedPrice,
    this.hasDiscount,
    this.isWholesale = false,
    this.discount,
    this.rating = 0,
    this.reviewCount = 0,
  });

  @override
  State<HomeProductCard> createState() => _HomeProductCardState();
}

class _HomeProductCardState extends State<HomeProductCard> {
  bool _isWishlisted = false;
  bool _wishlistLoading = false;
  bool _cartLoading = false;

  @override
  void initState() {
    super.initState();
    _loadWishlistState();
  }

  Future<void> _loadWishlistState() async {
    if (!is_logged_in.$) return;
    try {
      final response = await WishListRepository().isProductInUserWishList(
        productSlug: widget.slug,
      );
      if (mounted) {
        setState(() {
          _isWishlisted = response.isInWishlist ?? false;
        });
      }
    } catch (_) {}
  }

  Future<void> _toggleWishlist() async {
    if (!is_logged_in.$) {
      ToastComponent.showDialog(AppLocalizations.of(context)!.you_need_to_log_in);
      return;
    }
    if (_wishlistLoading) return;

    setState(() {
      _wishlistLoading = true;
    });

    try {
      final response = _isWishlisted
          ? await WishListRepository().remove(productSlug: widget.slug)
          : await WishListRepository().add(productSlug: widget.slug);
      if (!mounted) return;
      setState(() {
        _isWishlisted = response.isInWishlist ?? !_isWishlisted;
      });
      ToastComponent.showDialog(response.message ?? "Updated successfully");
    } catch (_) {
      ToastComponent.showDialog("Something went wrong");
    } finally {
      if (mounted) {
        setState(() {
          _wishlistLoading = false;
        });
      }
    }
  }

  Future<void> _addToCart() async {
    if (_cartLoading) return;

    setState(() {
      _cartLoading = true;
    });

    try {
      final CartAddResponse response = await CartRepository().getCartAddResponse(
        widget.id,
        "",
        user_id.$,
        1,
      );
      temp_user_id.$ = response.tempUserId;
      if (mounted) {
        await context.read<CartCounter>().getCount();
      }
      ToastComponent.showDialog(response.message ?? "Added to cart");
    } catch (_) {
      ToastComponent.showDialog("Something went wrong");
    } finally {
      if (mounted) {
        setState(() {
          _cartLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final displayPrice = SystemConfig.systemCurrency != null
        ? widget.mainPrice?.replaceAll(
              SystemConfig.systemCurrency!.code!,
              SystemConfig.systemCurrency!.symbol!,
            ) ??
            ''
        : widget.mainPrice ?? '';

    final displayStrokedPrice = SystemConfig.systemCurrency != null
        ? widget.strokedPrice?.replaceAll(
              SystemConfig.systemCurrency!.code!,
              SystemConfig.systemCurrency!.symbol!,
            ) ??
            ''
        : widget.strokedPrice ?? '';

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => ProductDetails(slug: widget.slug)),
        );
      },
      child: Container(
        width: widget.width ?? 152.w,
        padding: EdgeInsets.all(10.w),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16.r),
          border: Border.all(color: const Color(0xffE8ECF2)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(12.r),
                        child: AspectRatio(
                          aspectRatio: 1,
                          child: FadeInImage.assetNetwork(
                            placeholder: 'assets/placeholder.png',
                            image: widget.image ?? 'assets/placeholder.png',
                            fit: BoxFit.cover,
                          ),
                        ),
                      ),
                      Positioned(
                        top: 8.h,
                        right: 8.w,
                        child: _ActionChip(
                          icon: _wishlistLoading
                              ? Icons.hourglass_top_rounded
                              : (_isWishlisted
                                    ? Icons.favorite
                                    : Icons.favorite_border),
                          onTap: _toggleWishlist,
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: 10.h),
                  SizedBox(
                    height: 36.h,
                    child: Text(
                      widget.name ?? '',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 12.sp,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xff1F2937),
                        height: 1.3,
                      ),
                    ),
                  ),
                  SizedBox(height: 6.h),
                  Row(
                    children: [
                      const Icon(
                        Icons.star_rounded,
                        color: Colors.amber,
                        size: 16,
                      ),
                      SizedBox(width: 4.w),
                      Text(
                        widget.rating.toStringAsFixed(1),
                        style: TextStyle(
                          fontSize: 11.sp,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xff1F2937),
                        ),
                      ),
                      const Spacer(),
                      Text(
                        '${widget.reviewCount}',
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 11.sp,
                          color: const Color(0xff667085),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: 4.h),
                  if ((widget.hasDiscount ?? false) &&
                      displayStrokedPrice.isNotEmpty)
                    Text(
                      displayStrokedPrice,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 11.sp,
                        color: const Color(0xff98A2B3),
                        decoration: TextDecoration.lineThrough,
                      ),
                    )
                  else
                    SizedBox(height: 14.h),
                  SizedBox(height: 2.h),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Expanded(
                        child: Text(
                          displayPrice,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: MyTheme.priceText(color: MyTheme.price_color),
                        ),
                      ),
                      _ActionChip(
                        icon: _cartLoading
                            ? Icons.hourglass_top_rounded
                            : Icons.shopping_cart_outlined,
                        onTap: _addToCart,
                        filled: true,
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ActionChip extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final bool filled;

  const _ActionChip({
    required this.icon,
    required this.onTap,
    this.filled = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(999.r),
      child: Container(
        height: 30.h,
        width: 30.w,
        decoration: BoxDecoration(
          color: filled ? MyTheme.accent_color : Colors.white,
          borderRadius: BorderRadius.circular(999.r),
          border: Border.all(
            color: filled ? MyTheme.accent_color : const Color(0xffE4E7EC),
          ),
        ),
        child: Icon(
          icon,
          size: 16.sp,
          color: filled ? Colors.white : const Color(0xff344054),
        ),
      ),
    );
  }
}
