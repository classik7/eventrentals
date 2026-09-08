<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'subtotal',
        'service_fee',
        'owner_earnings',
        'total_amount',
        'amount',
        'cart_snapshot',
        'status',
        'escrow_release_date',
        'withdrawable_amount',
        'escrow_released'
    ];

    protected $casts = [
        'escrow_release_date' => 'datetime',
        'escrow_released' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
	
	public function rentals()
{
    return $this->hasMany(\App\Models\Rental::class);
}
}