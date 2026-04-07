import 'package:active_flutter_delivery_app/custom/toast_component.dart';
import 'package:active_flutter_delivery_app/data_model/pickup_payout_summary_response.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/pickup_payout_repository.dart';
import 'package:flutter/material.dart';

class PickupPayoutInfo extends StatefulWidget {
  const PickupPayoutInfo({Key? key, this.showBackButton = true})
      : super(key: key);

  final bool showBackButton;

  @override
  State<PickupPayoutInfo> createState() => _PickupPayoutInfoState();
}

class _PickupPayoutInfoState extends State<PickupPayoutInfo> {
  final _accountNameController = TextEditingController();
  final _accountNumberController = TextEditingController();
  final _bankNameController = TextEditingController();
  final _branchNameController = TextEditingController();
  final _routingNumberController = TextEditingController();
  final _walletTypeController = TextEditingController();
  final _walletNumberController = TextEditingController();
  final _notesController = TextEditingController();

  String _payoutMethod = "bank";
  bool _isLoading = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _loadInfo();
  }

  Future<void> _loadInfo() async {
    setState(() {
      _isLoading = true;
    });

    final summary = await PickupPayoutRepository().getSummary();
    _hydrate(summary.pickupPoint);

    if (mounted) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _hydrate(PickupPayoutProfile? profile) {
    _payoutMethod = profile?.payoutMethod ?? "bank";
    _accountNameController.text = profile?.payoutAccountName ?? "";
    _accountNumberController.text = profile?.payoutAccountNumber ?? "";
    _bankNameController.text = profile?.payoutBankName ?? "";
    _branchNameController.text = profile?.payoutBranchName ?? "";
    _routingNumberController.text = profile?.payoutRoutingNumber ?? "";
    _walletTypeController.text = profile?.payoutMobileWalletType ?? "";
    _walletNumberController.text = profile?.payoutMobileWalletNumber ?? "";
    _notesController.text = profile?.payoutNotes ?? "";
  }

  Future<void> _saveInfo() async {
    if (_accountNameController.text.trim().isEmpty) {
      ToastComponent.showDialog("Account holder name is required.", context);
      return;
    }

    setState(() {
      _isSaving = true;
    });

    final response = await PickupPayoutRepository().updatePayoutInfo(
      payoutMethod: _payoutMethod,
      payoutAccountName: _accountNameController.text.trim(),
      payoutAccountNumber: _accountNumberController.text.trim(),
      payoutBankName: _bankNameController.text.trim(),
      payoutBranchName: _branchNameController.text.trim(),
      payoutRoutingNumber: _routingNumberController.text.trim(),
      payoutMobileWalletType: _walletTypeController.text.trim(),
      payoutMobileWalletNumber: _walletNumberController.text.trim(),
      payoutNotes: _notesController.text.trim(),
    );

    if (mounted) {
      setState(() {
        _isSaving = false;
      });
    }

    ToastComponent.showDialog(response.message ?? "Updated", context);
    if (response.result == true && mounted) {
      Navigator.of(context).pop(true);
    }
  }

  @override
  void dispose() {
    _accountNameController.dispose();
    _accountNumberController.dispose();
    _bankNameController.dispose();
    _branchNameController.dispose();
    _routingNumberController.dispose();
    _walletTypeController.dispose();
    _walletNumberController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color.fromRGBO(248, 249, 251, 1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: widget.showBackButton
            ? IconButton(
                onPressed: () => Navigator.of(context).pop(),
                icon: Icon(Icons.arrow_back, color: MyTheme.dark_grey),
              )
            : null,
        title: Text(
          "Payout Info",
          style: TextStyle(color: MyTheme.accent_color, fontSize: 16),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(12),
              children: [
                Container(
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
                        "Edit payout account information",
                        style: TextStyle(
                          color: MyTheme.dark_grey,
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 14),
                      DropdownButtonFormField<String>(
                        initialValue: _payoutMethod,
                        decoration: _inputDecoration("Payout Method"),
                        items: const [
                          DropdownMenuItem(value: "bank", child: Text("Bank")),
                          DropdownMenuItem(
                              value: "mobile_wallet",
                              child: Text("Mobile Wallet")),
                          DropdownMenuItem(
                              value: "manual", child: Text("Manual / Other")),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _payoutMethod = value ?? "bank";
                          });
                        },
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _accountNameController,
                        decoration: _inputDecoration("Account Holder Name"),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _accountNumberController,
                        decoration: _inputDecoration("Account / Wallet Number"),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _bankNameController,
                        decoration: _inputDecoration("Bank Name"),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _branchNameController,
                        decoration: _inputDecoration("Branch Name"),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _routingNumberController,
                        decoration: _inputDecoration("Routing Number"),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _walletTypeController,
                        decoration: _inputDecoration("Wallet Type"),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _walletNumberController,
                        decoration: _inputDecoration("Wallet Number"),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _notesController,
                        maxLines: 3,
                        decoration: _inputDecoration("Notes"),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: MyTheme.accent_color,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                          ),
                          onPressed: _isSaving ? null : _saveInfo,
                          child:
                              Text(_isSaving ? "Saving..." : "Save Payout Info"),
                        ),
                      ),
                    ],
                  ),
                ),
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
