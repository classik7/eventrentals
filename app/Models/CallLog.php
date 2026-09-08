<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $fillable = [
        'dispute_id',
        'admin_id',
        'phone_called'
    ];
}