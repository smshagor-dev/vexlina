import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';

class PortalHelper {
  static bool get isPickupPointApp => user_type.$ == "pickup_point";
  static bool get isDeliveryBoyApp =>
      !isPickupPointApp && user_type.$ == "delivery_boy";
  static String get apiPrefix =>
      isPickupPointApp ? AppConfig.PICKUP_POINT_PREFIX : AppConfig.DELIVERY_PREFIX;

  static String get loginTitle =>
      isPickupPointApp ? "Pickup Point" : "Delivery";

  static String get completedLabel =>
      isPickupPointApp ? "Completed Orders" : "Completed Delivery";

  static String get pendingLabel =>
      isPickupPointApp ? "Active Orders" : "Pending Delivery";

  static String get returnedLabel =>
      isPickupPointApp ? "Return Orders" : "Cancelled Delivery";

  static String get upcomingLabel =>
      isPickupPointApp ? "Upcoming Orders" : "Assigned";

  static String get pickedLabel =>
      isPickupPointApp ? "Picked Up" : "Picked";

  static String get earningsLabel =>
      isPickupPointApp ? "My Earnings" : "My Earnings";

  static String get collectionOrReturnLabel =>
      isPickupPointApp ? "Return Orders" : "My Collection";

  static String get dashboardLabel => "Dashboard";

  static String get upcomingOrdersLabel =>
      isPickupPointApp ? "Upcoming Orders" : "Assigned";

  static String get pickedUpOrdersLabel =>
      isPickupPointApp ? "Picked Up Orders" : "Picked";

  static String get onTheWayOrdersLabel =>
      isPickupPointApp ? "On The Way Orders" : "On The Way";

  static String get reachedOrdersLabel => "Reached Orders";

  static String get returnOrdersLabel =>
      isPickupPointApp ? "Return Orders" : "Cancelled Delivery";
}
