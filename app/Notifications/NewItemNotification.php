<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewItemNotification extends Notification
{
    use Queueable;

    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function via($notifiable)
    {
        return ['database']; // ✅ IMPORTANT
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_item',
            'message' => 'New item added by a vendor you follow',
            'item_id' => $this->item->id,
            'title' => $this->item->title,
        ];
    }
}