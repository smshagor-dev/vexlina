import 'dart:io';

import 'package:active_ecommerce_seller_app/custom/toast_component.dart';
import 'package:active_ecommerce_seller_app/data_model/product_mini_response.dart';
import 'package:active_ecommerce_seller_app/data_model/reels_response.dart';
import 'package:active_ecommerce_seller_app/my_theme.dart';
import 'package:active_ecommerce_seller_app/repositories/reels_repository.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_typeahead/flutter_typeahead.dart';
import 'package:toast/toast.dart';

class ReelsScreen extends StatefulWidget {
  const ReelsScreen({super.key});

  @override
  State<ReelsScreen> createState() => _ReelsScreenState();
}

class _ReelsScreenState extends State<ReelsScreen> {
  final ReelsRepository _repository = ReelsRepository();

  bool _isLoading = true;
  bool _canPost = false;
  List<ReelItem> _reels = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final permissions = await _repository.getPermissions();
      final myPosts = await _repository.getMyPosts();
      if (!mounted) return;
      setState(() {
        _canPost = permissions.data?.canPost ?? false;
        _reels = myPosts.data;
        _isLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      ToastComponent.showDialog(
        "Unable to load reels right now",
        gravity: Toast.center,
      );
    }
  }

  Future<void> _openCreateScreen() async {
    if (!_canPost) {
      ToastComponent.showDialog(
        "Your account cannot upload reels right now",
        gravity: Toast.center,
      );
      return;
    }

    final created = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => _CreateReelScreen(repository: _repository),
      ),
    );

    if (created == true) {
      _loadData();
    }
  }

  Future<void> _deleteReel(ReelItem reel) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Delete Reel"),
        content: const Text("Are you sure you want to remove this reel?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("Cancel"),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text("Delete"),
          ),
        ],
      ),
    );

    if (confirm != true) {
      return;
    }

    final response = await _repository.deleteReel(reel.id);
    ToastComponent.showDialog(
      response.message?.toString() ?? "Reel updated",
      gravity: Toast.center,
    );

    if (response.result == true) {
      _loadData();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("My Reels"),
        backgroundColor: Colors.white,
        foregroundColor: MyTheme.app_accent_color,
        elevation: 2,
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _reels.isEmpty
                ? ListView(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                    children: [
                      _uploadActionSection(),
                      const SizedBox(height: 70),
                      const Icon(Icons.ondemand_video_rounded,
                          size: 56, color: Colors.grey),
                      const SizedBox(height: 16),
                      const Center(
                        child: Text(
                          "No reels in your studio yet",
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.symmetric(horizontal: 24),
                          child: Text(
                            "Upload your first short video to start building your reels library.",
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                    ],
                  )
                : ListView.separated(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                    itemCount: _reels.length + 1,
                    separatorBuilder: (_, __) => const SizedBox(height: 14),
                    itemBuilder: (context, index) {
                      if (index == 0) {
                        return _uploadActionSection();
                      }

                      final reel = _reels[index - 1];
                      return Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.06),
                              blurRadius: 16,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            ClipRRect(
                              borderRadius: const BorderRadius.vertical(
                                top: Radius.circular(16),
                              ),
                              child: reel.thumbnailUrl != null &&
                                      reel.thumbnailUrl!.isNotEmpty
                                  ? Image.network(
                                      reel.thumbnailUrl!,
                                      height: 190,
                                      width: double.infinity,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) =>
                                          _placeholderHeader(),
                                    )
                                  : _placeholderHeader(),
                            ),
                            Padding(
                              padding: const EdgeInsets.all(14),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    reel.caption.isEmpty
                                        ? "No caption added"
                                        : reel.caption,
                                    maxLines: 3,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  Wrap(
                                    spacing: 8,
                                    runSpacing: 8,
                                    children: [
                                      _statChip(
                                          Icons.remove_red_eye_outlined,
                                          "${reel.viewsCount} views"),
                                      _statChip(Icons.favorite_border,
                                          "${reel.likesCount} likes"),
                                      _statChip(Icons.mode_comment_outlined,
                                          "${reel.commentsCount} comments"),
                                      if (reel.product != null)
                                        _statChip(Icons.shopping_bag_outlined,
                                            reel.product!.name),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          reel.createdAt ?? "",
                                          style: const TextStyle(
                                            color: Colors.black54,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                      TextButton.icon(
                                        onPressed: () => _deleteReel(reel),
                                        icon: const Icon(Icons.delete_outline),
                                        label: const Text("Delete"),
                                        style: TextButton.styleFrom(
                                          foregroundColor: Colors.red,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
      ),
    );
  }

  Widget _placeholderHeader() {
    return Container(
      height: 190,
      width: double.infinity,
      color: const Color(0xffeef3ff),
      child: const Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.play_circle_outline_rounded,
              size: 52, color: Color(0xff3766d4)),
          SizedBox(height: 10),
          Text("Reel Preview", style: TextStyle(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  Widget _uploadActionSection() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [
            Color(0xff2e294e),
            Color(0xff4b4376),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.16),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(
              Icons.video_call_rounded,
              color: Colors.white,
              size: 28,
            ),
          ),
          const SizedBox(width: 14),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "Upload Reels",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  "Publish a new short video for your store.",
                  style: TextStyle(
                    color: Color(0xffdfdef0),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          ElevatedButton(
            onPressed: _openCreateScreen,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: MyTheme.app_accent_color,
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text(
              "Upload",
              style: TextStyle(fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }

  Widget _statChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: const Color(0xfff4f7fb),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: MyTheme.app_accent_color),
          const SizedBox(width: 6),
          ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 160),
            child: Text(
              text,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 12),
            ),
          ),
        ],
      ),
    );
  }
}

class _CreateReelScreen extends StatefulWidget {
  const _CreateReelScreen({required this.repository});

  final ReelsRepository repository;

  @override
  State<_CreateReelScreen> createState() => _CreateReelScreenState();
}

class _CreateReelScreenState extends State<_CreateReelScreen> {
  final TextEditingController _captionController = TextEditingController();
  final TextEditingController _productSearchController =
      TextEditingController();

  File? _videoFile;
  File? _thumbnailFile;
  MiniProduct? _selectedProduct;
  bool _allowComments = true;
  bool _isSubmitting = false;

  Future<void> _pickVideo() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: const ["mp4", "mov", "avi", "webm", "mkv"],
    );

    if (result == null || result.files.single.path == null) {
      return;
    }

    setState(() {
      _videoFile = File(result.files.single.path!);
    });
  }

  Future<void> _pickThumbnail() async {
    final result = await FilePicker.platform.pickFiles(type: FileType.image);
    if (result == null || result.files.single.path == null) {
      return;
    }

    setState(() {
      _thumbnailFile = File(result.files.single.path!);
    });
  }

  Future<void> _submit() async {
    if (_videoFile == null) {
      ToastComponent.showDialog(
        "Please choose a reel video",
        gravity: Toast.center,
      );
      return;
    }

    setState(() => _isSubmitting = true);
    final response = await widget.repository.createReel(
      videoFile: _videoFile!,
      thumbnailFile: _thumbnailFile,
      caption: _captionController.text,
      productId: _selectedProduct?.id,
      allowComments: _allowComments,
    );
    if (!mounted) return;
    setState(() => _isSubmitting = false);

    ToastComponent.showDialog(
      response.message?.toString() ?? "Request completed",
      gravity: Toast.center,
      duration: Toast.lengthLong,
    );

    if (response.result == true) {
      Navigator.pop(context, true);
    }
  }

  @override
  void dispose() {
    _captionController.dispose();
    _productSearchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Upload Reel"),
        backgroundColor: Colors.white,
        foregroundColor: MyTheme.app_accent_color,
        elevation: 2,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _pickerCard(
              title: "Reel Video",
              subtitle: _videoFile?.path.split(Platform.pathSeparator).last ??
                  "Choose a short vertical or square video",
              icon: Icons.video_library_outlined,
              buttonText: "Select Video",
              onTap: _pickVideo,
            ),
            const SizedBox(height: 14),
            _pickerCard(
              title: "Thumbnail",
              subtitle: _thumbnailFile?.path.split(Platform.pathSeparator).last ??
                  "Optional cover image for your reel",
              icon: Icons.image_outlined,
              buttonText: "Select Thumbnail",
              onTap: _pickThumbnail,
            ),
            const SizedBox(height: 18),
            const Text(
              "Caption",
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _captionController,
              minLines: 3,
              maxLines: 5,
              decoration: InputDecoration(
                hintText: "Write something about this reel",
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
            const SizedBox(height: 18),
            const Text(
              "Link Product",
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            TypeAheadField<MiniProduct>(
              controller: _productSearchController,
              suggestionsCallback: (search) async {
                if (search.trim().length < 2) {
                  return <MiniProduct>[];
                }
                final response = await widget.repository.searchProducts(search);
                return response.products ?? <MiniProduct>[];
              },
              itemBuilder: (context, MiniProduct product) {
                return ListTile(
                  leading: product.thumbnailImage != null
                      ? Image.network(
                          product.thumbnailImage!,
                          width: 40,
                          height: 40,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) =>
                              const Icon(Icons.inventory_2_outlined),
                        )
                      : const Icon(Icons.inventory_2_outlined),
                  title: Text(product.name ?? ""),
                  subtitle: Text(product.mainPrice ?? ""),
                );
              },
              onSelected: (MiniProduct product) {
                _selectedProduct = product;
                _productSearchController.text = product.name ?? "";
                setState(() {});
              },
              builder: (context, controller, focusNode) {
                return TextField(
                  controller: controller,
                  focusNode: focusNode,
                  decoration: InputDecoration(
                    hintText: "Search product by name",
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    suffixIcon: _selectedProduct != null
                        ? IconButton(
                            onPressed: () {
                              _selectedProduct = null;
                              controller.clear();
                              setState(() {});
                            },
                            icon: const Icon(Icons.close),
                          )
                        : null,
                  ),
                );
              },
            ),
            if (_selectedProduct != null) ...[
              const SizedBox(height: 10),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xfff4f7fb),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.check_circle_outline,
                        color: Color(0xff2f6bff), size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        "${_selectedProduct!.name} linked",
                        style: const TextStyle(fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 18),
            SwitchListTile(
              value: _allowComments,
              onChanged: (value) => setState(() => _allowComments = value),
              activeThumbColor: MyTheme.app_accent_color,
              contentPadding: EdgeInsets.zero,
              title: const Text(
                "Allow comments",
                style: TextStyle(fontWeight: FontWeight.w700),
              ),
              subtitle: const Text(
                "Turn this off if you only want to publish the reel without comments.",
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _isSubmitting ? null : _submit,
                icon: _isSubmitting
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.publish_rounded),
                label: Text(_isSubmitting ? "Publishing..." : "Publish Reel"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: MyTheme.app_accent_color,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _pickerCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required String buttonText,
    required VoidCallback onTap,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 14,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: const Color(0xffeef3ff),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(icon, color: MyTheme.app_accent_color),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Colors.black54),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          OutlinedButton(
            onPressed: onTap,
            child: Text(buttonText),
          ),
        ],
      ),
    );
  }
}
