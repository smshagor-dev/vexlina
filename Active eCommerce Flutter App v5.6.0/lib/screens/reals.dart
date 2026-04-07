import 'dart:async';
import 'dart:io';

import 'package:active_ecommerce_cms_demo_app/custom/toast_component.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/product_mini_response.dart';
import 'package:active_ecommerce_cms_demo_app/data_model/reels_response.dart';
import 'package:active_ecommerce_cms_demo_app/helpers/shared_value_helper.dart';
import 'package:active_ecommerce_cms_demo_app/main.dart';
import 'package:active_ecommerce_cms_demo_app/repositories/reels_repository.dart';
import 'package:active_ecommerce_cms_demo_app/screens/auth/login.dart';
import 'package:active_ecommerce_cms_demo_app/screens/product/product_details/product_details.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_typeahead/flutter_typeahead.dart';
import 'package:share_plus/share_plus.dart';
import 'package:video_player/video_player.dart';

class RealsScreen extends StatefulWidget {
  const RealsScreen({
    super.key,
    this.openComposerOnLoad = false,
    this.isVisible = true,
    this.initialReelId,
    this.showBackButton = false,
  });

  final bool openComposerOnLoad;
  final bool isVisible;
  final int? initialReelId;
  final bool showBackButton;

  @override
  State<RealsScreen> createState() => _RealsScreenState();
}

class _RealsScreenState extends State<RealsScreen>
    with WidgetsBindingObserver, RouteAware {
  final ReelsRepository _repository = ReelsRepository();
  final PageController _pageController = PageController();
  final String _deviceKey = "${user_id.$ ?? 0}-${DateTime.now().year}";

  List<ReelItem> _reels = [];
  bool _isLoading = true;
  bool _isRefreshing = false;
  bool _canPost = false;
  int _currentIndex = 0;
  bool _isRouteVisible = true;
  bool _isAppInForeground = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _loadFeed();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final route = ModalRoute.of(context);
    if (route is PageRoute<dynamic>) {
      routeObserver.unsubscribe(this);
      routeObserver.subscribe(this, route);
    }
  }

  @override
  void didUpdateWidget(covariant RealsScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.isVisible != widget.isVisible && widget.isVisible) {
      unawaited(_recordView(_currentIndex));
    }
  }

  bool get _isPlaybackAllowed =>
      widget.isVisible && _isRouteVisible && _isAppInForeground;

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final isForeground = state == AppLifecycleState.resumed;
    if (_isAppInForeground != isForeground && mounted) {
      setState(() => _isAppInForeground = isForeground);
    }
  }

  @override
  void didPush() {
    if (!_isRouteVisible && mounted) {
      setState(() => _isRouteVisible = true);
    }
  }

  @override
  void didPopNext() {
    if (!_isRouteVisible && mounted) {
      setState(() => _isRouteVisible = true);
      unawaited(_recordView(_currentIndex));
    }
  }

  @override
  void didPushNext() {
    if (_isRouteVisible && mounted) {
      setState(() => _isRouteVisible = false);
    }
  }

  @override
  void didPop() {
    if (_isRouteVisible && mounted) {
      setState(() => _isRouteVisible = false);
    }
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
        _currentIndex = _resolveInitialIndex(feed.data);
        _isLoading = false;
      });

      if (_reels.isNotEmpty && _currentIndex > 0) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted && _pageController.hasClients) {
            _pageController.jumpToPage(_currentIndex);
          }
        });
      }

      if (widget.openComposerOnLoad && _canPost) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _openCreateSheet();
          }
        });
      }

      if (_reels.isNotEmpty) {
        unawaited(_recordView(_currentIndex));
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

  int _resolveInitialIndex(List<ReelItem> reels) {
    final initialReelId = widget.initialReelId;
    if (initialReelId == null) {
      return 0;
    }

    final index = reels.indexWhere((item) => item.id == initialReelId);
    return index >= 0 ? index : 0;
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
                      active: _isPlaybackAllowed && index == _currentIndex,
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
                  if (widget.showBackButton) ...[
                    GestureDetector(
                      onTap: () => Navigator.of(context).maybePop(),
                      child: Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.32),
                          borderRadius: BorderRadius.circular(22),
                          border: Border.all(color: Colors.white24),
                        ),
                        child: const Icon(
                          Icons.arrow_back_ios_new_rounded,
                          color: Colors.white,
                          size: 18,
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                  ],
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

  @override
  void dispose() {
    routeObserver.unsubscribe(this);
    WidgetsBinding.instance.removeObserver(this);
    _pageController.dispose();
    super.dispose();
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
  bool _manuallyPaused = false;

  bool get _showWalletPricing =>
      wallet_system_status.$ && wallet_payment_discount_status.$;

  double? _extractAmount(String? raw) {
    if (raw == null || raw.trim().isEmpty) return null;

    final sanitized = raw.replaceAll(RegExp(r'[^0-9.]'), '');
    if (sanitized.isEmpty) return null;

    return double.tryParse(sanitized);
  }

  String _currencySymbolFrom(String? rawPrice) {
    if (rawPrice == null || rawPrice.isEmpty) return '';
    return rawPrice.replaceAll(RegExp(r'[0-9.,\\s]'), '');
  }

  String? _walletPriceFrom(String? rawPrice) {
    if (!_showWalletPricing) return null;

    final amount = _extractAmount(rawPrice);
    final discountPercent = wallet_payment_discount_percent.$;
    if (amount == null || amount <= 0 || discountPercent <= 0) {
      return null;
    }

    final discountedAmount = amount - ((amount * discountPercent) / 100);
    final symbol = _currencySymbolFrom(rawPrice);
    return '$symbol${discountedAmount.toStringAsFixed(2)}';
  }

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
      if (widget.active && !_manuallyPaused) {
        _controller!.play();
      } else {
        _controller!.pause();
      }
    }
  }

  Future<void> _initVideo() async {
    _controller = VideoPlayerController.networkUrl(
      Uri.parse(widget.reel.videoUrl),
    );
    await _controller!.initialize();
    await _controller!.setLooping(true);
    if (widget.active && !_manuallyPaused) {
      await _controller!.play();
    }
    if (mounted) {
      setState(() => _initialized = true);
    }
  }

  void _togglePlayback() {
    final controller = _controller;
    if (controller == null || !_initialized || !widget.active) return;

    if (controller.value.isPlaying) {
      controller.pause();
      setState(() => _manuallyPaused = true);
    } else {
      controller.play();
      setState(() => _manuallyPaused = false);
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
        GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: _togglePlayback,
          child: Stack(
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
              else if (reel.thumbnailUrl != null &&
                  reel.thumbnailUrl!.isNotEmpty)
                Image.network(
                  reel.thumbnailUrl!,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(color: Colors.black),
                )
              else
                Container(color: Colors.black),
              if (_initialized &&
                  _controller != null &&
                  widget.active &&
                  !_controller!.value.isPlaying)
                const Center(
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      color: Color(0x66000000),
                      shape: BoxShape.circle,
                    ),
                    child: Padding(
                      padding: EdgeInsets.all(18),
                      child: Icon(
                        Icons.play_arrow_rounded,
                        color: Colors.white,
                        size: 42,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ),
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
                    backgroundImage:
                        reel.user.avatar != null && reel.user.avatar!.isNotEmpty
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
                        builder: (_) =>
                            ProductDetails(slug: reel.product!.slug),
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
                                reel.product!.mainPrice.isNotEmpty
                                    ? reel.product!.mainPrice
                                    : reel.product!.price,
                                style: const TextStyle(
                                  color: Color(0xFFFFC3B0),
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              if (reel.product!.hasDiscount &&
                                  reel.product!.strokedPrice.isNotEmpty) ...[
                                const SizedBox(height: 3),
                                Row(
                                  children: [
                                    Flexible(
                                      child: Text(
                                        reel.product!.strokedPrice,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(
                                          color: Colors.white60,
                                          fontSize: 11,
                                          decoration:
                                              TextDecoration.lineThrough,
                                        ),
                                      ),
                                    ),
                                    if (reel.product!.discount.isNotEmpty) ...[
                                      const SizedBox(width: 6),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 6,
                                          vertical: 2,
                                        ),
                                        decoration: BoxDecoration(
                                          color: const Color(0x33FA3E00),
                                          borderRadius: BorderRadius.circular(
                                            999,
                                          ),
                                        ),
                                        child: Text(
                                          reel.product!.discount,
                                          style: const TextStyle(
                                            color: Color(0xFFFFC3B0),
                                            fontSize: 10,
                                            fontWeight: FontWeight.w700,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ],
                              if (_walletPriceFrom(
                                    reel.product!.mainPrice.isNotEmpty
                                        ? reel.product!.mainPrice
                                        : reel.product!.price,
                                  ) !=
                                  null) ...[
                                const SizedBox(height: 4),
                                Text(
                                  "Wallet pay ${_walletPriceFrom(reel.product!.mainPrice.isNotEmpty ? reel.product!.mainPrice : reel.product!.price)!}",
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
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
                      separatorBuilder: (_, __) =>
                          const Divider(color: Colors.white12, height: 20),
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
  Duration? _videoDuration;
  bool _allowComments = true;
  bool _submitting = false;
  bool _searching = false;
  double _uploadProgress = 0;
  Product? _selectedProduct;
  List<Product> _searchResults = [];

  Future<void> _pickVideo() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: const ["mp4", "mov", "avi", "webm"],
    );
    final path = result?.files.single.path;
    if (path == null) return;
    final file = File(path);
    final controller = VideoPlayerController.file(file);

    try {
      await controller.initialize();
      final duration = controller.value.duration;
      if (duration > const Duration(seconds: 30)) {
        ToastComponent.showDialog("Reels video must be 30 seconds or shorter");
        return;
      }

      if (!mounted) return;
      setState(() {
        _videoFile = file;
        _videoDuration = duration;
      });
    } catch (_) {
      ToastComponent.showDialog("Unable to read video duration");
    } finally {
      await controller.dispose();
    }
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
    if ((_videoDuration ?? Duration.zero) > const Duration(seconds: 30)) {
      ToastComponent.showDialog("Reels video must be 30 seconds or shorter");
      return;
    }
    setState(() => _submitting = true);
    final response = await widget.repository.createReel(
      videoFile: _videoFile!,
      thumbnailFile: _thumbnailFile,
      caption: _captionController.text.trim(),
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
      _submitting = false;
      _uploadProgress = 0;
    });

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
              subtitle: _videoFile == null
                  ? "Choose reel video"
                  : "${_videoFile!.path.split(Platform.pathSeparator).last}${_videoDuration != null ? " • ${_videoDuration!.inSeconds}s" : ""}",
              buttonText: "Pick Video",
              onTap: _pickVideo,
            ),
            const SizedBox(height: 14),
            _CreateTile(
              title: "Thumbnail",
              subtitle:
                  _thumbnailFile?.path.split(Platform.pathSeparator).last ??
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
            if (_submitting) ...[
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "Uploading reel... ${(_uploadProgress * 100).round()}%",
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF16171B),
                      ),
                    ),
                    const SizedBox(height: 10),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(999),
                      child: LinearProgressIndicator(
                        value: _uploadProgress <= 0 ? null : _uploadProgress,
                        minHeight: 10,
                        backgroundColor: const Color(0xFFFFEEE8),
                        valueColor: const AlwaysStoppedAnimation<Color>(
                          Color(0xFFFA3E00),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
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
                Text(subtitle, style: const TextStyle(color: Colors.black54)),
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

class MyReelsScreen extends StatefulWidget {
  const MyReelsScreen({super.key});

  @override
  State<MyReelsScreen> createState() => _MyReelsScreenState();
}

class _MyReelsScreenState extends State<MyReelsScreen> {
  final ReelsRepository _repository = ReelsRepository();

  bool _isLoading = true;
  bool _canUpload = false;
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
        _canUpload = (permissions.data?.classifiedPackageId ?? 0) > 0;
        _reels = myPosts.data;
        _isLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      ToastComponent.showDialog("Unable to load your reels right now");
    }
  }

  Future<void> _openCreateScreen() async {
    if (!_canUpload) {
      ToastComponent.showDialog("Only package customers can upload reels");
      return;
    }

    final created = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => _CreateReelScreen(repository: _repository),
      ),
    );

    if (created == true) {
      await _loadData();
      ToastComponent.showDialog("Reel posted successfully");
    }
  }

  Future<void> _openEditScreen(ReelItem reel) async {
    final updated = await Navigator.push<ReelItem?>(
      context,
      MaterialPageRoute(
        builder: (_) => _EditReelScreen(repository: _repository, reel: reel),
      ),
    );

    if (updated == null || !mounted) return;
    setState(() {
      final index = _reels.indexWhere((item) => item.id == updated.id);
      if (index != -1) {
        _reels[index] = updated;
      }
    });
  }

  Future<void> _deleteReel(ReelItem reel) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Delete Reel"),
        content: const Text("Are you sure you want to delete this reel?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("Cancel"),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text("Delete", style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final response = await _repository.deleteReel(reel.id);
    ToastComponent.showDialog(response.message);
    if (!response.result || !mounted) return;

    setState(() {
      _reels.removeWhere((item) => item.id == reel.id);
    });
  }

  void _openReel(ReelItem reel) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) =>
            RealsScreen(initialReelId: reel.id, showBackButton: true),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F7FB),
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF16171B),
        elevation: 0,
        title: const Text("My Reels"),
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : ListView.separated(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                itemBuilder: (_, index) {
                  if (index == 0) {
                    return _MyReelsHeaderCard(
                      canUpload: _canUpload,
                      onUpload: _openCreateScreen,
                    );
                  }

                  if (_reels.isEmpty) {
                    return Container(
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Column(
                        children: [
                          Icon(
                            Icons.ondemand_video_outlined,
                            size: 56,
                            color: Colors.black38,
                          ),
                          SizedBox(height: 16),
                          Text(
                            "You have not uploaded any reels yet",
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                    );
                  }

                  final reel = _reels[index - 1];
                  return _MyReelCard(
                    reel: reel,
                    onOpen: () => _openReel(reel),
                    onEdit: () => _openEditScreen(reel),
                    onDelete: () => _deleteReel(reel),
                  );
                },
                separatorBuilder: (_, __) => const SizedBox(height: 14),
                itemCount: _reels.isEmpty ? 2 : _reels.length + 1,
              ),
      ),
    );
  }
}

class _MyReelsHeaderCard extends StatelessWidget {
  const _MyReelsHeaderCard({required this.canUpload, required this.onUpload});

  final bool canUpload;
  final VoidCallback onUpload;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF171C2B), Color(0xFF2B3655)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
      ),
      child: Row(
        children: [
          Container(
            width: 54,
            height: 54,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(
              Icons.video_collection_outlined,
              color: Colors.white,
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
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  canUpload
                      ? "Upload, edit description, change linked product URL, turn comments on or off, and delete anytime."
                      : "Only customers with a purchased package can upload reels.",
                  style: const TextStyle(
                    color: Colors.white70,
                    fontSize: 12.5,
                    height: 1.45,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          ElevatedButton(
            onPressed: onUpload,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: const Color(0xFF16171B),
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: const Text("Upload"),
          ),
        ],
      ),
    );
  }
}

class _MyReelCard extends StatelessWidget {
  const _MyReelCard({
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
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
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
                    borderRadius: BorderRadius.circular(12),
                    child:
                        reel.thumbnailUrl != null &&
                            reel.thumbnailUrl!.isNotEmpty
                        ? Image.network(
                            reel.thumbnailUrl!,
                            width: 64,
                            height: 64,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) =>
                                _myReelPreviewPlaceholder(size: 64),
                          )
                        : _myReelPreviewPlaceholder(size: 64),
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
                            height: 1.3,
                            color: Color(0xFF101828),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          reel.product?.name ?? "No linked product",
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Color(0xFF667085),
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          reel.createdAt ?? "",
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Color(0xFF98A2B3),
                            fontSize: 11.5,
                            fontWeight: FontWeight.w500,
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
                  _myReelChip(
                    Icons.remove_red_eye_outlined,
                    "${reel.viewsCount} views",
                  ),
                  _myReelChip(
                    Icons.favorite_border_rounded,
                    "${reel.likesCount} reacts",
                  ),
                  _myReelChip(
                    Icons.mode_comment_outlined,
                    reel.allowComments
                        ? "${reel.commentsCount} comments"
                        : "Comments off",
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onEdit,
                      icon: const Icon(Icons.edit_outlined, size: 18),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF475467),
                        padding: const EdgeInsets.symmetric(vertical: 11),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        side: const BorderSide(color: Color(0xFFD0D5DD)),
                      ),
                      label: const Text(
                        "Edit Reel",
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onDelete,
                      icon: const Icon(Icons.delete_outline, size: 18),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFFD92D20),
                        padding: const EdgeInsets.symmetric(vertical: 11),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        side: const BorderSide(color: Color(0xFFFDA29B)),
                      ),
                      label: const Text(
                        "Delete",
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
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

Widget _myReelPreviewPlaceholder({double size = 190}) {
  return Container(
    height: size,
    width: size,
    decoration: BoxDecoration(
      color: const Color(0xFFF2F4F7),
      borderRadius: BorderRadius.circular(size <= 70 ? 12 : 0),
    ),
    child: Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(
          Icons.play_circle_outline_rounded,
          size: size <= 60 ? 24 : 52,
          color: Color(0xFF475467),
        ),
        if (size > 60) ...const [
          SizedBox(height: 10),
          Text(
            "Reel Preview",
            style: TextStyle(
              fontWeight: FontWeight.w600,
              color: Color(0xFF475467),
            ),
          ),
        ],
      ],
    ),
  );
}

Widget _myReelChip(IconData icon, String text) {
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

class _EditReelScreen extends StatefulWidget {
  const _EditReelScreen({required this.repository, required this.reel});

  final ReelsRepository repository;
  final ReelItem reel;

  @override
  State<_EditReelScreen> createState() => _EditReelScreenState();
}

class _EditReelScreenState extends State<_EditReelScreen> {
  late final TextEditingController _captionController;
  late final TextEditingController _productController;
  Product? _selectedProduct;
  bool _allowComments = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _captionController = TextEditingController(text: widget.reel.caption);
    _productController = TextEditingController(
      text: widget.reel.product?.name ?? "",
    );
    _allowComments = widget.reel.allowComments;
    if (widget.reel.product != null) {
      _selectedProduct = Product(
        id: widget.reel.product!.id,
        slug: widget.reel.product!.slug,
        name: widget.reel.product!.name,
        thumbnailImage: widget.reel.product!.thumbnailImage,
        mainPrice: widget.reel.product!.price,
      );
    }
  }

  @override
  void dispose() {
    _captionController.dispose();
    _productController.dispose();
    super.dispose();
  }

  Future<List<Product>> _searchProducts(String search) async {
    if (search.trim().length < 2) {
      return <Product>[];
    }
    final response = await widget.repository.searchProducts(search);
    return response.products ?? <Product>[];
  }

  Future<void> _save() async {
    setState(() => _isSaving = true);
    final response = await widget.repository.updateReel(
      reelId: widget.reel.id,
      caption: _captionController.text.trim(),
      productId: _selectedProduct?.id,
      allowComments: _allowComments,
    );
    if (!mounted) return;
    setState(() => _isSaving = false);

    if (!response.result || response.data == null) {
      ToastComponent.showDialog(
        response.message.isEmpty
            ? "Unable to update reel right now"
            : response.message,
      );
      return;
    }

    ToastComponent.showDialog(
      response.message.isEmpty ? "Reel updated successfully" : response.message,
    );
    Navigator.pop(context, response.data);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F7FB),
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF16171B),
        elevation: 0,
        title: const Text("Edit Reel"),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (widget.reel.link != null && widget.reel.link!.isNotEmpty) ...[
              _EditInfoCard(
                title: "Current reel URL",
                child: SelectableText(
                  widget.reel.link!,
                  style: const TextStyle(
                    color: Color(0xFF475467),
                    height: 1.45,
                  ),
                ),
              ),
              const SizedBox(height: 14),
            ],
            _EditInfoCard(
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
            _EditInfoCard(
              title: "Linked product / URL",
              subtitle:
                  "Changing the linked product updates the reel product URL.",
              child: Column(
                children: [
                  TypeAheadField<Product>(
                    controller: _productController,
                    suggestionsCallback: _searchProducts,
                    itemBuilder: (context, Product product) {
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
                    onSelected: (Product product) {
                      setState(() => _selectedProduct = product);
                      _productController.text = product.name ?? "";
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
                              _selectedProduct != null ||
                                  controller.text.trim().isNotEmpty
                              ? IconButton(
                                  onPressed: () {
                                    setState(() => _selectedProduct = null);
                                    controller.clear();
                                  },
                                  icon: const Icon(Icons.close),
                                )
                              : null,
                        ),
                      );
                    },
                  ),
                  if (_selectedProduct != null) ...[
                    const SizedBox(height: 12),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFEEE8),
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
                ],
              ),
            ),
            const SizedBox(height: 14),
            _EditInfoCard(
              title: "Comments",
              child: SwitchListTile(
                value: _allowComments,
                onChanged: (value) => setState(() => _allowComments = value),
                contentPadding: EdgeInsets.zero,
                title: const Text("Allow comments"),
                subtitle: const Text("Turn comments on or off for this reel."),
                activeThumbColor: const Color(0xFFFA3E00),
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isSaving ? null : _save,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFFA3E00),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
                child: _isSaving
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text(
                        "Save Changes",
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

class _EditInfoCard extends StatelessWidget {
  const _EditInfoCard({
    required this.title,
    required this.child,
    this.subtitle,
  });

  final String title;
  final String? subtitle;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFDFDFE),
        borderRadius: BorderRadius.circular(20),
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
              subtitle!,
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
}
