import 'package:active_flutter_delivery_app/custom/toast_component.dart';
import 'package:active_flutter_delivery_app/data_model/pickup_payout_requests_response.dart';
import 'package:active_flutter_delivery_app/data_model/pickup_payout_summary_response.dart';
import 'package:active_flutter_delivery_app/helpers/portal_helper.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/pickup_payout_repository.dart';
import 'package:active_flutter_delivery_app/screens/pickup_payout_info.dart';
import 'package:active_flutter_delivery_app/ui_sections/drawer.dart';
import 'package:flutter/material.dart';

class PickupPayouts extends StatefulWidget {
  const PickupPayouts({Key? key, this.showBackButton = false}) : super(key: key);

  final bool showBackButton;

  @override
  State<PickupPayouts> createState() => _PickupPayoutsState();
}

class _PickupPayoutsState extends State<PickupPayouts> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final _amountController = TextEditingController();
  final _messageController = TextEditingController();

  PickupPayoutSummaryResponse? _summaryResponse;
  List<PickupPayoutRequestItem> _requests = [];
  bool _isLoading = true;
  bool _isSubmittingRequest = false;

  @override
  void initState() {
    super.initState();
    _fetchAll();
  }

  Future<void> _fetchAll() async {
    setState(() {
      _isLoading = true;
    });

    final summary = await PickupPayoutRepository().getSummary();
    final requests = await PickupPayoutRepository().getRequests();

    _summaryResponse = summary;
    _requests = requests.data ?? [];

    if (mounted) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _submitRequest() async {
    if (_amountController.text.trim().isEmpty) {
      ToastComponent.showDialog("Request amount is required.", context);
      return;
    }

    setState(() {
      _isSubmittingRequest = true;
    });

    final response = await PickupPayoutRepository().storePayoutRequest(
      amount: _amountController.text.trim(),
      message: _messageController.text.trim(),
    );

    if (mounted) {
      setState(() {
        _isSubmittingRequest = false;
      });
    }

    ToastComponent.showDialog(response.message ?? "Submitted", context);
    if (response.result == true) {
      _amountController.clear();
      _messageController.clear();
      await _fetchAll();
    }
  }

  @override
  void dispose() {
    _amountController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async => widget.showBackButton,
      child: Scaffold(
        key: _scaffoldKey,
        backgroundColor: const Color.fromRGBO(248, 249, 251, 1),
        appBar: _buildAppBar(context),
        drawer: const MainDrawer(),
        body: RefreshIndicator(
          color: MyTheme.accent_color,
          onRefresh: _fetchAll,
          child: _isLoading
              ? const Center(child: CircularProgressIndicator())
              : ListView(
                  padding: const EdgeInsets.all(12),
                  children: [
                    _buildSummaryCards(),
                    const SizedBox(height: 14),
                    _buildRequestForm(),
                    const SizedBox(height: 14),
                    _buildInfoCard(),
                    const SizedBox(height: 14),
                    _buildHistory(),
                    const SizedBox(height: 90),
                  ],
                ),
        ),
      ),
    );
  }

  PreferredSizeWidget _buildAppBar(BuildContext context) {
    return AppBar(
      centerTitle: false,
      backgroundColor: Colors.white,
      elevation: 0,
      leading: widget.showBackButton
          ? IconButton(
              onPressed: () => Navigator.of(context).pop(),
              icon: Icon(Icons.arrow_back, color: MyTheme.dark_grey),
            )
          : IconButton(
              onPressed: () => _scaffoldKey.currentState?.openDrawer(),
              icon: Icon(Icons.menu, color: MyTheme.dark_grey),
            ),
      title: Text(
        PortalHelper.isPickupPointApp ? "Payouts" : "Payouts",
        style: TextStyle(color: MyTheme.accent_color, fontSize: 16),
      ),
    );
  }

  Widget _buildSummaryCards() {
    final summary = _summaryResponse?.summary;
    final items = [
      {"label": "Current Balance", "value": summary?.currentBalance ?? "--"},
      {"label": "Requestable", "value": summary?.requestableBalance ?? "--"},
      {"label": "Pending", "value": summary?.pendingPayoutTotal ?? "--"},
      {
        "label": "Cycle",
        "value":
            "${summary?.payoutFrequencyDays?.toString() ?? '--'} day(s)"
      },
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: items.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: 1.45,
      ),
      itemBuilder: (context, index) {
        final item = items[index];
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: .04),
                blurRadius: 16,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                item["label"]!,
                style: TextStyle(
                  fontSize: 12,
                  color: MyTheme.font_grey,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                item["value"]!,
                style: TextStyle(
                  fontSize: 16,
                  color: MyTheme.dark_grey,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildInfoCard() {
    final profile = _summaryResponse?.pickupPoint;

    return _buildSectionCard(
      title: "Payout Information",
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildInfoLine(
            "Method",
            _labelize(profile?.payoutMethod),
          ),
          _buildInfoLine(
            "Account Holder",
            profile?.payoutAccountName ?? "--",
          ),
          _buildInfoLine(
            "Account / Wallet Number",
            profile?.payoutAccountNumber ?? "--",
          ),
          _buildInfoLine(
            "Bank",
            profile?.payoutBankName ?? "--",
          ),
          _buildInfoLine(
            "Branch",
            profile?.payoutBranchName ?? "--",
          ),
          _buildInfoLine(
            "Routing",
            profile?.payoutRoutingNumber ?? "--",
          ),
          _buildInfoLine(
            "Wallet Type",
            profile?.payoutMobileWalletType ?? "--",
          ),
          _buildInfoLine(
            "Wallet Number",
            profile?.payoutMobileWalletNumber ?? "--",
          ),
          _buildInfoLine(
            "Notes",
            profile?.payoutNotes ?? "--",
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
                side: BorderSide(color: MyTheme.accent_color_2),
              ),
              onPressed: () async {
                final updated = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const PickupPayoutInfo(),
                  ),
                );
                if (updated == true) {
                  await _fetchAll();
                }
              },
              child: Text(
                "Edit Payout Info",
                style: TextStyle(color: MyTheme.accent_color_2),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRequestForm() {
    final summary = _summaryResponse?.summary;

    return _buildSectionCard(
      title: "Request Payout",
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color.fromRGBO(36, 33, 36, .06),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              summary?.eligibilityMessage ??
                  "You can request payout based on the admin-selected payout cycle.",
              style: TextStyle(color: MyTheme.font_grey, fontSize: 12),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _amountController,
            keyboardType:
                const TextInputType.numberWithOptions(decimal: true),
            decoration: _inputDecoration("Request Amount"),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _messageController,
            maxLines: 3,
            decoration: _inputDecoration("Message To Admin"),
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: MyTheme.accent_color_2,
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              onPressed: (summary?.canRequest ?? false) && !_isSubmittingRequest
                  ? _submitRequest
                  : null,
              child: Text(
                  _isSubmittingRequest ? "Submitting..." : "Submit Request"),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHistory() {
    return _buildSectionCard(
      title: "Payout History",
      child: _requests.isEmpty
          ? const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Center(child: Text("No payout requests found yet.")),
            )
          : Column(
              children: _requests.map((item) {
                final statusColor = item.status == 1
                    ? Colors.green
                    : item.status == 2
                        ? Colors.red
                        : Colors.orange;

                return Container(
                  width: double.infinity,
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color.fromRGBO(248, 249, 251, 1),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: MyTheme.light_grey),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              item.amount ?? "--",
                              style: TextStyle(
                                color: MyTheme.dark_grey,
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: statusColor.withValues(alpha: .12),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Text(
                              item.statusLabel ?? "--",
                              style: TextStyle(
                                color: statusColor,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      _buildHistoryLine("Requested", item.requestedAt ?? "--"),
                      if ((item.processedAt ?? "").isNotEmpty)
                        _buildHistoryLine("Processed", item.processedAt ?? "--"),
                      if ((item.paymentMethod ?? "").isNotEmpty)
                        _buildHistoryLine("Method", item.paymentMethod ?? "--"),
                      if ((item.paymentReference ?? "").isNotEmpty)
                        _buildHistoryLine(
                            "Reference", item.paymentReference ?? "--"),
                      if ((item.message ?? "").isNotEmpty)
                        _buildHistoryLine("Message", item.message ?? "--"),
                      if ((item.adminNote ?? "").isNotEmpty)
                        _buildHistoryLine("Admin Note", item.adminNote ?? "--"),
                    ],
                  ),
                );
              }).toList(),
            ),
    );
  }

  Widget _buildHistoryLine(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: RichText(
        text: TextSpan(
          style: TextStyle(color: MyTheme.font_grey, fontSize: 12),
          children: [
            TextSpan(
              text: "$label: ",
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            TextSpan(text: value),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoLine(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: RichText(
        text: TextSpan(
          style: TextStyle(color: MyTheme.font_grey, fontSize: 12),
          children: [
            TextSpan(
              text: "$label: ",
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            TextSpan(text: value.isEmpty ? "--" : value),
          ],
        ),
      ),
    );
  }

  String _labelize(String? value) {
    if (value == null || value.isEmpty) return "--";
    return value
        .split('_')
        .map((part) => part.isEmpty
            ? part
            : part[0].toUpperCase() + part.substring(1))
        .join(' ');
  }

  Widget _buildSectionCard({required String title, required Widget child}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: .04),
            blurRadius: 16,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(
              color: MyTheme.dark_grey,
              fontSize: 16,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }

  InputDecoration _inputDecoration(String label) {
    return InputDecoration(
      labelText: label,
      filled: true,
      fillColor: const Color.fromRGBO(248, 249, 251, 1),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: MyTheme.light_grey),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: MyTheme.light_grey),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: MyTheme.accent_color_2),
      ),
    );
  }
}
