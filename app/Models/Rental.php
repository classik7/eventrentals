<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
   protected $fillable = [
    'item_id',
    'renter_id',
    'owner_id',
    'start_date',
    'end_date',
    'quantity',
    'total_price',
    'status',
    'payment_id',   // 🔥 THIS MUST BE HERE
];


    // Item being rented
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Renter (customer)
    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

public function review()
{
    return $this->hasOne(\App\Models\Review::class);
}

    // Owner (item owner)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
			
			//dispute
	public function dispute()
{
    return $this->hasOne(Dispute::class);
}
			
			//payment
public function payment()
{
    return $this->belongsTo(\App\Models\Payment::class);
}
}
