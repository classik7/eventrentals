<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorFollow extends Model
{
    protected $fillable = ['user_id', 'vendor_id'];

    // 🔥 Relationship: follower (user)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔥 Relationship: vendor being followed
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}