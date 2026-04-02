import 'package:active_flutter_delivery_app/addon_config.dart';
import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/custom/input_decorations.dart';
import 'package:active_flutter_delivery_app/custom/intl_phone_input.dart';
import 'package:active_flutter_delivery_app/custom/lang_text.dart';
import 'package:active_flutter_delivery_app/custom/toast_component.dart';
import 'package:active_flutter_delivery_app/helpers/auth_helper.dart';
import 'package:active_flutter_delivery_app/helpers/biometric_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/auth_repository.dart';
import 'package:active_flutter_delivery_app/repositories/support_repository.dart';
import 'package:active_flutter_delivery_app/screens/main.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl_phone_number_input/intl_phone_number_input.dart';
import 'package:toast/toast.dart';

class Login extends StatefulWidget {
  @override
  _LoginState createState() => _LoginState();
}

class _LoginState extends State<Login> {
  String _login_by = "email"; //phone or email
  String initialCountry = 'US';
  PhoneNumber phoneCode = PhoneNumber(isoCode: 'US', dialCode: "+1");
  String? _phone = "";

  //controllers
  TextEditingController _phoneNumberController = TextEditingController();
  TextEditingController _emailController = TextEditingController();
  TextEditingController _passwordController = TextEditingController();
  final TextEditingController _supportNameController = TextEditingController();
  final TextEditingController _supportEmailController = TextEditingController();
  final TextEditingController _supportPhoneController = TextEditingController();
  final TextEditingController _supportMessageController = TextEditingController();
  final BiometricHelper _biometricHelper = BiometricHelper();
  bool _biometricAvailable = false;
  bool _hasBiometricCredentials = false;
  bool _isBiometricLoading = false;
  bool _isSupportSending = false;

  @override
  void initState() {
    //on Splash Screen hide statusbar
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual, overlays: [SystemUiOverlay.bottom]);
    super.initState();
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
    SystemChrome.setEnabledSystemUIMode(
        SystemUiMode.manual, overlays: [SystemUiOverlay.top, SystemUiOverlay.bottom]);
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
      ToastComponent.showDialog(LangText(context).local!.enter_email, context,
          gravity: Toast.center, duration: Toast.lengthLong);
      return;
    } else if (_login_by == 'phone' && _phone == "") {
      ToastComponent.showDialog(
          LangText(context).local!.enter_phone_number, context,
          gravity: Toast.center, duration: Toast.lengthLong);
      return;
    } else if (password == "") {
      ToastComponent.showDialog(LangText(context).local!.enter_password, context,
          gravity: Toast.center, duration: Toast.lengthLong);
      return;
    }

    var loginResponse = await AuthRepository()
        .getLoginResponse(_login_by == 'email' ? email : _phone, password,_login_by);

    if (loginResponse.result == false) {

      if(loginResponse.message.runtimeType == List){
        ToastComponent.showDialog(loginResponse.message!.join("\n"),context,gravity: Toast.center, duration:3);
        return;
      }

      ToastComponent.showDialog(loginResponse.message!, context,
          gravity: Toast.center, duration: Toast.lengthLong);
    } else {
      //print('dd');
      ToastComponent.showDialog(loginResponse.message!, context,
          gravity: Toast.center, duration: Toast.lengthLong);
      await _biometricHelper.saveCredentials(
        loginBy: _login_by,
        identifier: _login_by == 'email' ? email : (_phone ?? ''),
        password: password,
      );
      AuthHelper().setUserData(loginResponse);
      access_token.load().whenComplete(() {
        if (access_token.$!.isNotEmpty) {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) {
              return Main();
            }),
          );
        }
      });
      _loadBiometricState();
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
        context,
        gravity: Toast.center,
        duration: Toast.lengthLong,
      );
      return;
    }

    final loginResponse = await AuthRepository().getLoginResponse(
      credentialMap['identifier'],
      credentialMap['password'] ?? '',
      credentialMap['login_by'] ?? 'email',
    );

    if (!mounted) return;

    setState(() {
      _isBiometricLoading = false;
    });

    if (loginResponse.result == false) {
      ToastComponent.showDialog(
        loginResponse.message is List
            ? loginResponse.message!.join("\n")
            : (loginResponse.message ?? 'Biometric login failed'),
        context,
        gravity: Toast.center,
        duration: Toast.lengthLong,
      );
      return;
    }

    ToastComponent.showDialog(
      loginResponse.message ?? 'Login successful',
      context,
      gravity: Toast.center,
      duration: Toast.lengthLong,
    );
    AuthHelper().setUserData(loginResponse);
    access_token.load().whenComplete(() {
      if (access_token.$!.isNotEmpty) {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) {
            return Main();
          }),
        );
      }
    });
  }

  Future<void> _sendSupportMessage(BuildContext bottomSheetContext) async {
    final name = _supportNameController.text.trim();
    final email = _supportEmailController.text.trim();
    final phone = _supportPhoneController.text.trim();
    final message = _supportMessageController.text.trim();
    const subject = "Delivery Boy Login Support";

    if (name.isEmpty || email.isEmpty || message.isEmpty) {
      ToastComponent.showDialog(
        'Please fill name, email and message.',
        context,
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
      context,
      gravity: Toast.center,
      duration: Toast.lengthLong,
    );

    if (response.result == true) {
      Navigator.of(bottomSheetContext).pop();
      _supportMessageController.clear();
    }
  }

  void _showSupportForm() {
    _supportNameController.text = user_name.$?.isNotEmpty == true ? user_name.$! : "";
    _supportEmailController.text = _emailController.text.trim().isNotEmpty
        ? _emailController.text.trim()
        : (user_email.$?.isNotEmpty == true ? user_email.$! : "");
    _supportPhoneController.text = _phone ?? user_phone.$ ?? "";

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
                        backgroundColor: MyTheme.golden,
                        foregroundColor: Colors.black,
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
          style: TextStyle(
            color: MyTheme.golden,
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
            hint_text: hintText,
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async => false,
      child: Scaffold(
        backgroundColor: MyTheme.splash_login_screen_color,
        body: buildBody(context),
      ),
    );
  }

  buildBody(context) {
    final _screen_width = MediaQuery.of(context).size.width * (1);
    return Stack(
      children: [
        Align(
          alignment: Alignment.topCenter,
          child: Container(
            width: _screen_width * (3 / 4),
            child: Image.asset(
              "assets/splash_login_background_logo.png",
              color: Color.fromRGBO(225, 225, 225, .1),
            ),
          ),
        ),
        Container(
          width: double.infinity,
          child: SingleChildScrollView(
            child: Column(
              children: [
                Padding(
                  padding: const EdgeInsets.only(
                    top: 75,
                  ),
                  child: Container(
                      width: 75,
                      child: Image.asset("assets/delivery_app_logo.png")),
                ),
                Padding(
                  padding: const EdgeInsets.only(
                    top: 20,
                    bottom: 0.0,
                  ),
                  child: Text(
                    "${LangText(context).local!.login_to} ",
                    style: TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.w600),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.only(
                    top: 0,
                    bottom: 50.0,
                  ),
                  child: Text(
                    AppConfig.app_name,
                    style: TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.w600),
                  ),
                ),
                Container(
                  width: _screen_width * (3 / 4),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Padding(
                        padding: const EdgeInsets.only(bottom: 4.0),
                        child: Text(
                          _login_by == "email" ? "Email" : "Phone",
                          style: TextStyle(
                              color: MyTheme.golden,
                              fontWeight: FontWeight.w600),
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
                                child: TextField(
                                  style: TextStyle(color: Colors.white70),
                                  controller: _emailController,
                                  autofocus: false,
                                  decoration:
                                      InputDecorations.buildInputDecoration_1(
                                          hint_text: "johndoe@example.com"),
                                ),
                              ),
                              AddonConfig.otp_addon_installed
                                  ? GestureDetector(
                                      onTap: () {
                                        setState(() {
                                          _login_by = "phone";
                                        });
                                      },
                                      child: Text(
                                        LangText(context)
                                            .local!
                                            .or_login_with_a_phone,
                                        style: TextStyle(
                                            color: MyTheme.golden,
                                            fontStyle: FontStyle.italic,
                                            decoration:
                                                TextDecoration.underline),
                                      ),
                                    )
                                  : Container()
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
                                height: 36,
                                child: CustomInternationalPhoneNumberInput(
                                  onInputChanged: (PhoneNumber number) {
                                    print(number.phoneNumber);
                                    setState(() {
                                      _phone = number.phoneNumber;
                                    });
                                  },
                                  onInputValidated: (bool value) {
                                    print('on input validation ${value}');
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
                                  inputDecoration: InputDecorations
                                      .buildInputDecoration_phone(
                                          hint_text: "01XX XXX XXX"),
                                  onSaved: (PhoneNumber number) {
                                    print('On Saved: $number');
                                  },
                                ),
                              ),
                              GestureDetector(
                                onTap: () {
                                  setState(() {
                                    _login_by = "email";
                                  });
                                },
                                child: Text(
                                  LangText(context)
                                      .local!
                                      .or_login_with_an_email,
                                  style: TextStyle(
                                      color: MyTheme.golden,
                                      fontStyle: FontStyle.italic,
                                      decoration: TextDecoration.underline),
                                ),
                              )
                            ],
                          ),
                        ),
                      Padding(
                        padding: const EdgeInsets.only(bottom: 4.0),
                        child: Text(
                          LangText(context).local!.password_ucf,
                          style: TextStyle(
                              color: MyTheme.golden,
                              fontWeight: FontWeight.w600),
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.only(bottom: 8.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Container(
                              height: 36,
                              child: TextField(
                                controller: _passwordController,
                                autofocus: false,
                                obscureText: true,
                                enableSuggestions: false,
                                autocorrect: false,
                                style: TextStyle(color: Colors.white70),
                                decoration:
                                    InputDecorations.buildInputDecoration_1(
                                        hint_text: "• • • • • • • •"),
                              ),
                            ),
                            /*GestureDetector(
                            onTap: () {
                              Navigator.push(context,
                                  MaterialPageRoute(builder: (context) {
                                    return PasswordForget();
                                  }));
                            },
                            child: Text(
                              "Forgot Password?",
                              style: TextStyle(
                                  color: MyTheme.golden,
                                  fontStyle: FontStyle.italic,
                                  decoration: TextDecoration.underline),
                            ),
                          )*/
                          ],
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.only(top: 30.0),
                        child: Container(
                          height: 45,
                          decoration: BoxDecoration(
                              border: Border.all(
                                  color: MyTheme.textfield_grey, width: 1),
                              borderRadius: const BorderRadius.all(
                                  Radius.circular(12.0))),
                          child: TextButton(
                            style: TextButton.styleFrom(
                              minimumSize:
                                  Size(MediaQuery.of(context).size.width, 0),
                              //height: 50,
                              backgroundColor: MyTheme.golden,
                              shape: RoundedRectangleBorder(
                                  borderRadius: const BorderRadius.all(
                                      Radius.circular(12.0))),
                            ),
                            child: Text(
                              LangText(context).local!.login_screen_log_in,
                              style: TextStyle(
                                  color: Colors.black,
                                  fontSize: 14,
                                  fontWeight: FontWeight.w600),
                            ),
                            onPressed: () {
                              onPressedLogin();
                            },
                          ),
                        ),
                      ),
                      Padding(
                        padding:
                            const EdgeInsets.only(top: 10, left: 10, right: 10),
                        child: Column(
                          children: [
                            Text(
                              "If you are finding any problem while logging in please contact support",
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.cyanAccent,
                              ),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 10),
                            TextButton(
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
                          ],
                        ),
                      ),
                      if (_biometricAvailable && _hasBiometricCredentials)
                        Padding(
                          padding: const EdgeInsets.only(top: 24.0),
                          child: Container(
                            width: double.infinity,
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(.08),
                              borderRadius: const BorderRadius.all(Radius.circular(16)),
                              border: Border.all(
                                color: Colors.white.withOpacity(.2),
                              ),
                            ),
                            child: TextButton.icon(
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                              ),
                              onPressed: _isBiometricLoading ? null : _loginWithBiometric,
                              icon: _isBiometricLoading
                                  ? const SizedBox(
                                      height: 18,
                                      width: 18,
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    )
                                  : const Icon(Icons.fingerprint, size: 28),
                              label: Text(
                                _isBiometricLoading ? 'Checking biometrics...' : 'Login with Biometrics',
                                style: const TextStyle(
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
              ],
            ),
          ),
        )
      ],
    );
  }
}
