<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Dispute extends Model
{
    protected $fillable = [
        'rental_id',
        'renter_id',
        'owner_id',
        'reason',
        'status',
        'refund_amount',
        'admin_note'
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function evidence()
    {
        return $this->hasMany(DisputeEvidence::class);
    }
	
	public function messages()
{
    return $this->hasMany(DisputeMessage::class)->latest();
}
}

