<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LotteryNotification extends Notification
{
    use Queueable;
    
    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Prepare database notification data
     */
    public function toDatabase($notifiable)
    {

       return [
            'notification_type_id' => $this->data['notification_type_id'] ?? null,
            'order_id' => $this->data['order_id'] ?? null,
            'order_code' => $this->data['order_code'] ?? null,
            'user_id' => $this->data['user_id'] ?? null,
            'ticket_number' => $this->data['ticket_number'] ?? null,
        ];
    }
    
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
