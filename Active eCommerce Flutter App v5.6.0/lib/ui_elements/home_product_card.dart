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
      ToastComponent.showDialog(
        AppLocalizations.of(context)!.you_need_to_log_in,
      );
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
      final CartAddResponse response = await CartRepository()
          .getCartAddResponse(widget.id, "", user_id.$, 1);
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
    final discountLabel = _discountLabel();
    final showWalletOffer = wallet_system_status.$;
    final walletPrice = _walletPrice();

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => ProductDetails(slug: widget.slug),
          ),
        );
      },
      child: Container(
        width: widget.width ?? 152.w,
        padding: EdgeInsets.all(7.w),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16.r),
          border: Border.all(color: const Color(0xffE8ECF2)),
          boxShadow: [
            BoxShadow(
              color: const Color(0xff101828).withValues(alpha: 0.06),
              blurRadius: 20,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
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
                  top: 6.h,
                  right: 6.w,
                  child: _IconActionButton(
                    icon: _wishlistLoading
                        ? Icons.hourglass_top_rounded
                        : (_isWishlisted
                              ? Icons.favorite
                              : Icons.favorite_border),
                    onTap: _toggleWishlist,
                    foregroundColor: _isWishlisted
                        ? const Color(0xffD92D20)
                        : const Color(0xff475467),
                    backgroundColor: Colors.white,
                    borderColor: const Color(0xffE5E7EB),
                  ),
                ),
              ],
            ),
            SizedBox(height: 7.h),
            SizedBox(
              height: 28.h,
              child: Text(
                widget.name ?? '',
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 10.sp,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xff1F2937),
                  height: 1.2,
                ),
              ),
            ),
            SizedBox(height: 5.h),
            Container(
              padding: EdgeInsets.symmetric(horizontal: 6.w, vertical: 4.h),
              decoration: BoxDecoration(
                color: const Color(0xffF8FAFC),
                borderRadius: BorderRadius.circular(11.r),
                border: Border.all(color: const Color(0xffEAECF0)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Row(
                      children: [
                        Container(
                          height: 16.h,
                          width: 16.w,
                          decoration: BoxDecoration(
                            color: const Color(0xffFFF7E8),
                            borderRadius: BorderRadius.circular(999.r),
                          ),
                          child: Icon(
                            Icons.star_rounded,
                            color: const Color(0xffF59E0B),
                            size: 10.sp,
                          ),
                        ),
                        SizedBox(width: 4.w),
                        Expanded(
                          child: Text(
                            widget.rating.toStringAsFixed(1),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 8.5.sp,
                              fontWeight: FontWeight.w700,
                              color: const Color(0xff111827),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    height: 14.h,
                    width: 1,
                    color: const Color(0xffE5E7EB),
                  ),
                  SizedBox(width: 6.w),
                  Expanded(
                    child: Row(
                      children: [
                        Container(
                          height: 16.h,
                          width: 16.w,
                          decoration: BoxDecoration(
                            color: const Color(0xffEEF4FF),
                            borderRadius: BorderRadius.circular(999.r),
                          ),
                          child: Icon(
                            Icons.mode_comment_outlined,
                            color: const Color(0xff3538CD),
                            size: 9.sp,
                          ),
                        ),
                        SizedBox(width: 4.w),
                        Expanded(
                          child: Text(
                            '${widget.reviewCount}',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 8.5.sp,
                              fontWeight: FontWeight.w700,
                              color: const Color(0xff344054),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            SizedBox(height: 5.h),
            Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Expanded(
                  child: Text(
                    displayPrice,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 12.sp,
                      fontWeight: FontWeight.w800,
                      color: MyTheme.price_color,
                    ),
                  ),
                ),
                if (discountLabel != null)
                  Padding(
                    padding: EdgeInsets.only(right: 6.w),
                    child: Container(
                      padding: EdgeInsets.symmetric(
                        horizontal: 5.w,
                        vertical: 2.h,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xffFEF3F2),
                        borderRadius: BorderRadius.circular(999.r),
                      ),
                      child: Text(
                        discountLabel,
                        style: TextStyle(
                          color: const Color(0xffD92D20),
                          fontSize: 7.sp,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
                _IconActionButton(
                  icon: _cartLoading
                      ? Icons.hourglass_top_rounded
                      : Icons.shopping_cart_outlined,
                  onTap: _addToCart,
                  foregroundColor: Colors.white,
                  backgroundColor: const Color(0xffF97316),
                  borderColor: const Color(0xffF97316),
                ),
              ],
            ),
            if ((widget.hasDiscount ?? false) && displayStrokedPrice.isNotEmpty)
              Padding(
                padding: EdgeInsets.only(top: 1.h),
                child: Text(
                  displayStrokedPrice,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 8.sp,
                    color: const Color(0xff98A2B3),
                    decoration: TextDecoration.lineThrough,
                  ),
                ),
              ),
            if (showWalletOffer && walletPrice != null)
              Padding(
                padding: EdgeInsets.only(top: 2.h),
                child: Row(
                  children: [
                    Icon(
                      Icons.account_balance_wallet_outlined,
                      size: 11.sp,
                      color: const Color(0xff16A34A),
                    ),
                    SizedBox(width: 4.w),
                    Expanded(
                      child: Text(
                        'Wallet $walletPrice',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: const Color(0xff16A34A),
                          fontSize: 8.4.sp,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  String? _discountLabel() {
    final value = widget.discount?.toString().trim();
    if (value == null || value.isEmpty) {
      return null;
    }

    final numeric = value.replaceAll(RegExp(r'[^0-9.\-]'), '');
    final parsed = double.tryParse(numeric);
    if (parsed == null || parsed <= 0) {
      return null;
    }

    return value.toUpperCase().contains('%') ? value : '$value OFF';
  }

  String? _walletPrice() {
    final amount = _extractAmount(widget.mainPrice);
    if (amount == null || amount <= 0) {
      return null;
    }

    return _formatAmountWithCurrency(amount * 0.9);
  }

  double? _extractAmount(String? raw) {
    if (raw == null || raw.trim().isEmpty) {
      return null;
    }

    final sanitized = raw.replaceAll(RegExp(r'[^0-9.]'), '');
    if (sanitized.isEmpty) {
      return null;
    }

    return double.tryParse(sanitized);
  }

  String _formatAmountWithCurrency(double amount) {
    final symbol = SystemConfig.systemCurrency?.symbol ?? '';
    return '$symbol${amount.toStringAsFixed(2)}';
  }
}

class _IconActionButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final Color foregroundColor;
  final Color backgroundColor;
  final Color borderColor;

  const _IconActionButton({
    required this.icon,
    required this.onTap,
    required this.foregroundColor,
    required this.backgroundColor,
    required this.borderColor,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12.r),
      child: Container(
        height: 28.h,
        width: 28.w,
        decoration: BoxDecoration(
          color: backgroundColor,
          borderRadius: BorderRadius.circular(9.r),
          border: Border.all(color: borderColor),
        ),
        child: Icon(icon, size: 13.sp, color: foregroundColor),
      ),
    );
  }
}
