import 'package:active_ecommerce_seller_app/app_config.dart';
import 'package:active_ecommerce_seller_app/custom/buttons.dart';
import 'package:active_ecommerce_seller_app/custom/input_decorations.dart';
import 'package:active_ecommerce_seller_app/custom/intl_phone_input.dart';
import 'package:active_ecommerce_seller_app/custom/localization.dart';
import 'package:active_ecommerce_seller_app/custom/my_widget.dart';
import 'package:active_ecommerce_seller_app/custom/toast_component.dart';
import 'package:active_ecommerce_seller_app/helpers/auth_helper.dart';
import 'package:active_ecommerce_seller_app/helpers/biometric_helper.dart';
import 'package:active_ecommerce_seller_app/helpers/main_helper.dart';
import 'package:active_ecommerce_seller_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_seller_app/my_theme.dart';
import 'package:active_ecommerce_seller_app/repositories/address_repository.dart';
import 'package:active_ecommerce_seller_app/repositories/auth_repository.dart';
import 'package:active_ecommerce_seller_app/repositories/support_repository.dart';
import 'package:active_ecommerce_seller_app/screens/main.dart';
import 'package:active_ecommerce_seller_app/screens/password_forget.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:active_ecommerce_seller_app/l10n/app_localizations.dart';
import 'package:intl_phone_number_input/intl_phone_number_input.dart';
import 'package:one_context/one_context.dart';
import 'package:toast/toast.dart';

class Login extends StatefulWidget {
  const Login({super.key});

  @override
  _LoginState createState() => _LoginState();
}

class _LoginState extends State<Login> {
  String _login_by = "email"; //phone or email
  String initialCountry = 'US';
  PhoneNumber phoneCode = PhoneNumber(isoCode: 'US', dialCode: "+1");
  String? _phone = "";
  late BuildContext loadingContext;
  var countries_code = <String?>[];

  //controllers
  final TextEditingController _phoneNumberController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _supportNameController = TextEditingController();
  final TextEditingController _supportEmailController = TextEditingController();
  final TextEditingController _supportPhoneController = TextEditingController();
  final TextEditingController _supportMessageController = TextEditingController();
  final BiometricHelper _biometricHelper = BiometricHelper();
  bool _biometricAvailable = false;
  bool _hasBiometricCredentials = false;
  bool _isBiometricLoading = false;
  bool _isSupportSending = false;
  MyWidget? myWidget;

  fetch_country() async {
    var data = await AddressRepository().getCountryList();
    data.countries!.forEach((c) => countries_code.add(c.code));
    phoneCode = PhoneNumber(isoCode: data.countries!.first.code);
    setState(() {});
  }

  @override
  void initState() {
    //on Splash Screen hide statusbar
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual,
        overlays: [SystemUiOverlay.bottom]);
    super.initState();
    if (otp_addon_installed.$) {
      fetch_country();
    }
    _loadBiometricState();
    /*if (is_logged_in.value == true) {
      Navigator.push(context, MaterialPageRoute(builder: (context) {
        return Main();
      }));
    }*/
  }

  @override
  void dispose() {
    //before going to other screen show statusbar
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual,
        overlays: [SystemUiOverlay.top, SystemUiOverlay.bottom]);
    _phoneNumberController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _supportNameController.dispose();
    _supportEmailController.dispose();
    _supportPhoneController.dispose();
    _supportMessageController.dispose();
    super.dispose();
  }

  Future<void> _loadBiometricState() async {
    final available = await _biometricHelper.isBiometricAvailable();
    final hasCredentials = await _biometricHelper.hasSavedCredentials();
    if (!mounted) return;
    setState(() {
      _biometricAvailable = available;
      _hasBiometricCredentials = hasCredentials;
    });
  }

  onPressedLogin() async {
    var email = _emailController.text.toString();
    var password = _passwordController.text.toString();

    if (_login_by == 'email' && email == "") {
      ToastComponent.showDialog(
          LangText(context: OneContext().context).getLocal().enter_email,
          gravity: Toast.center,
          duration: Toast.lengthLong);
      return;
    } else if (_login_by == 'phone' && _phone == "") {
      ToastComponent.showDialog(
          LangText(context: OneContext().context).getLocal().enter_phone_number,
          gravity: Toast.center,
          duration: Toast.lengthLong);
      return;
    } else if (password == "") {
      ToastComponent.showDialog(
          LangText(context: OneContext().context).getLocal().enter_password,
          gravity: Toast.center,
          duration: Toast.lengthLong);
      return;
    }
    loading();
    var loginResponse = await AuthRepository().getLoginResponse(
        _login_by == 'email' ? email : _phone, password, _login_by);
    Navigator.pop(loadingContext);

    if (loginResponse.result == true) {
      if (loginResponse.message.runtimeType == List) {
        ToastComponent.showDialog(loginResponse.message!.join("\n"),
            gravity: Toast.center, duration: 3);
        return;
      }

      ToastComponent.showDialog(loginResponse.message!,
          gravity: Toast.center, duration: Toast.lengthLong);
      await _biometricHelper.saveCredentials(
        loginBy: _login_by,
        identifier: _login_by == 'email' ? email : (_phone ?? ''),
        password: password,
      );
      AuthHelper().setUserData(loginResponse);
      await _loadBiometricState();

      access_token.load().whenComplete(() {
        if (access_token.$!.isNotEmpty) {
          Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
            builder: (context) {
              return Main();
            },
          ), (route) => false);
        }
      });
    } else {
      ToastComponent.showDialog(loginResponse.message!,
          gravity: Toast.center, duration: Toast.lengthLong);
    }
  }

  Future<void> _loginWithBiometric() async {
    if (_isBiometricLoading) return;

    setState(() {
      _isBiometricLoading = true;
    });

    final credentialMap = await _biometricHelper.authenticateAndGetCredentials();

    if (!mounted) return;

    if (credentialMap == null) {
      setState(() {
        _isBiometricLoading = false;
      });
      ToastComponent.showDialog(
        'Biometric verification failed or no saved login found.',
        gravity: Toast.center,
        duration: Toast.lengthLong,
      );
      return;
    }

    loading();
    try {
      final loginResponse = await AuthRepository().getLoginResponse(
        credentialMap['identifier'],
        credentialMap['password'] ?? '',
        credentialMap['login_by'] ?? 'email',
      );

      if (Navigator.canPop(loadingContext)) {
        Navigator.pop(loadingContext);
      }

      if (loginResponse.result == true) {
        ToastComponent.showDialog(
          loginResponse.message ?? 'Login successful',
          gravity: Toast.center,
          duration: Toast.lengthLong,
        );
        AuthHelper().setUserData(loginResponse);

        access_token.load().whenComplete(() {
          if (access_token.$!.isNotEmpty) {
            Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
              builder: (context) {
                return Main();
              },
            ), (route) => false);
          }
        });
      } else {
        ToastComponent.showDialog(
          loginResponse.message ?? 'Biometric login failed',
          gravity: Toast.center,
          duration: Toast.lengthLong,
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isBiometricLoading = false;
        });
      }
    }
  }

  Future<void> _sendSupportMessage(BuildContext bottomSheetContext) async {
    final name = _supportNameController.text.trim();
    final email = _supportEmailController.text.trim();
    final phone = _supportPhoneController.text.trim();
    final message = _supportMessageController.text.trim();
    const subject = "Seller Login Support";

    if (name.isEmpty || email.isEmpty || message.isEmpty) {
      ToastComponent.showDialog(
        'Please fill name, email and message.',
        gravity: Toast.center,
        duration: Toast.lengthLong,
      );
      return;
    }

    setState(() {
      _isSupportSending = true;
    });

    final response = await SupportRepository().sendLoginSupportMessage(
      name: name,
      email: email,
      phone: phone,
      subject: subject,
      message: message,
    );

    if (!mounted) return;

    setState(() {
      _isSupportSending = false;
    });

    ToastComponent.showDialog(
      response.message ?? 'Support request sent',
      gravity: Toast.center,
      duration: Toast.lengthLong,
    );

    if (response.result == true) {
      Navigator.of(bottomSheetContext).pop();
      _supportMessageController.clear();
    }
  }

  void _showSupportForm() {
    _supportNameController.text = shop_name.$.isNotEmpty ? shop_name.$ : "";
    _supportEmailController.text = _emailController.text.trim().isNotEmpty
        ? _emailController.text.trim()
        : seller_email.$;
    _supportPhoneController.text = _phone ?? "";

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (bottomSheetContext) {
        return Padding(
          padding: EdgeInsets.only(
            left: 16,
            right: 16,
            top: 24,
            bottom: MediaQuery.of(bottomSheetContext).viewInsets.bottom + 16,
          ),
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: const Color.fromRGBO(31, 30, 36, 1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    "Contact Support",
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    "Send your login issue directly to support.",
                    style: TextStyle(color: Colors.white.withValues(alpha: .7)),
                  ),
                  const SizedBox(height: 16),
                  _buildSupportField(
                    label: "Name",
                    controller: _supportNameController,
                    hintText: "Your full name",
                  ),
                  const SizedBox(height: 12),
                  _buildSupportField(
                    label: "Email",
                    controller: _supportEmailController,
                    hintText: "you@example.com",
                    keyboardType: TextInputType.emailAddress,
                  ),
                  const SizedBox(height: 12),
                  _buildSupportField(
                    label: "Phone",
                    controller: _supportPhoneController,
                    hintText: "Optional phone number",
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 12),
                  _buildSupportField(
                    label: "Message",
                    controller: _supportMessageController,
                    hintText: "Describe your login problem",
                    maxLines: 5,
                  ),
                  const SizedBox(height: 18),
                  SizedBox(
                    width: double.infinity,
                    child: TextButton(
                      style: TextButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: MyTheme.app_accent_color,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                      onPressed: _isSupportSending
                          ? null
                          : () => _sendSupportMessage(bottomSheetContext),
                      child: Text(
                        _isSupportSending ? "Sending..." : "Send Message to Support",
                        style: const TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildSupportField({
    required String label,
    required TextEditingController controller,
    required String hintText,
    TextInputType keyboardType = TextInputType.text,
    int maxLines = 1,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          keyboardType: keyboardType,
          maxLines: maxLines,
          style: const TextStyle(color: Colors.white70),
          decoration: InputDecorations.buildInputDecoration_1(
            borderColor: Colors.transparent,
            hint_text: hintText,
            hintTextColor: Colors.white54,
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    myWidget = MyWidget(myContext: context);
    return WillPopScope(
      onWillPop: () async => false,
      child: Scaffold(
        backgroundColor: MyTheme.login_reg_screen_color,
        body: buildBody(context),
      ),
    );
  }

  buildBody(context) {
    final screen_width = MediaQuery.of(context).size.width;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 36),
      width: double.infinity,
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              height: 100,
            ),
            Text(
              LangText(context: context).getLocal().hi_welcome_to_all_lower,
              style: TextStyle(
                  color: MyTheme.app_accent_border,
                  fontSize: 20,
                  fontWeight: FontWeight.w300),
            ),
            SizedBox(
              height: 20,
            ),
            Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Container(
                    padding: EdgeInsets.all(8),
                    decoration: BoxDecoration(
                        border: Border.all(color: MyTheme.app_accent_border),
                        borderRadius: BorderRadius.circular(10)),
                    width: 72,
                    height: 72,
                    child: Image.asset(
                      "assets/logo/white_logo.png",
                      height: 48,
                      width: 36,
                    )),
                SizedBox(
                  width: 10,
                ),
                SizedBox(
                  width: screen_width / 2,
                  child: Text(
                    AppConfig.app_name,
                    style: TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.w500),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.only(
                top: 40,
                bottom: 30.0,
              ),
              child: Text(
                LangText(context: context)
                    .getLocal()
                    .login_to_your_account_all_lower,
                style: TextStyle(
                    color: MyTheme.app_accent_border,
                    fontSize: 20,
                    fontWeight: FontWeight.w300),
              ),
            ),

            // login form container
            SizedBox(
              width: screen_width,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.only(bottom: 4.0),
                    child: Text(
                      _login_by == "email"
                          ? LangText(context: context).getLocal().email_ucf
                          : LangText(context: context)
                              .getLocal()
                              .login_screen_phone,
                      style: const TextStyle(
                          color: MyTheme.app_accent_border,
                          fontWeight: FontWeight.w400,
                          fontSize: 12),
                    ),
                  ),
                  if (_login_by == "email")
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Container(
                            height: 36,
                            decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(10),
                                color: Color.fromRGBO(255, 255, 255, 0.5)),
                            child: TextField(
                              style: TextStyle(color: MyTheme.white),
                              controller: _emailController,
                              autofocus: false,
                              decoration:
                                  InputDecorations.buildInputDecoration_1(
                                      borderColor: MyTheme.noColor,
                                      hint_text: LangText(context: context)
                                          .getLocal()
                                          .sellerexample,
                                      hintTextColor: MyTheme.dark_grey),
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Container(
                            decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(10),
                                color: Color.fromRGBO(255, 255, 255, 0.5)),
                            height: 36,
                            child: CustomInternationalPhoneNumberInput(
                              countries: countries_code,
                              onInputChanged: (PhoneNumber number) {
                                print(number.phoneNumber);
                                setState(() {
                                  _phone = number.phoneNumber;
                                });
                              },
                              onInputValidated: (bool value) {
                                print('on input validation $value');
                              },
                              selectorConfig: SelectorConfig(
                                selectorType: PhoneInputSelectorType.DIALOG,
                              ),
                              ignoreBlank: false,
                              autoValidateMode: AutovalidateMode.disabled,
                              selectorTextStyle:
                                  TextStyle(color: MyTheme.font_grey),
                              textStyle: TextStyle(color: Colors.white54),
                              initialValue: phoneCode,
                              textFieldController: _phoneNumberController,
                              formatInput: true,
                              keyboardType: TextInputType.numberWithOptions(
                                  signed: true, decimal: true),
                              inputDecoration:
                                  InputDecorations.buildInputDecoration_phone(
                                      hint_text: "01XXX XXX XXX"),
                              onSaved: (PhoneNumber number) {
                                print('On Saved: $number');
                              },
                            ),
                          ),
                        ],
                      ),
                    ),
                  if (otp_addon_installed.$)
                    Row(
                      children: [
                        Spacer(),
                        GestureDetector(
                          onTap: () {
                            setState(() {
                              _login_by =
                                  _login_by == "email" ? "phone" : "email";
                            });
                          },
                          child: Text(
                            "or, Login with ${_login_by == "email" ? 'a phone' : 'an email'}",
                            style: TextStyle(
                                color: MyTheme.white,
                                fontStyle: FontStyle.italic,
                                decoration: TextDecoration.underline),
                          ),
                        ),
                      ],
                    ),
                  Padding(
                    padding: const EdgeInsets.only(bottom: 4.0),
                    child: Text(
                      getLocal(context).password_ucf,
                      style: TextStyle(
                          color: MyTheme.app_accent_border,
                          fontWeight: FontWeight.w400,
                          fontSize: 12),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Container(
                          decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10),
                              color: Color.fromRGBO(255, 255, 255, 0.5)),
                          height: 36,
                          child: TextField(
                            controller: _passwordController,
                            autofocus: false,
                            obscureText: true,
                            enableSuggestions: false,
                            autocorrect: false,
                            style: TextStyle(color: MyTheme.white),
                            decoration: InputDecorations.buildInputDecoration_1(
                                borderColor: MyTheme.noColor,
                                hint_text: "• • • • • • • •",
                                hintTextColor: MyTheme.dark_grey),
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.only(top: 8.0),
                          child: GestureDetector(
                            onTap: () {
                              Navigator.push(context,
                                  MaterialPageRoute(builder: (context) {
                                return PasswordForget();
                              }));
                            },
                            child: Text(
                              getLocal(context).forget_password_ucf,
                              style: TextStyle(
                                  color: MyTheme.white,
                                  fontStyle: FontStyle.italic,
                                  decoration: TextDecoration.underline),
                            ),
                          ),
                        )
                      ],
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.only(top: 30.0),
                    child: Container(
                      height: 45,
                      decoration: BoxDecoration(
                          border: Border.all(
                              color: MyTheme.app_accent_border, width: 1),
                          borderRadius:
                              const BorderRadius.all(Radius.circular(12.0))),
                      child: Buttons(
                        width: MediaQuery.of(context).size.width,
                        height: 50,
                        color: Colors.white.withOpacity(0.8),
                        shape: RoundedRectangleBorder(
                          borderRadius: const BorderRadius.all(
                            Radius.circular(11.0),
                          ),
                        ),
                        child: Text(
                          getLocal(context).log_in,
                          style: TextStyle(
                              color: MyTheme.app_accent_color,
                              fontSize: 17,
                              fontWeight: FontWeight.w500),
                        ),
                        onPressed: () {
                          onPressedLogin();
                        },
                      ),
                    ),
                  ),
                  if (_biometricAvailable && _hasBiometricCredentials)
                    Padding(
                      padding: const EdgeInsets.only(top: 14.0),
                      child: SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            side: BorderSide(
                              color: Colors.white.withValues(alpha: .35),
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: _isBiometricLoading
                              ? null
                              : _loginWithBiometric,
                          icon: _isBiometricLoading
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: Colors.white,
                                  ),
                                )
                              : const Icon(
                                  Icons.fingerprint,
                                  color: Colors.white,
                                  size: 26,
                                ),
                          label: Text(
                            _isBiometricLoading
                                ? 'Checking biometrics...'
                                : 'Login with Biometrics',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(
                top: 20,
              ),
              child: Container(
                alignment: Alignment.center,
                child: Text(
                  "In case of any difficulties contact support",
                  style:
                      TextStyle(fontSize: 12, color: MyTheme.app_accent_border),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(top: 10),
              child: Center(
                child: TextButton(
                  onPressed: _showSupportForm,
                  style: TextButton.styleFrom(
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 10,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                      side: BorderSide(
                        color: Colors.white.withValues(alpha: .25),
                      ),
                    ),
                  ),
                  child: const Text(
                    "Message Support",
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ),
            // Padding(
            //   padding: const EdgeInsets.only(
            //     top: 20,
            //   ),
            //   child: Container(
            //     alignment: Alignment.center,
            //     child: Text(
            //       LangText(context: context).getLocal()!.or,
            //       style:
            //           TextStyle(fontSize: 12, color: MyTheme.app_accent_border),
            //     ),
            //   ),
            // ),
            // Padding(
            //   padding: const EdgeInsets.only(top: 10.0),
            //   child: Container(
            //     alignment: Alignment.center,
            //     height: 45,
            //     child: Buttons(
            //       alignment: Alignment.center,
            //       //width: MediaQuery.of(context).size.width,
            //       height: 50,
            //       //color: Colors.white.withOpacity(0.8),
            //       child: Text(
            //         LangText(context: context).getLocal()!.registration,
            //         style: TextStyle(
            //             color: MyTheme.white,
            //             fontSize: 17,
            //             fontWeight: FontWeight.w500,
            //             decoration: TextDecoration.underline),
            //       ),
            //       onPressed: () {
            //         Navigator.push(
            //             context,
            //             MaterialPageRoute(
            //                 builder: (context) => Registration()));
            //       },
            //     ),
            //   ),
            // ),
          ],
        ),
      ),
    );
  }

  loading() {
    showDialog(
        context: context,
        builder: (context) {
          loadingContext = context;
          return AlertDialog(
              content: Row(
            children: [
              const CircularProgressIndicator(),
              const SizedBox(
                width: 10,
              ),
              Text(AppLocalizations.of(context)!.please_wait_ucf),
            ],
          ));
        });
  }
}
