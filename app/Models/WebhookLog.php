<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'event',
        'reference',
        'payload',
        'processed'
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean'
    ];
}