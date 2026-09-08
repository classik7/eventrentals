<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'logo',
        'banner',

        // 🔥 KYC
        'kyc_tier',
        'kyc_status',
        'id_document',
        'selfie',

        // 🔥 WALLET (NEW SYSTEM)
        'wallet_available',
        'wallet_pending',
    ];

    /**
     * Hidden fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // =======================
    // RELATIONSHIPS
    // =======================

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'renter_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistItems()
    {
        return $this->belongsToMany(Item::class, 'wishlists')->withTimestamps();
    }

    public function reviewsReceived()
    {
        return $this->hasMany(\App\Models\Review::class, 'reviewee_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(\App\Models\Review::class, 'reviewer_id');
    }

    // =======================
    // HELPERS
    // =======================

    public function averageRating()
    {
        return $this->reviewsReceived()->avg('rating');
    }

    // 🔥 KYC LABEL (PalmPay style)
    public function getKycTierLabel()
    {
        return match($this->kyc_tier) {
            1 => 'Tier 1',
            2 => 'Tier 2',
            3 => 'Tier 3',
            default => 'Unknown'
        };
    }

    // 🔥 KYC STATUS LABEL
    public function getKycStatusLabel()
    {
        return match($this->kyc_status) {
            'not_verified' => 'Unverified',
            'pending' => 'Pending Review',
            'verified' => 'Verified',
            default => 'Unknown'
        };
    }

    // 🔥 WITHDRAWAL LIMIT BY TIER
    public function getWithdrawalLimit()
    {
        return match($this->kyc_tier) {
            1 => 50000,
            2 => 500000,
            3 => 5000000,
            default => 0
        };
    }
	
	public function canAccessSupport()
{
    return in_array($this->role, ['admin', 'support']);
}
}