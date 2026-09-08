<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'message','file', 'seen'];

    public function conversation()
    {
        return $this->belongsTo(SupportConversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}