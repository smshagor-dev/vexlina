import 'package:active_ecommerce_seller_app/custom/toast_component.dart';
import 'package:active_ecommerce_seller_app/helpers/main_helper.dart';
import 'package:active_ecommerce_seller_app/my_theme.dart';
import 'package:active_ecommerce_seller_app/repositories/payment_repository.dart';
import 'package:flutter/material.dart';
import 'package:toast/toast.dart';
import 'dart:convert';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:active_ecommerce_seller_app/l10n/app_localizations.dart';

class NagadScreen extends StatefulWidget {
  double amount;
  String payment_type;
  String? payment_method_key;
  String? package_id;

  NagadScreen(
      {super.key,
      this.amount = 0.00,
      this.payment_type = "",
      this.payment_method_key = "",
      this.package_id});

  @override
  _NagadScreenState createState() => _NagadScreenState();
}

class _NagadScreenState extends State<NagadScreen> {
  final int _combined_order_id = 0;
  final bool _order_init = false;
  String? _initial_url = "";
  bool _initial_url_fetched = false;

  late WebViewController _webViewController;

  @override
  void initState() {
    // TODO: implement initState
    super.initState();
    _webViewController = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(onPageFinished: (page) {
          //print(page.toString());

          if (page.contains("/nagad/verify/") ||
              page.contains('/check-out/confirm-payment/')) {
            getData();
          } else {
            if (page.contains('confirm-payment')) {
              print('yessssssss');
            } else {
              print('nooooooooo');
            }
          }
        }),
      );
    getSetInitialUrl();
  }

  getSetInitialUrl() async {
    var nagadUrlResponse = await PaymentRepository().getNagadBeginResponse(
        widget.payment_type,
        _combined_order_id,
        widget.package_id,
        widget.amount);

    if (nagadUrlResponse.result == false) {
      ToastComponent.showDialog(nagadUrlResponse.message!,
          gravity: Toast.center, duration: Toast.lengthLong);
      Navigator.of(context).pop();
      return;
    }

    _initial_url = nagadUrlResponse.url;
    _initial_url_fetched = true;
    _webViewController.loadRequest(Uri.parse(_initial_url!),
        headers: commonHeader);

    setState(() {});

    //print(_initial_url);
    //print(_initial_url_fetched);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: buildAppBar(context),
      body: buildBody(),
    );
  }

  void getData() {
    String? paymentDetails = '';
    _webViewController
        .runJavaScriptReturningResult("document.body.innerText")
        .then((data) {
      var responseJSON = jsonDecode(data as String);
      if (responseJSON.runtimeType == String) {
        responseJSON = jsonDecode(responseJSON);
      }
      if (responseJSON["result"] == false) {
        ToastComponent.showDialog(responseJSON["message"],
            duration: Toast.lengthLong, gravity: Toast.center);
        Navigator.pop(context);
      } else if (responseJSON["result"] == true) {
        paymentDetails = responseJSON['payment_details'];
        onPaymentSuccess(paymentDetails);
      }
    });
  }

  onPaymentSuccess(paymentDetails) async {
    var nagadPaymentProcessResponse = await PaymentRepository()
        .getNagadPaymentProcessResponse(widget.payment_type, widget.amount,
            _combined_order_id, paymentDetails);

    if (nagadPaymentProcessResponse.result == false) {
      ToastComponent.showDialog(nagadPaymentProcessResponse.message!,
          duration: Toast.lengthLong, gravity: Toast.center);
      Navigator.pop(context);
      return;
    }

    ToastComponent.showDialog(nagadPaymentProcessResponse.message!,
        duration: Toast.lengthLong, gravity: Toast.center);
    Navigator.pop(context);
  }

  buildBody() {
    if (_initial_url_fetched == false) {
      return Container(
        child: Center(
          child: Text(AppLocalizations.of(context)!.fetching_nagad_url),
        ),
      );
    } else {
      return SingleChildScrollView(
        child: SizedBox(
          width: MediaQuery.of(context).size.width,
          height: MediaQuery.of(context).size.height,
          child: WebViewWidget(
            controller: _webViewController,
          ),
        ),
      );
    }
  }

  AppBar buildAppBar(BuildContext context) {
    return AppBar(
      backgroundColor: Colors.white,
      centerTitle: true,
      leading: Builder(
        builder: (context) => IconButton(
          icon: Icon(Icons.arrow_back, color: MyTheme.dark_grey),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      title: Text(
        AppLocalizations.of(context)!.pay_with_nagad,
        style: TextStyle(fontSize: 16, color: MyTheme.app_accent_color),
      ),
      elevation: 0.0,
      titleSpacing: 0,
    );
  }
}
