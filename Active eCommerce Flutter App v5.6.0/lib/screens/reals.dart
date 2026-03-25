import 'dart:async';
import 'dart:io';

import 'package:active_ecommerce_cms_demo_app/custom/toast_component.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/product_mini_response.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/reels_response.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/reels_repository.dart';
import 'package:active_ecommerce_cms_demo_app/screens/auth/login.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/product_details/product_details.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import 'package:video_player/video_player.dart';

class RealsScreen extends StatefulWidget {
  const RealsScreen({super.key, this.openComposerOnLoad = false});

  final bool openComposerOnLoad;

  @override
  State<RealsScreen> createState() => _RealsScreenState();
}

class _RealsScreenState extends State<RealsScreen> {
  final ReelsRepository _repository = ReelsRepository();
  final PageController _pageController = PageController();
  final String _deviceKey = "${user_id.$ ?? 0}-${DateTime.now().year}";

  List<ReelItem> _reels = [];
  bool _isLoading = true;
  bool _isRefreshing = false;
  bool _canPost = false;
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadFeed();
  }

  Future<void> _loadFeed({bool showLoader = true}) async {
    if (showLoader) {
      setState(() => _isLoading = true);
    }
    try {
      final feed = await _repository.getFeed();
      bool canPost = feed.meta?.canPost ?? false;
      if (is_logged_in.$) {
        final permissions = await _repository.getPermissions();
        canPost = permissions.data?.canPost ?? canPost;
      }

      if (!mounted) return;
      setState(() {
        _reels = feed.data;
        _canPost = canPost;
        _isLoading = false;
      });

      if (widget.openComposerOnLoad && _canPost) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _openCreateSheet();
          }
        });
      }

      if (_reels.isNotEmpty) {
        unawaited(_recordView(0));
      }
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      ToastComponent.showDialog("Unable to load reels right now");
    }
  }

  Future<void> _refresh() async {
    setState(() => _isRefreshing = true);
    await _loadFeed(showLoader: false);
    if (mounted) {
      setState(() => _isRefreshing = false);
    }
  }

  Future<void> _recordView(int index) async {
    if (index < 0 || index >= _reels.length) return;
    final count = await _repository.recordView(
      _reels[index].id,
      deviceKey: _deviceKey,
    );
    if (!mounted || count <= 0) return;
    setState(() {
      _reels[index] = _reels[index].copyWith(viewsCount: count);
    });
  }

  Future<void> _toggleLike(int index) async {
    if (!_requireLogin()) return;
    final reel = _reels[index];
    final nextLiked = !reel.isLiked;
    final nextCount = nextLiked ? reel.likesCount + 1 : reel.likesCount - 1;
    setState(() {
      _reels[index] = reel.copyWith(
        isLiked: nextLiked,
        likesCount: nextCount < 0 ? 0 : nextCount,
      );
    });

    final response = await _repository.toggleLike(reel.id);
    if (!response.result && mounted) {
      setState(() => _reels[index] = reel);
      ToastComponent.showDialog(response.message);
    }
  }

  Future<void> _toggleSave(int index) async {
    if (!_requireLogin()) return;
    final reel = _reels[index];
    final nextSaved = !reel.isSaved;
    final nextCount = nextSaved ? reel.savesCount + 1 : reel.savesCount - 1;
    setState(() {
      _reels[index] = reel.copyWith(
        isSaved: nextSaved,
        savesCount: nextCount < 0 ? 0 : nextCount,
      );
    });

    final response = await _repository.toggleSave(reel.id);
    if (!response.result && mounted) {
      setState(() => _reels[index] = reel);
      ToastComponent.showDialog(response.message);
    }
  }

  Future<void> _shareReel(int index) async {
    final reel = _reels[index];
    if (is_logged_in.$) {
      final sharesCount = await _repository.recordShare(reel.id);
      if (mounted && sharesCount > 0) {
        setState(() {
          _reels[index] = reel.copyWith(sharesCount: sharesCount);
        });
      }
    }
    await Share.share(reel.link ?? reel.caption);
  }

  void _openComments(int index) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF14161B),
      builder: (context) => _ReelCommentsSheet(
        reel: _reels[index],
        repository: _repository,
        requireLogin: _requireLogin,
        onCommentAdded: (totalComments) {
          setState(() {
            _reels[index] = _reels[index].copyWith(
              commentsCount: totalComments,
            );
          });
        },
      ),
    );
  }

  Future<void> _openCreateSheet() async {
    if (!_requireLogin()) return;
    if (!_canPost) {
      ToastComponent.showDialog(
        "Only seller or classified package users can post reels",
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
      await _loadFeed(showLoader: false);
      ToastComponent.showDialog("Reel posted successfully");
    }
  }

  bool _requireLogin() {
    if (is_logged_in.$) return true;
    Navigator.push(context, MaterialPageRoute(builder: (_) => const Login()));
    return false;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0E1014),
      body: SafeArea(
        bottom: false,
        child: Stack(
          children: [
            if (_isLoading)
              const Center(
                child: CircularProgressIndicator(color: Colors.white),
              )
            else if (_reels.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 92,
                        height: 92,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.08),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.12),
                          ),
                        ),
                        child: const Icon(
                          Icons.play_circle_outline_rounded,
                          size: 52,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 20),
                      const Text(
                        "No reels available right now",
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Text(
                        _canPost
                            ? "Start with your first short video and link a product so customers can buy instantly."
                            : "Check back soon to watch product reels, reviews, and quick shopping clips.",
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: Colors.white60,
                          fontSize: 14,
                          height: 1.55,
                        ),
                      ),
                      if (_canPost) ...[
                        const SizedBox(height: 20),
                        ElevatedButton(
                          onPressed: _openCreateSheet,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFFA3E00),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(
                              horizontal: 22,
                              vertical: 14,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(999),
                            ),
                          ),
                          child: const Text(
                            "Upload Reels",
                            style: TextStyle(fontWeight: FontWeight.w700),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              )
            else
              RefreshIndicator(
                onRefresh: _refresh,
                color: Colors.white,
                backgroundColor: const Color(0xFF1A1D24),
                child: PageView.builder(
                  controller: _pageController,
                  scrollDirection: Axis.vertical,
                  itemCount: _reels.length,
                  onPageChanged: (index) {
                    setState(() => _currentIndex = index);
                    unawaited(_recordView(index));
                  },
                  itemBuilder: (context, index) {
                    return _ReelPage(
                      reel: _reels[index],
                      active: index == _currentIndex,
                      onLike: () => _toggleLike(index),
                      onSave: () => _toggleSave(index),
                      onComment: () => _openComments(index),
                      onShare: () => _shareReel(index),
                    );
                  },
                ),
              ),
            Positioned(
              top: 14,
              left: 16,
              right: 16,
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.32),
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: const Text(
                      "Reels",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  const Spacer(),
                  if (_isRefreshing)
                    const Padding(
                      padding: EdgeInsets.only(right: 12),
                      child: SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  if (_canPost)
                    GestureDetector(
                      onTap: _openCreateSheet,
                      child: Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: const Color(0xFFFA3E00),
                          borderRadius: BorderRadius.circular(22),
                        ),
                        child: const Icon(Icons.add, color: Colors.white),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ReelPage extends StatefulWidget {
  const _ReelPage({
    required this.reel,
    required this.active,
    required this.onLike,
    required this.onSave,
    required this.onComment,
    required this.onShare,
  });

  final ReelItem reel;
  final bool active;
  final VoidCallback onLike;
  final VoidCallback onSave;
  final VoidCallback onComment;
  final VoidCallback onShare;

  @override
  State<_ReelPage> createState() => _ReelPageState();
}

class _ReelPageState extends State<_ReelPage> {
  VideoPlayerController? _controller;
  bool _initialized = false;

  @override
  void initState() {
    super.initState();
    _initVideo();
  }

  @override
  void didUpdateWidget(covariant _ReelPage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.reel.videoUrl != widget.reel.videoUrl) {
      _disposeController();
      _initVideo();
      return;
    }

    if (_controller != null && _initialized) {
      if (widget.active) {
        _controller!.play();
      } else {
        _controller!.pause();
      }
    }
  }

  Future<void> _initVideo() async {
    _controller = VideoPlayerController.networkUrl(Uri.parse(widget.reel.videoUrl));
    await _controller!.initialize();
    await _controller!.setLooping(true);
    if (widget.active) {
      await _controller!.play();
    }
    if (mounted) {
      setState(() => _initialized = true);
    }
  }

  void _disposeController() {
    _controller?.pause();
    _controller?.dispose();
    _controller = null;
    _initialized = false;
  }

  @override
  void dispose() {
    _disposeController();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final reel = widget.reel;

    return Stack(
      fit: StackFit.expand,
      children: [
        Container(color: Colors.black),
        if (_initialized && _controller != null)
          FittedBox(
            fit: BoxFit.cover,
            child: SizedBox(
              width: _controller!.value.size.width,
              height: _controller!.value.size.height,
              child: VideoPlayer(_controller!),
            ),
          )
        else if (reel.thumbnailUrl != null && reel.thumbnailUrl!.isNotEmpty)
          Image.network(
            reel.thumbnailUrl!,
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => Container(color: Colors.black),
          )
        else
          Container(color: Colors.black),
        DecoratedBox(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [
                Colors.black.withValues(alpha: 0.18),
                Colors.transparent,
                Colors.black.withValues(alpha: 0.72),
              ],
            ),
          ),
        ),
        Positioned(
          left: 18,
          right: 88,
          bottom: 34,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: Colors.white12,
                    backgroundImage: reel.user.avatar != null &&
                            reel.user.avatar!.isNotEmpty
                        ? NetworkImage(reel.user.avatar!)
                        : null,
                    child: reel.user.avatar == null || reel.user.avatar!.isEmpty
                        ? const Icon(Icons.person, color: Colors.white)
                        : null,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      reel.user.name.isEmpty ? "Unknown user" : reel.user.name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
              if (reel.caption.isNotEmpty) ...[
                const SizedBox(height: 14),
                Text(
                  reel.caption,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    height: 1.45,
                  ),
                ),
              ],
              if (reel.product != null) ...[
                const SizedBox(height: 16),
                GestureDetector(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ProductDetails(slug: reel.product!.slug),
                      ),
                    );
                  },
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.14),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: Colors.white24),
                    ),
                    child: Row(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: reel.product!.thumbnailImage != null
                              ? Image.network(
                                  reel.product!.thumbnailImage!,
                                  width: 52,
                                  height: 52,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Container(
                                    width: 52,
                                    height: 52,
                                    color: Colors.white10,
                                  ),
                                )
                              : Container(
                                  width: 52,
                                  height: 52,
                                  color: Colors.white10,
                                ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                reel.product!.name,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                reel.product!.price,
                                style: const TextStyle(
                                  color: Color(0xFFFFC3B0),
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 14,
                            vertical: 10,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFA3E00),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: const Text(
                            "Buy Now",
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
        Positioned(
          right: 14,
          bottom: 36,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _ReelActionButton(
                icon: widget.reel.isLiked
                    ? Icons.favorite
                    : Icons.favorite_border_rounded,
                count: widget.reel.likesCount,
                active: widget.reel.isLiked,
                onTap: widget.onLike,
              ),
              const SizedBox(height: 18),
              _ReelActionButton(
                icon: Icons.mode_comment_outlined,
                count: widget.reel.commentsCount,
                onTap: widget.onComment,
              ),
              const SizedBox(height: 18),
              _ReelActionButton(
                icon: widget.reel.isSaved
                    ? Icons.bookmark
                    : Icons.bookmark_outline_rounded,
                count: widget.reel.savesCount,
                active: widget.reel.isSaved,
                onTap: widget.onSave,
              ),
              const SizedBox(height: 18),
              _ReelActionButton(
                icon: Icons.share_outlined,
                count: widget.reel.sharesCount,
                onTap: widget.onShare,
              ),
              const SizedBox(height: 18),
              _ReelActionButton(
                icon: Icons.visibility_outlined,
                count: widget.reel.viewsCount,
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _ReelActionButton extends StatelessWidget {
  const _ReelActionButton({
    required this.icon,
    required this.count,
    this.active = false,
    this.onTap,
  });

  final IconData icon;
  final int count;
  final bool active;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.28),
              shape: BoxShape.circle,
              border: Border.all(color: Colors.white24),
            ),
            child: Icon(
              icon,
              color: active ? const Color(0xFFFA3E00) : Colors.white,
              size: 28,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            count.toString(),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 12,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

class _ReelCommentsSheet extends StatefulWidget {
  const _ReelCommentsSheet({
    required this.reel,
    required this.repository,
    required this.requireLogin,
    required this.onCommentAdded,
  });

  final ReelItem reel;
  final ReelsRepository repository;
  final bool Function() requireLogin;
  final ValueChanged<int> onCommentAdded;

  @override
  State<_ReelCommentsSheet> createState() => _ReelCommentsSheetState();
}

class _ReelCommentsSheetState extends State<_ReelCommentsSheet> {
  final TextEditingController _commentController = TextEditingController();
  List<ReelCommentItem> _comments = [];
  bool _loading = true;
  bool _sending = false;

  @override
  void initState() {
    super.initState();
    _fetchComments();
  }

  Future<void> _fetchComments() async {
    final response = await widget.repository.getComments(widget.reel.id);
    if (!mounted) return;
    setState(() {
      _comments = response.data;
      _loading = false;
    });
  }

  Future<void> _submit() async {
    if (!widget.requireLogin()) return;
    if (_commentController.text.trim().isEmpty) return;
    setState(() => _sending = true);
    final response = await widget.repository.addComment(
      reelId: widget.reel.id,
      comment: _commentController.text.trim(),
    );
    if (!mounted) return;
    setState(() => _sending = false);

    if (response.result) {
      _commentController.clear();
      await _fetchComments();
      widget.onCommentAdded(_comments.length);
    } else {
      ToastComponent.showDialog(response.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: EdgeInsets.only(
          left: 16,
          right: 16,
          top: 16,
          bottom: MediaQuery.of(context).viewInsets.bottom + 16,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 46,
              height: 5,
              decoration: BoxDecoration(
                color: Colors.white24,
                borderRadius: BorderRadius.circular(999),
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              "Comments",
              style: TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 16),
            Flexible(
              child: _loading
                  ? const Center(
                      child: CircularProgressIndicator(color: Colors.white),
                    )
                  : _comments.isEmpty
                      ? const Padding(
                          padding: EdgeInsets.symmetric(vertical: 32),
                          child: Text(
                            "No comments yet",
                            style: TextStyle(color: Colors.white60),
                          ),
                        )
                      : ListView.separated(
                          shrinkWrap: true,
                          itemBuilder: (_, index) {
                            final comment = _comments[index];
                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  comment.user.name,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  comment.comment,
                                  style: const TextStyle(
                                    color: Colors.white70,
                                    height: 1.5,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  comment.createdAt,
                                  style: const TextStyle(
                                    color: Colors.white38,
                                    fontSize: 12,
                                  ),
                                ),
                              ],
                            );
                          },
                          separatorBuilder: (_, __) => const Divider(
                            color: Colors.white12,
                            height: 20,
                          ),
                          itemCount: _comments.length,
                        ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _commentController,
                    style: const TextStyle(color: Colors.white),
                    decoration: InputDecoration(
                      hintText: "Write a comment",
                      hintStyle: const TextStyle(color: Colors.white38),
                      filled: true,
                      fillColor: Colors.white10,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                GestureDetector(
                  onTap: _sending ? null : _submit,
                  child: Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: const Color(0xFFFA3E00),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: _sending
                        ? const Padding(
                            padding: EdgeInsets.all(12),
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.send_rounded, color: Colors.white),
                  ),
                ),
              ],
            ),
          ],
        ),
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
  bool _allowComments = true;
  bool _submitting = false;
  bool _searching = false;
  Product? _selectedProduct;
  List<Product> _searchResults = [];

  Future<void> _pickVideo() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: const ["mp4", "mov", "avi", "webm"],
    );
    final path = result?.files.single.path;
    if (path == null) return;
    setState(() => _videoFile = File(path));
  }

  Future<void> _pickThumbnail() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: const ["jpg", "jpeg", "png", "webp"],
    );
    final path = result?.files.single.path;
    if (path == null) return;
    setState(() => _thumbnailFile = File(path));
  }

  Future<void> _searchProducts() async {
    final query = _productSearchController.text.trim();
    if (query.isEmpty) return;
    setState(() => _searching = true);
    final response = await widget.repository.searchProducts(query);
    if (!mounted) return;
    setState(() {
      _searchResults = response.products ?? [];
      _searching = false;
    });
  }

  Future<void> _submit() async {
    if (_videoFile == null) {
      ToastComponent.showDialog("Please choose a video");
      return;
    }
    setState(() => _submitting = true);
    final response = await widget.repository.createReel(
      videoFile: _videoFile!,
      thumbnailFile: _thumbnailFile,
      caption: _captionController.text.trim(),
      productId: _selectedProduct?.id,
      allowComments: _allowComments,
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (response.result) {
      Navigator.pop(context, true);
    } else {
      ToastComponent.showDialog(response.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF7F4F0),
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF16171B),
        elevation: 0,
        title: const Text("Create Reel"),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _CreateTile(
              title: "Video",
              subtitle: _videoFile?.path.split(Platform.pathSeparator).last ??
                  "Choose reel video",
              buttonText: "Pick Video",
              onTap: _pickVideo,
            ),
            const SizedBox(height: 14),
            _CreateTile(
              title: "Thumbnail",
              subtitle: _thumbnailFile?.path
                      .split(Platform.pathSeparator)
                      .last ??
                  "Optional cover image",
              buttonText: "Pick Image",
              onTap: _pickThumbnail,
            ),
            const SizedBox(height: 14),
            const Text(
              "Caption",
              style: TextStyle(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _captionController,
              maxLines: 4,
              decoration: InputDecoration(
                hintText: "Tell viewers about this reel",
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(18),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              "Link Product",
              style: TextStyle(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _productSearchController,
                    decoration: InputDecoration(
                      hintText: "Search product name",
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(18),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                ElevatedButton(
                  onPressed: _searching ? null : _searchProducts,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFA3E00),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 16,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                  child: _searching
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text("Search"),
                ),
              ],
            ),
            if (_selectedProduct != null) ...[
              const SizedBox(height: 10),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFEEE8),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Text(
                  "Selected: ${_selectedProduct!.name}",
                  style: const TextStyle(
                    color: Color(0xFFFA3E00),
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
            if (_searchResults.isNotEmpty) ...[
              const SizedBox(height: 12),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                ),
                child: ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemBuilder: (_, index) {
                    final product = _searchResults[index];
                    return ListTile(
                      title: Text(product.name ?? ""),
                      subtitle: Text(product.mainPrice ?? ""),
                      trailing: _selectedProduct?.id == product.id
                          ? const Icon(
                              Icons.check_circle,
                              color: Color(0xFFFA3E00),
                            )
                          : null,
                      onTap: () {
                        setState(() => _selectedProduct = product);
                      },
                    );
                  },
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemCount: _searchResults.length,
                ),
              ),
            ],
            const SizedBox(height: 16),
            SwitchListTile(
              value: _allowComments,
              onChanged: (value) => setState(() => _allowComments = value),
              contentPadding: EdgeInsets.zero,
              activeThumbColor: const Color(0xFFFA3E00),
              title: const Text("Allow comments"),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _submitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFFA3E00),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
                child: _submitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text(
                        "Publish Reel",
                        style: TextStyle(fontWeight: FontWeight.w700),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CreateTile extends StatelessWidget {
  const _CreateTile({
    required this.title,
    required this.subtitle,
    required this.buttonText,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final String buttonText;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 6),
                Text(
                  subtitle,
                  style: const TextStyle(color: Colors.black54),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          ElevatedButton(
            onPressed: onTap,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFFFEEE8),
              foregroundColor: const Color(0xFFFA3E00),
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: Text(buttonText),
          ),
        ],
      ),
    );
  }
}
