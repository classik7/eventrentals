<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformWalletTransaction extends Model
{
    protected $fillable = [
    'type',
    'amount',
    'balance_after',
    'description',
    'reference'
];
}
