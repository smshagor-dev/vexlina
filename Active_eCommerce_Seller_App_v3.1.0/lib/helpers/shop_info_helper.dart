import 'package:active_ecommerce_seller_app/data_model/shop_info_response.dart';
import 'package:active_ecommerce_seller_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_seller_app/repositories/shop_repository.dart';

class ShopInfoHelper {
  setShopInfo() async {
    var shopInfo = await ShopRepository().getShopInfo();

    shop_name.$ = shopInfo.shopInfo!.name! ?? '';
    shop_name.save();

    shop_logo.$ = shopInfo.shopInfo!.logo!;
    shop_logo.save();

    seller_email.$ = shopInfo.shopInfo!.email!;
    seller_email.save();

    shop_rating.$ = shopInfo.shopInfo!.rating.toString();
    shop_rating.save();

    shop_verify.$ = shopInfo.shopInfo!.verified!;
    shop_verify.save();

    verify_form_submitted.$ = shopInfo.shopInfo!.is_submitted_form!;
    verify_form_submitted.save();
  }

  static loadShopInfo() {
    shop_name.load();

    shop_logo.load();

    seller_email.load();

    shop_rating.load();

    shop_verify.load();
  }
}
