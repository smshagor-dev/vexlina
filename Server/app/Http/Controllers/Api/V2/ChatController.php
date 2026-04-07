<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Conversation;
use App\Http\Resources\V2\ConversationCollection;
use App\Http\Resources\V2\MessageCollection;
use App\Mail\ConversationMailManager;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;

class ChatController extends Controller
{

    public function conversations()
    {
        $conversations = Conversation::with(['sender.shop', 'receiver.shop'])
            ->where(function ($query) {
                $query->where('sender_id', auth()->id())
                    ->orWhere('receiver_id', auth()->id());
            })
            ->latest('updated_at')
            ->paginate(10);
        return new ConversationCollection($conversations);
    }

    public function messages($id)
    {
        $this->authorizedConversation($id);
        $messages = Message::where('conversation_id', $id)->latest('id')->paginate(10);
        return new MessageCollection($messages);
    }

    public function insert_message(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        $conversation = $this->authorizedConversation($request->conversation_id);
        $message = new Message;
        $message->conversation_id = $request->conversation_id;
        $message->user_id = auth()->user()->id;
        $message->message = $request->message;
        $message->save();
        if ($conversation->sender_id == auth()->user()->id) {
            $conversation->sender_viewed = 1;
            $conversation->receiver_viewed = 0;
        } elseif ($conversation->receiver_id == auth()->user()->id) {
            $conversation->sender_viewed = 0;
            $conversation->receiver_viewed = 1;
        }
        $conversation->save();
        $messages = Message::where('id', $message->id)->paginate(1);
        return new MessageCollection($messages);
    }

    public function get_new_messages($conversation_id, $last_message_id)
    {
        $this->authorizedConversation($conversation_id);
        $messages = Message::where('conversation_id', $conversation_id)->where('id', '>', $last_message_id)->latest('id')->paginate(10);
        return new MessageCollection($messages);
    }

    public function create_conversation(Request $request)
    {
        $seller_user = Product::findOrFail($request->product_id)->user;
        $user = User::find(auth()->user()->id);
        $conversation = new Conversation;
        $conversation->sender_id = $user->id;
        $conversation->receiver_id = Product::findOrFail($request->product_id)->user->id;
        $conversation->title = $request->title;

        if ($conversation->save()) {
            $message = new Message;
            $message->conversation_id = $conversation->id;
            $message->user_id = $user->id;
            $message->message = $request->message;

            if ($message->save()) {
                $this->send_message_to_seller($conversation, $message, $seller_user, $user);
            }
        }

        return response()->json(['result' => true, 'conversation_id' => $conversation->id,
            'shop_name' => $conversation->receiver->user_type == 'admin' ? 'In House Product' : $conversation->receiver->shop->name,
            'shop_logo' => $conversation->receiver->user_type == 'admin' ? uploaded_asset(get_setting('header_logo'))  : uploaded_asset($conversation->receiver->shop->logo),
            'title'=> $conversation->title,
            'message' => translate("Conversation created"),]);
    }

    public function send_message_to_seller($conversation, $message, $seller_user, $user)
    {
        $array['view'] = 'emails.conversation';
        $array['subject'] = translate('Sender').':- '. $user->name;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = translate('Hi! You recieved a message from ') . $user->name . '.';
        $array['sender'] = $user->name;

        if ($seller_user->type == 'admin') {
            $array['link'] = route('conversations.admin_show', encrypt($conversation->id));
        } else {
            $array['link'] = route('conversations.show', encrypt($conversation->id));
        }

        $array['details'] = $message->message;

        try {
            Mail::to($conversation->receiver->email)->queue(new ConversationMailManager($array));
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }

    }

    public function createDeliveryConversation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $order = Order::with(['delivery_boy', 'user'])
            ->where('id', $request->order_id)
            ->where('user_id', auth()->user()->id)
            ->firstOrFail();

        if (!$order->assign_delivery_boy || !$order->delivery_boy) {
            return response()->json([
                'result' => false,
                'message' => translate('Delivery boy is not assigned yet'),
            ], 422);
        }

        $conversation = $this->findOrCreateDeliveryConversation($order, auth()->user(), $order->delivery_boy);

        return response()->json($this->deliveryConversationPayload($conversation, $order->delivery_boy, $order));
    }

    public function openDeliveryBoyConversation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $order = Order::with('user')
            ->where('id', $request->order_id)
            ->where('assign_delivery_boy', auth()->user()->id)
            ->firstOrFail();

        $conversation = $this->findOrCreateDeliveryConversation($order, auth()->user(), $order->user);

        return response()->json($this->deliveryConversationPayload($conversation, $order->user, $order));
    }

    protected function authorizedConversation($conversationId): Conversation
    {
        $conversation = Conversation::findOrFail($conversationId);

        abort_unless(
            in_array(auth()->user()->id, [$conversation->sender_id, $conversation->receiver_id]),
            403,
            translate('Unauthorized')
        );

        return $conversation;
    }

    protected function findOrCreateDeliveryConversation(Order $order, User $currentUser, User $otherUser): Conversation
    {
        $title = translate('Delivery Order') . ' #' . $order->code;

        $conversation = Conversation::where('title', $title)
            ->where(function ($query) use ($currentUser, $otherUser) {
                $query->where(function ($subQuery) use ($currentUser, $otherUser) {
                    $subQuery->where('sender_id', $currentUser->id)
                        ->where('receiver_id', $otherUser->id);
                })->orWhere(function ($subQuery) use ($currentUser, $otherUser) {
                    $subQuery->where('sender_id', $otherUser->id)
                        ->where('receiver_id', $currentUser->id);
                });
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $conversation = new Conversation();
        $conversation->sender_id = $currentUser->id;
        $conversation->receiver_id = $otherUser->id;
        $conversation->title = $title;
        $conversation->sender_viewed = 1;
        $conversation->receiver_viewed = 0;
        $conversation->save();

        return $conversation;
    }

    protected function deliveryConversationPayload(Conversation $conversation, User $participant, Order $order): array
    {
        return [
            'result' => true,
            'conversation_id' => $conversation->id,
            'shop_name' => $participant->name,
            'shop_logo' => uploaded_asset($participant->avatar_original),
            'participant_phone' => $participant->phone,
            'participant_type' => $participant->user_type,
            'title' => $conversation->title ?? (translate('Delivery Order') . ' #' . $order->code),
            'message' => translate('Conversation ready'),
        ];
    }
}
