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
import 'package:video_player/video_player.dart';

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
        builder: (_) => _CreateOrEditReelScreen(repository: _repository),
      ),
    );

    if (created == true) {
      await _loadData();
    }
  }

  Future<void> _openEditScreen(ReelItem reel) async {
    final updated = await Navigator.push<ReelItem>(
      context,
      MaterialPageRoute(
        builder:
            (_) => _CreateOrEditReelScreen(repository: _repository, reel: reel),
      ),
    );

    if (updated != null && mounted) {
      setState(() {
        final index = _reels.indexWhere((item) => item.id == updated.id);
        if (index >= 0) {
          _reels[index] = updated;
        }
      });
    }
  }

  Future<void> _openPreview(ReelItem reel) async {
    await Navigator.push<void>(
      context,
      MaterialPageRoute(builder: (_) => _ReelPreviewScreen(reel: reel)),
    );
  }

  Future<void> _deleteReel(ReelItem reel) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder:
          (context) => AlertDialog(
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
    if (!mounted) return;

    ToastComponent.showDialog(
      response.message?.toString() ?? "Reel deleted",
      gravity: Toast.center,
    );

    if (response.result == true) {
      setState(() {
        _reels.removeWhere((item) => item.id == reel.id);
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F7FB),
      appBar: AppBar(
        title: const Text("Reels"),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF16171B),
        elevation: 0.5,
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child:
            _isLoading
                ? const Center(child: CircularProgressIndicator())
                : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
                  itemCount: _reels.isEmpty ? 2 : _reels.length + 1,
                  separatorBuilder: (_, __) => const SizedBox(height: 14),
                  itemBuilder: (context, index) {
                    if (index == 0) {
                      return _SellerReelsHeaderCard(
                        canUpload: _canPost,
                        onUpload: _openCreateScreen,
                      );
                    }

                    if (_reels.isEmpty) {
                      return Container(
                        padding: const EdgeInsets.all(28),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(22),
                          border: Border.all(color: const Color(0xFFEAECF0)),
                        ),
                        child: const Column(
                          children: [
                            Icon(
                              Icons.ondemand_video_outlined,
                              size: 48,
                              color: Color(0xFF98A2B3),
                            ),
                            SizedBox(height: 14),
                            Text(
                              "No reels uploaded yet",
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF101828),
                              ),
                            ),
                            SizedBox(height: 8),
                            Text(
                              "Upload short product videos, manage linked products, and keep your store reels fresh.",
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                color: Color(0xFF667085),
                                height: 1.45,
                              ),
                            ),
                          ],
                        ),
                      );
                    }

                    final reel = _reels[index - 1];
                    return _SellerReelCard(
                      reel: reel,
                      onOpen: () => _openPreview(reel),
                      onEdit: () => _openEditScreen(reel),
                      onDelete: () => _deleteReel(reel),
                    );
                  },
                ),
      ),
    );
  }
}

class _SellerReelsHeaderCard extends StatelessWidget {
  const _SellerReelsHeaderCard({
    required this.canUpload,
    required this.onUpload,
  });

  final bool canUpload;
  final VoidCallback onUpload;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1D2340), Color(0xFF33406E)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(
              Icons.video_collection_outlined,
              color: Colors.white,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  "Manage your reels",
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 17,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  canUpload
                      ? "Upload, edit description, change linked product, turn comments on or off, and delete anytime."
                      : "Your seller account cannot upload reels right now.",
                  style: const TextStyle(
                    color: Color(0xFFD9E0F2),
                    fontSize: 12.5,
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          ElevatedButton(
            onPressed: canUpload ? onUpload : null,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: const Color(0xFF16171B),
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
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
}

class _SellerReelCard extends StatelessWidget {
  const _SellerReelCard({
    required this.reel,
    required this.onOpen,
    required this.onEdit,
    required this.onDelete,
  });

  final ReelItem reel;
  final VoidCallback onOpen;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onOpen,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0xFFEAECF0)),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF101828).withValues(alpha: 0.05),
                blurRadius: 20,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(14),
                    child:
                        reel.thumbnailUrl != null &&
                                reel.thumbnailUrl!.isNotEmpty
                            ? Image.network(
                              reel.thumbnailUrl!,
                              width: 50,
                              height: 50,
                              fit: BoxFit.cover,
                              errorBuilder:
                                  (_, __, ___) =>
                                      _reelPreviewPlaceholder(size: 50),
                            )
                            : _reelPreviewPlaceholder(size: 50),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          reel.caption.isEmpty
                              ? "No description added"
                              : reel.caption,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 14.5,
                            fontWeight: FontWeight.w700,
                            height: 1.35,
                            color: Color(0xFF101828),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          reel.product?.name ?? "No linked product",
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF667085),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _infoChip(
                    Icons.remove_red_eye_outlined,
                    "${reel.viewsCount} views",
                  ),
                  _infoChip(
                    Icons.favorite_border_rounded,
                    "${reel.likesCount} reacts",
                  ),
                  _infoChip(
                    Icons.mode_comment_outlined,
                    reel.allowComments
                        ? "${reel.commentsCount} comments"
                        : "Comments off",
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                reel.createdAt ?? "",
                style: const TextStyle(
                  color: Color(0xFF98A2B3),
                  fontSize: 11.5,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onEdit,
                      icon: const Icon(Icons.edit_outlined, size: 18),
                      label: const Text(
                        "Edit Reel",
                        style: TextStyle(fontWeight: FontWeight.w600),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF475467),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        side: const BorderSide(color: Color(0xFFD0D5DD)),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onDelete,
                      icon: const Icon(Icons.delete_outline, size: 18),
                      label: const Text(
                        "Delete",
                        style: TextStyle(fontWeight: FontWeight.w600),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFFD92D20),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        side: const BorderSide(color: Color(0xFFFDA29B)),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _CreateOrEditReelScreen extends StatefulWidget {
  const _CreateOrEditReelScreen({required this.repository, this.reel});

  final ReelsRepository repository;
  final ReelItem? reel;

  bool get isEdit => reel != null;

  @override
  State<_CreateOrEditReelScreen> createState() =>
      _CreateOrEditReelScreenState();
}

class _CreateOrEditReelScreenState extends State<_CreateOrEditReelScreen> {
  final TextEditingController _captionController = TextEditingController();
  final TextEditingController _productSearchController =
      TextEditingController();

  File? _videoFile;
  File? _thumbnailFile;
  MiniProduct? _selectedProduct;
  Duration? _videoDuration;
  bool _allowComments = true;
  bool _isSubmitting = false;
  double _uploadProgress = 0;

  @override
  void initState() {
    super.initState();
    final reel = widget.reel;
    if (reel != null) {
      _captionController.text = reel.caption;
      _productSearchController.text = reel.product?.name ?? "";
      _allowComments = reel.allowComments;
      if (reel.product != null) {
        _selectedProduct = MiniProduct(
          id: reel.product!.id,
          slug: reel.product!.slug,
          name: reel.product!.name,
          thumbnailImage: reel.product!.thumbnailImage,
          mainPrice: reel.product!.price,
        );
      }
    }
  }

  @override
  void dispose() {
    _captionController.dispose();
    _productSearchController.dispose();
    super.dispose();
  }

  Future<void> _pickVideo() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: const ["mp4", "mov", "avi", "webm", "mkv"],
    );

    if (result == null || result.files.single.path == null) {
      return;
    }

    final file = File(result.files.single.path!);
    final controller = VideoPlayerController.file(file);

    try {
      await controller.initialize();
      final duration = controller.value.duration;
      if (!mounted) return;

      if (duration.inSeconds > 30) {
        ToastComponent.showDialog(
          "Please select a reel video within 30 seconds",
          gravity: Toast.center,
          duration: Toast.lengthLong,
        );
        return;
      }

      setState(() {
        _videoFile = file;
        _videoDuration = duration;
      });
    } catch (_) {
      if (!mounted) return;
      ToastComponent.showDialog(
        "Unable to read video duration",
        gravity: Toast.center,
      );
    } finally {
      await controller.dispose();
    }
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
    if (widget.isEdit) {
      await _saveEdit();
      return;
    }

    if (_videoFile == null) {
      ToastComponent.showDialog(
        "Please choose a reel video",
        gravity: Toast.center,
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
      _uploadProgress = 0;
    });

    final response = await widget.repository.createReel(
      videoFile: _videoFile!,
      thumbnailFile: _thumbnailFile,
      caption: _captionController.text,
      productId: _selectedProduct?.id,
      durationSeconds: _videoDuration?.inSeconds,
      allowComments: _allowComments,
      onProgress: (progress) {
        if (!mounted) return;
        setState(() => _uploadProgress = progress);
      },
    );

    if (!mounted) return;
    setState(() {
      _isSubmitting = false;
      _uploadProgress = 0;
    });

    ToastComponent.showDialog(
      response.message?.toString() ?? "Request completed",
      gravity: Toast.center,
      duration: Toast.lengthLong,
    );

    if (response.result == true) {
      Navigator.pop(context, true);
    }
  }

  Future<void> _saveEdit() async {
    final reel = widget.reel;
    if (reel == null) return;

    setState(() => _isSubmitting = true);
    final response = await widget.repository.updateReel(
      reelId: reel.id,
      caption: _captionController.text.trim(),
      productId: _selectedProduct?.id,
      allowComments: _allowComments,
    );

    if (!mounted) return;
    setState(() => _isSubmitting = false);

    if (!response.result || response.data == null) {
      ToastComponent.showDialog(
        response.message.isEmpty
            ? "Unable to update reel right now"
            : response.message,
        gravity: Toast.center,
      );
      return;
    }

    ToastComponent.showDialog(
      response.message.isEmpty ? "Reel updated successfully" : response.message,
      gravity: Toast.center,
    );
    Navigator.pop(context, response.data);
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.isEdit;

    return Scaffold(
      backgroundColor: const Color(0xFFF6F7FB),
      appBar: AppBar(
        title: Text(isEdit ? "Edit Reel" : "Upload Reel"),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF16171B),
        elevation: 0.5,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!isEdit) ...[
              _pickerCard(
                title: "Reel Video",
                subtitle:
                    _videoFile?.path.split(Platform.pathSeparator).last ??
                    "Choose a short video up to 30 seconds",
                icon: Icons.video_library_outlined,
                buttonText: "Select Video",
                onTap: _pickVideo,
              ),
              if (_videoDuration != null) ...[
                const SizedBox(height: 10),
                Text(
                  "Duration: ${_videoDuration!.inSeconds}s / 30s",
                  style: const TextStyle(
                    color: Color(0xFF667085),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
              const SizedBox(height: 14),
              _pickerCard(
                title: "Thumbnail",
                subtitle:
                    _thumbnailFile?.path.split(Platform.pathSeparator).last ??
                    "Optional cover image for your reel",
                icon: Icons.image_outlined,
                buttonText: "Select Thumbnail",
                onTap: _pickThumbnail,
              ),
              const SizedBox(height: 16),
              if (_isSubmitting) ...[
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE4E7EC)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "Uploading reel... ${(_uploadProgress * 100).round()}%",
                        style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF101828),
                        ),
                      ),
                      const SizedBox(height: 10),
                      LinearProgressIndicator(
                        value: _uploadProgress <= 0 ? null : _uploadProgress,
                        minHeight: 8,
                        borderRadius: BorderRadius.circular(999),
                        backgroundColor: const Color(0xFFEAECF0),
                        color: MyTheme.app_accent_color,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],
            ],
            _sectionCard(
              title: "Description",
              child: TextField(
                controller: _captionController,
                minLines: 4,
                maxLines: 6,
                decoration: InputDecoration(
                  hintText: "Write something about this reel",
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide.none,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 14),
            _sectionCard(
              title: "Linked product",
              subtitle: "Select a product to connect with this reel.",
              child: TypeAheadField<MiniProduct>(
                controller: _productSearchController,
                suggestionsCallback: (search) async {
                  if (search.trim().length < 2) {
                    return <MiniProduct>[];
                  }
                  final response = await widget.repository.searchProducts(
                    search,
                  );
                  return response.products ?? <MiniProduct>[];
                },
                itemBuilder: (context, MiniProduct product) {
                  return ListTile(
                    leading:
                        product.thumbnailImage != null
                            ? Image.network(
                              product.thumbnailImage!,
                              width: 40,
                              height: 40,
                              fit: BoxFit.cover,
                              errorBuilder:
                                  (_, __, ___) =>
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
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      suffixIcon:
                          _selectedProduct != null
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
            ),
            if (_selectedProduct != null) ...[
              const SizedBox(height: 10),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF3EE),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Text(
                  "Selected product: ${_selectedProduct!.name}",
                  style: const TextStyle(
                    color: Color(0xFFFA3E00),
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
            const SizedBox(height: 14),
            _sectionCard(
              title: "Comments",
              child: SwitchListTile(
                value: _allowComments,
                onChanged: (value) => setState(() => _allowComments = value),
                activeThumbColor: MyTheme.app_accent_color,
                contentPadding: EdgeInsets.zero,
                title: const Text(
                  "Allow comments",
                  style: TextStyle(fontWeight: FontWeight.w700),
                ),
                subtitle: const Text("Turn comments on or off for this reel."),
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _isSubmitting ? null : _submit,
                icon:
                    _isSubmitting && isEdit
                        ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                        : Icon(
                          isEdit ? Icons.save_outlined : Icons.publish_rounded,
                        ),
                label: Text(
                  isEdit
                      ? (_isSubmitting ? "Saving..." : "Save Changes")
                      : (_isSubmitting ? "Uploading..." : "Publish Reel"),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: MyTheme.app_accent_color,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _sectionCard({
    required String title,
    required Widget child,
    String? subtitle,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFDFDFE),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFEAECF0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
          ),
          if (subtitle != null) ...[
            const SizedBox(height: 6),
            Text(
              subtitle,
              style: const TextStyle(
                color: Color(0xFF667085),
                fontSize: 12.5,
                height: 1.45,
              ),
            ),
          ],
          const SizedBox(height: 12),
          child,
        ],
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
        border: Border.all(color: const Color(0xFFEAECF0)),
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
              color: const Color(0xFFEEF3FF),
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
          OutlinedButton(onPressed: onTap, child: Text(buttonText)),
        ],
      ),
    );
  }
}

class _ReelPreviewScreen extends StatefulWidget {
  const _ReelPreviewScreen({required this.reel});

  final ReelItem reel;

  @override
  State<_ReelPreviewScreen> createState() => _ReelPreviewScreenState();
}

class _ReelPreviewScreenState extends State<_ReelPreviewScreen> {
  VideoPlayerController? _controller;
  bool _isReady = false;

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    final controller = VideoPlayerController.networkUrl(
      Uri.parse(widget.reel.videoUrl),
    );
    _controller = controller;
    await controller.initialize();
    await controller.setLooping(true);
    await controller.play();
    if (!mounted) return;
    setState(() => _isReady = true);
  }

  void _togglePlayback() {
    final controller = _controller;
    if (controller == null || !_isReady) return;
    if (controller.value.isPlaying) {
      controller.pause();
    } else {
      controller.play();
    }
    setState(() {});
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final reel = widget.reel;

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text("Reel Preview"),
      ),
      body: Column(
        children: [
          Expanded(
            child: GestureDetector(
              onTap: _togglePlayback,
              child: Center(
                child:
                    _isReady && _controller != null
                        ? AspectRatio(
                          aspectRatio: _controller!.value.aspectRatio,
                          child: VideoPlayer(_controller!),
                        )
                        : const CircularProgressIndicator(color: Colors.white),
              ),
            ),
          ),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.08),
              border: Border(
                top: BorderSide(color: Colors.white.withValues(alpha: 0.08)),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  reel.caption.isEmpty ? "No description added" : reel.caption,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  reel.product?.name ?? "No linked product",
                  style: const TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 10),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _darkInfoChip(
                      Icons.remove_red_eye_outlined,
                      "${reel.viewsCount} views",
                    ),
                    _darkInfoChip(
                      Icons.favorite_border_rounded,
                      "${reel.likesCount} reacts",
                    ),
                    _darkInfoChip(
                      Icons.mode_comment_outlined,
                      reel.allowComments
                          ? "${reel.commentsCount} comments"
                          : "Comments off",
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

Widget _reelPreviewPlaceholder({double size = 50}) {
  return Container(
    width: size,
    height: size,
    decoration: BoxDecoration(
      color: const Color(0xFFF2F4F7),
      borderRadius: BorderRadius.circular(14),
    ),
    child: Icon(
      Icons.play_circle_outline_rounded,
      size: size * 0.52,
      color: const Color(0xFF475467),
    ),
  );
}

Widget _infoChip(IconData icon, String text) {
  return Container(
    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
    decoration: BoxDecoration(
      color: const Color(0xFFF8FAFC),
      borderRadius: BorderRadius.circular(999),
      border: Border.all(color: const Color(0xFFEAECF0)),
    ),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: const Color(0xFF475467)),
        const SizedBox(width: 6),
        ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 160),
          child: Text(
            text,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 12,
              color: Color(0xFF344054),
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
      ],
    ),
  );
}

Widget _darkInfoChip(IconData icon, String text) {
  return Container(
    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
    decoration: BoxDecoration(
      color: Colors.white.withValues(alpha: 0.08),
      borderRadius: BorderRadius.circular(999),
      border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
    ),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: Colors.white70),
        const SizedBox(width: 6),
        Text(
          text,
          style: const TextStyle(
            fontSize: 12,
            color: Colors.white,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    ),
  );
}
