<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'owner_id',
        'amount',
        'status',
        'approved_by',

        // 💳 BANK DETAILS
        'bank_code',
        'account_number',
        'account_name',

        // 💰 PAYSTACK TRACKING
        'transfer_reference',
        'transfer_status',      // processing | success | failed
        'transfer_response',    // full Paystack response (json)

        // 🛠 ADMIN
        'admin_note',

        // 🔥 FRAUD / RISK
        'is_flagged',
        'risk_level',
        'risk_reason',
        'risk_score',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'transfer_response' => 'array', // 🔥 auto JSON decode
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}