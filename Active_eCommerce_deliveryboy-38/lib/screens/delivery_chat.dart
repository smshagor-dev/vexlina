import 'dart:async';

import 'package:active_flutter_delivery_app/data_model/message_response.dart';
import 'package:active_flutter_delivery_app/helpers/shared_value_helper.dart';
import 'package:active_flutter_delivery_app/my_theme.dart';
import 'package:active_flutter_delivery_app/repositories/chat_repository.dart';
import 'package:active_flutter_delivery_app/services/calls_and_messages_service.dart';
import 'package:flutter/material.dart';

class DeliveryChat extends StatefulWidget {
  const DeliveryChat({
    super.key,
    required this.conversationId,
    required this.messengerName,
    this.messengerTitle,
    this.messengerPhone,
  });

  final int conversationId;
  final String messengerName;
  final String? messengerTitle;
  final String? messengerPhone;

  @override
  State<DeliveryChat> createState() => _DeliveryChatState();
}

class _DeliveryChatState extends State<DeliveryChat> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final CallsAndMessagesService _callsAndMessagesService =
      CallsAndMessagesService();

  List<Message> _messages = [];
  bool _isInitial = true;
  int _lastId = 0;
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    _fetchMessages();
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchMessages() async {
    final response = await ChatRepository().getMessageResponse(
      conversationId: widget.conversationId,
    );

    if (!mounted) {
      return;
    }

    _messages = response.data ?? [];
    _lastId = _messages.isNotEmpty ? (_messages.first.id ?? 0) : 0;
    _isInitial = false;
    setState(() {});
    _scheduleRefresh();
  }

  void _scheduleRefresh() {
    _refreshTimer?.cancel();
    _refreshTimer = Timer(const Duration(seconds: 5), _getNewMessages);
  }

  Future<void> _getNewMessages() async {
    if (!mounted) {
      return;
    }

    final response = await ChatRepository().getNewMessageResponse(
      conversationId: widget.conversationId,
      lastMessageId: _lastId,
    );

    final incoming = response.data ?? [];
    if (incoming.isNotEmpty) {
      _messages = [...incoming, ..._messages];
      _lastId = _messages.isNotEmpty ? (_messages.first.id ?? 0) : 0;
      setState(() {});
    }

    _scheduleRefresh();
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) {
      return;
    }

    _messageController.clear();

    final response = await ChatRepository().getInserMessageResponse(
      conversationId: widget.conversationId,
      message: text,
    );

    final created = response.data ?? [];
    if (created.isNotEmpty) {
      _messages = [...created, ..._messages];
      _lastId = _messages.isNotEmpty ? (_messages.first.id ?? 0) : 0;
      if (mounted) {
        setState(() {});
      }
    }
  }

  bool _isMine(Message message) => message.userId == user_id.$;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF7F7F7),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back, color: MyTheme.dark_grey),
          onPressed: () => Navigator.of(context).pop(),
        ),
        titleSpacing: 0,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.messengerName,
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: MyTheme.font_grey,
              ),
            ),
            if ((widget.messengerTitle ?? '').trim().isNotEmpty)
              Text(
                widget.messengerTitle!,
                style: TextStyle(fontSize: 11, color: MyTheme.grey_153),
              ),
          ],
        ),
        actions: [
          if ((widget.messengerPhone ?? '').trim().isNotEmpty)
            IconButton(
              onPressed: () =>
                  _callsAndMessagesService.call(widget.messengerPhone),
              icon: const Icon(Icons.call_rounded),
              color: MyTheme.red,
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: _isInitial
                ? const Center(child: CircularProgressIndicator())
                : _messages.isEmpty
                ? Center(
                    child: Text(
                      'Start the conversation with your customer.',
                      style: TextStyle(color: MyTheme.grey_153, fontSize: 13),
                    ),
                  )
                : ListView.builder(
                    controller: _scrollController,
                    reverse: true,
                    padding: const EdgeInsets.all(14),
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      final message = _messages[index];
                      final mine = _isMine(message);
                      return Align(
                        alignment: mine
                            ? Alignment.centerRight
                            : Alignment.centerLeft,
                        child: Container(
                          constraints: BoxConstraints(
                            maxWidth: MediaQuery.of(context).size.width * .72,
                          ),
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.fromLTRB(12, 10, 12, 8),
                          decoration: BoxDecoration(
                            color: mine ? MyTheme.red : Colors.white,
                            borderRadius: BorderRadius.only(
                              topLeft: const Radius.circular(14),
                              topRight: const Radius.circular(14),
                              bottomLeft: Radius.circular(mine ? 14 : 4),
                              bottomRight: Radius.circular(mine ? 4 : 14),
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: .05),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                message.message ?? '',
                                style: TextStyle(
                                  color: mine ? Colors.white : MyTheme.font_grey,
                                  fontSize: 13,
                                  height: 1.35,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                message.time ?? '',
                                style: TextStyle(
                                  color: mine
                                      ? Colors.white.withValues(alpha: .8)
                                      : MyTheme.grey_153,
                                  fontSize: 10,
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
          SafeArea(
            top: false,
            child: Container(
              padding: const EdgeInsets.fromLTRB(14, 8, 14, 12),
              color: Colors.white,
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      minLines: 1,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Type your message',
                        filled: true,
                        fillColor: const Color(0xffF1F1F1),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 12,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  SizedBox(
                    width: 46,
                    height: 46,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: MyTheme.red,
                        padding: EdgeInsets.zero,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                      onPressed: _sendMessage,
                      child: const Icon(Icons.send_rounded, color: Colors.white),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
