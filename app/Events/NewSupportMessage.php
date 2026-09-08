<?php

namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;

class NewSupportMessage implements ShouldBroadcastNow
{
    public $message;

    public function __construct(SupportMessage $message)
    {
        // 🔥 DEBUG
        Log::info('EVENT FIRED', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id
        ]);

        // ✅ KEEP MODEL (IMPORTANT)
        $this->message = $message;
    }

    // ✅ CHANNEL
    public function broadcastOn()
    {
        return new PrivateChannel('support.' . $this->message->conversation_id);
    }

    // ✅ EVENT NAME
    public function broadcastAs()
    {
        return 'message.sent';
    }

    // ✅ DATA TO FRONTEND
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'message' => $this->message->message,
            'file' => $this->message->file,
            'seen' => $this->message->seen, // ✅ NOW WORKS
            'created_at' => $this->message->created_at->format('H:i'),
        ];
    }
}