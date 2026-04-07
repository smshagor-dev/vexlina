import 'package:active_flutter_delivery_app/app_config.dart';
import 'package:active_flutter_delivery_app/data_model/dashboard_summary_response.dart';
import 'package:active_flutter_delivery_app/helpers/api_request.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';

class DashboardRepository {
  Future<DashboardSummaryResponse> getDashboardSummaryResponse() async {

    final response = await ApiRequest.get(url: ("${AppConfig.BASE_URL}/${PortalHelper.apiPrefix}/dashboard-summary/${user_id.$}"),headers: { "Authorization": "Bearer ${access_token.$}","App-Language": app_language.$!,"System-Key": AppConfig.system_key});

    /*print("body\n");
    print(response.body.toString());*/
    return dashboardSummaryResponseFromJson(response.body);
  }


}
