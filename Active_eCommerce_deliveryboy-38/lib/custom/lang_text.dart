
import 'package:flutter/cupertino.dart';
import 'package:active_flutter_delivery_app/l10n/app_localizations.dart';

class LangText{

  BuildContext context;
  AppLocalizations? local;

  LangText(this.context){
   local= AppLocalizations.of(context);
  }
}
