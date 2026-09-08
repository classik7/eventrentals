<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'start_date',
        'end_date',
		'quantity_units'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
