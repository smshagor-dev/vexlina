<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LotteryWinNotification extends Notification
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
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'notification_type_id' => $this->data['notification_type_id'] ?? null,

            'lottary_id'     => $this->data['lottary_id'] ?? null,
            'prize_id'       => $this->data['prize_id'] ?? null,
            'prize_name'     => $this->data['prize_name'] ?? null,
            'ticket_number'  => $this->data['ticket_number'] ?? null,
            'winner_id'      => $this->data['winner_id'] ?? null,

            'user_id'        => $this->data['user_id'] ?? $notifiable->id,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
