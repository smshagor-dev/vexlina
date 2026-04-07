<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ConversationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) use ($request) {
                $currentUserId = optional($request->user())->id;
                $participant = $data->sender_id == $currentUserId ? $data->receiver : $data->sender;
                $participantShop = $participant && $participant->user_type != 'admin' ? $participant->shop : null;

                return [
                    'id' => $data->id,
                    'receiver_id' => intval(optional($participant)->id),
                    'receiver_type'=> optional($participant)->user_type,
                    'shop_id' => optional($participant)->user_type == 'admin' ? 0 : intval(optional($participantShop)->id),
                    'shop_name' => optional($participant)->user_type == 'admin'
                        ? 'In House Product'
                        : (optional($participantShop)->name ?? optional($participant)->name),
                    'shop_logo' => optional($participant)->user_type == 'admin'
                        ? uploaded_asset(get_setting('header_logo'))
                        : uploaded_asset(optional($participantShop)->logo ?? optional($participant)->avatar_original),
                    'title'=> $data->title,
                    'sender_viewed'=> intval($data->sender_viewed),
                    'receiver_viewed'=> intval($data->receiver_viewed),
                    'date'=> $data->updated_at,
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
