<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeMessage extends Model
{
    protected $fillable = [
        'dispute_id',
        'sender_id',
        'message',
		'attachment',
		'seen_at',
		'type'
    ];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }
	
	public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
	
	
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}