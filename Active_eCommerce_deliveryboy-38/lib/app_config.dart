var this_year = DateTime.now().year.toString();

class AppConfig {
  static String copyright_text =
      "@ Vexlina " + this_year; //this shows in the splash screen
  static String app_name =
      "Vexlina Delivery"; //this shows in the splash screen

  //Default language config
  static String default_language = "en";
  static String mobile_app_code = "en";
  static bool app_language_rtl = false;
  static String system_key = "b15c483b-686a-1343-a919-810ac895d0dc";

  //configure this
  static const bool HTTPS = true;

  //configure this
  static const DOMAIN_PATH = "vexlina.com";

  //do not configure these below
  static const String API_ENDPATH = "api/v2";
  static const String PUBLIC_FOLDER = "public";
  static const String DELIVERY_PREFIX = "delivery-boy";
  static const String PROTOCOL = HTTPS ? "https://" : "http://";
  static const String RAW_BASE_URL = "${PROTOCOL}${DOMAIN_PATH}";
  static const String BASE_URL = "${RAW_BASE_URL}/${API_ENDPATH}";

  //configure this if you are using amazon s3 like services
  //give direct link to file like https://[[bucketname]].s3.ap-southeast-1.amazonaws.com/
  //otherwise do not change anythink
  static const String BASE_PATH = "${RAW_BASE_URL}/${PUBLIC_FOLDER}/";
}
