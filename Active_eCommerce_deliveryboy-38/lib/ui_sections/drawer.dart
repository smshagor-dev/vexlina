import 'package:active_flutter_delivery_app/custom/lang_text.dart';
import 'package:active_flutter_delivery_app/custom/toast_component.dart';
import 'package:active_flutter_delivery_app/helpers/auth_helper.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/auth_repository.dart';
import 'package:active_flutter_delivery_app/screens/assigned_delivery.dart';
import 'package:active_flutter_delivery_app/screens/cancelled_delivery.dart';
import 'package:active_flutter_delivery_app/screens/change_language.dart';
import 'package:active_flutter_delivery_app/screens/completed_delivery.dart';
import 'package:active_flutter_delivery_app/screens/earnings.dart';
import 'package:active_flutter_delivery_app/screens/login.dart';
import 'package:active_flutter_delivery_app/screens/main.dart';
import 'package:active_flutter_delivery_app/screens/on_the_way_delivery.dart';
import 'package:active_flutter_delivery_app/screens/pending.dart';
import 'package:active_flutter_delivery_app/screens/picked_delivery.dart';
import 'package:active_flutter_delivery_app/screens/reached_delivery.dart';
import 'package:flutter/material.dart';
import 'package:route_transitions/route_transitions.dart';

class MainDrawer extends StatefulWidget {
  const MainDrawer({
    Key? key,
  }) : super(key: key);

  @override
  _MainDrawerState createState() => _MainDrawerState();
}

class _MainDrawerState extends State<MainDrawer> {
  late BuildContext loadingcontext;

  onTapLogout(context) async {
    AuthHelper().clearUserData();

    /*
    var logoutResponse = await AuthRepository()
            .getLogoutResponse();


    if(logoutResponse.result == true){
         ToastComponent.showDialog(logoutResponse.message, context,
                   gravity: Toast.center, duration: Toast.lengthLong);
         }
         */
    Navigator.push(context, MaterialPageRoute(builder: (context) {
      return Login();
    }));
  }

  deleteWarningDialog() {
    return showDialog(
        context: context,
        builder: (context) => AlertDialog(
              title: Text(
                LangText(context).local!.delete_account_warning_title,
                style: TextStyle(fontSize: 15, color: MyTheme.dark_grey),
              ),
              content: Text(
                LangText(context).local!.delete_account_warning_description,
                style: TextStyle(fontSize: 13, color: MyTheme.dark_grey),
              ),
              actions: [
                TextButton(
                    onPressed: () {
                      pop(context);
                    },
                    child: Text(LangText(context).local!.no_ucf)),
                TextButton(
                    onPressed: () {
                      pop(context);
                      deleteAccountReq();
                    },
                    child: Text(LangText(context).local!.yes_ucf))
              ],
            ));
  }

  deleteAccountReq() async {
    loading();
    var response = await AuthRepository().getAccountDeleteResponse();

    if (response.result!) {
      AuthHelper().clearUserData();
      Navigator.pop(loadingcontext);
      Navigator.pushAndRemoveUntil(context,
          MaterialPageRoute(builder: (context) {
        return Main();
      }), (route) => false);
    }
    ToastComponent.showDialog(response.message!, context);
  }

  loading() {
    showDialog(
        context: context,
        builder: (context) {
          loadingcontext = context;
          return AlertDialog(
              content: Row(
            children: [
              CircularProgressIndicator(),
              SizedBox(
                width: 10,
              ),
              Text("${LangText(context).local!.please_wait_ucf}"),
            ],
          ));
        });
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Container(
        padding: EdgeInsets.only(top: 50),
        child: SingleChildScrollView(
          child: Column(
            children: <Widget>[
              is_logged_in.$ == true
                  ? ListTile(
                      leading: CircleAvatar(
                        backgroundImage: NetworkImage(
                          "${avatar_original.$}",
                        ),
                      ),
                      title: Text("${user_name.$}"),
                      subtitle: PortalHelper.isPickupPointApp
                          ? Text("Pickup Point Manager")
                          : user_email.$ != "" && user_email.$ != null
                              ? Text("${user_email.$}")
                              : Text("${user_phone.$}"))
                  : Text(LangText(context).local!.not_logged_in_ucf,
                      style: TextStyle(
                          color: Color.fromRGBO(153, 153, 153, 1),
                          fontSize: 14)),
              Divider(),
              ListTile(
                  visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                  leading: Image.asset("assets/language.png",
                      height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                  title: Text(LangText(context).local!.change_language_ucf,
                      style: TextStyle(
                          color: Color.fromRGBO(153, 153, 153, 1),
                          fontSize: 14)),
                  onTap: () {
                    Navigator.push(context,
                        MaterialPageRoute(builder: (context) {
                      return ChangeLanguage();
                    }));
                  }),
              ListTile(
                  visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                  leading: Image.asset("assets/dashboard.png",
                      height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                  title: Text(PortalHelper.dashboardLabel,
                      style: TextStyle(
                          color: Color.fromRGBO(153, 153, 153, 1),
                          fontSize: 14)),
                  onTap: () {
                    Navigator.push(context,
                        MaterialPageRoute(builder: (context) {
                      return Main();
                    }));

                    // pop(context);
                    // slideRightWidget(
                    //   newPage: Main(),
                    //   context: context,
                    // );
                  }),
              is_logged_in.$ == true && !PortalHelper.isPickupPointApp
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/tick_circle.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text(
                          PortalHelper.completedLabel,
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        Navigator.push(context,
                            MaterialPageRoute(builder: (context) {
                          return CompletedDelivery(show_back_button: true);
                        }));
                        // pop(context);
                        // slideRightWidget(
                        //   newPage: CompletedDelivery(show_back_button: true),
                        //   context: context,
                        // );
                      })
                  : Container(),
              is_logged_in.$ == true && !PortalHelper.isPickupPointApp
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/clock_circle.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text(PortalHelper.pendingLabel,
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        Navigator.push(context,
                            MaterialPageRoute(builder: (context) {
                          return Pending();
                        }));

                        // pop(context);
                        // slideRightWidget(
                        //   newPage: Pending(),
                        //   context: context,
                        // );
                      })
                  : Container(),
              is_logged_in.$ == true && !PortalHelper.isPickupPointApp
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/cross_circle.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text(
                          PortalHelper.returnedLabel,
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        Navigator.push(context,
                            MaterialPageRoute(builder: (context) {
                          return CancelledDelivery(show_back_button: true);
                        }));

                        // pop(context);
                        // slideRightWidget(
                        //   newPage: CancelledDelivery(show_back_button: true),
                        //   context: context,
                        // );
                      })
                  : Container(),
              is_logged_in.$ == true && !PortalHelper.isPickupPointApp
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/dollar_circle.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text(PortalHelper.earningsLabel,
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        Navigator.push(context,
                            MaterialPageRoute(builder: (context) {
                          return Earnings(show_back_button: true);
                        }));
                        // pop(context);
                        // slideRightWidget(
                        //   newPage: Collection(show_back_button: true),
                        //   context: context,
                        // );
                      })
                  : Container(),
              if (is_logged_in.$ == true && PortalHelper.isPickupPointApp)
                ...[
                  ListTile(
                    visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                    leading: Icon(Icons.inventory_2_outlined,
                        color: Color.fromRGBO(153, 153, 153, 1), size: 18),
                    title: Text(PortalHelper.upcomingOrdersLabel,
                        style: TextStyle(
                            color: Color.fromRGBO(153, 153, 153, 1),
                            fontSize: 14)),
                    onTap: () {
                      Navigator.push(context,
                          MaterialPageRoute(builder: (context) {
                        return AssignedDelivery(show_back_button: true);
                      }));
                    },
                  ),
                  ListTile(
                    visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                    leading: Icon(Icons.pan_tool_alt_outlined,
                        color: Color.fromRGBO(153, 153, 153, 1), size: 18),
                    title: Text(PortalHelper.pickedUpOrdersLabel,
                        style: TextStyle(
                            color: Color.fromRGBO(153, 153, 153, 1),
                            fontSize: 14)),
                    onTap: () {
                      Navigator.push(context,
                          MaterialPageRoute(builder: (context) {
                        return PickedDelivery(show_back_button: true);
                      }));
                    },
                  ),
                  ListTile(
                    visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                    leading: Icon(Icons.local_shipping_outlined,
                        color: Color.fromRGBO(153, 153, 153, 1), size: 18),
                    title: Text(PortalHelper.onTheWayOrdersLabel,
                        style: TextStyle(
                            color: Color.fromRGBO(153, 153, 153, 1),
                            fontSize: 14)),
                    onTap: () {
                      Navigator.push(context,
                          MaterialPageRoute(builder: (context) {
                        return OnTheWayDelivery(show_back_button: true);
                      }));
                    },
                  ),
                  ListTile(
                    visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                    leading: Icon(Icons.task_alt_outlined,
                        color: Color.fromRGBO(153, 153, 153, 1), size: 18),
                    title: Text(PortalHelper.reachedOrdersLabel,
                        style: TextStyle(
                            color: Color.fromRGBO(153, 153, 153, 1),
                            fontSize: 14)),
                    onTap: () {
                      Navigator.push(context,
                          MaterialPageRoute(builder: (context) {
                        return ReachedDelivery(show_back_button: true);
                      }));
                    },
                  ),
                  ListTile(
                    visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                    leading: Image.asset("assets/tick_circle.png",
                        height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                    title: Text(PortalHelper.completedLabel,
                        style: TextStyle(
                            color: Color.fromRGBO(153, 153, 153, 1),
                            fontSize: 14)),
                    onTap: () {
                      Navigator.push(context,
                          MaterialPageRoute(builder: (context) {
                        return CompletedDelivery(show_back_button: true);
                      }));
                    },
                  ),
                  ListTile(
                    visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                    leading: Image.asset("assets/cross_circle.png",
                        height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                    title: Text(PortalHelper.returnOrdersLabel,
                        style: TextStyle(
                            color: Color.fromRGBO(153, 153, 153, 1),
                            fontSize: 14)),
                    onTap: () {
                      Navigator.push(context,
                          MaterialPageRoute(builder: (context) {
                        return CancelledDelivery(show_back_button: true);
                      }));
                    },
                  ),
                  ListTile(
                    visualDensity: VisualDensity(horizontal: -4, vertical: -4),
                    leading: Image.asset("assets/dollar_circle.png",
                        height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                    title: Text(PortalHelper.earningsLabel,
                        style: TextStyle(
                            color: Color.fromRGBO(153, 153, 153, 1),
                            fontSize: 14)),
                    onTap: () {
                      Navigator.push(context,
                          MaterialPageRoute(builder: (context) {
                        return Earnings(show_back_button: true);
                      }));
                    },
                  ),
                ],
              //is_logged_in.$ == true
              false
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/profile.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text('Profile',
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        /*Navigator.push(context,
                        MaterialPageRoute(builder: (context) {
                          return Profile(show_back_button: true);
                        }));*/
                      })
                  : Container(),
              Divider(),
              is_logged_in.$ == false
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/login.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text(LangText(context).local!.login_ucf,
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        Navigator.push(context,
                            MaterialPageRoute(builder: (context) {
                          return Login();
                        }));
                        // pop(context);
                        // slideRightWidget(
                        //   newPage: Login(),
                        //   context: context,
                        // );
                      })
                  : Container(),
              is_logged_in.$ == true
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/logout.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text(LangText(context).local!.logout_ucf,
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        onTapLogout(context);
                      })
                  : Container(),
              is_logged_in.$ == true
                  ? ListTile(
                      visualDensity:
                          VisualDensity(horizontal: -4, vertical: -4),
                      leading: Image.asset("assets/trash.png",
                          height: 16, color: Color.fromRGBO(153, 153, 153, 1)),
                      title: Text(LangText(context).local!.account_delete_ucf,
                          style: TextStyle(
                              color: Color.fromRGBO(153, 153, 153, 1),
                              fontSize: 14)),
                      onTap: () {
                        deleteWarningDialog();
                      })
                  : Container(),
            ],
          ),
        ),
      ),
    );
  }
}
