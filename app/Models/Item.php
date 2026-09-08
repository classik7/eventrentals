<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'price_per_day',
        'original_price',
        'selling_price',
        'is_rentable',
        'is_sellable',
        'location',
        'quantity_units',
        'unit_size',
        'unit_label',
        'image',
        'status',
        'user_id',
        'state',
        'local_government',
    ];

    // ✅ VERY IMPORTANT (APPEND image_url TO JSON)
    protected $appends = ['image_url'];

    /* =====================
        RELATIONSHIPS
    ===================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rentals()
    {
        return $this->hasMany(\App\Models\Rental::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists')
                    ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            \App\Models\Review::class,
            \App\Models\Rental::class,
            'item_id',
            'rental_id',
            'id',
            'id'
        );
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }

    /* =====================
        IMAGE ACCESSOR (🔥 FIX)
    ===================== */

   public function getImageUrlAttribute()
{
    if (!$this->image) {
        return null;
    }

    // ✅ REMOVE "items/" IF IT ALREADY EXISTS
    $filename = basename($this->image);

    return url('/image/' . $filename);
}

    /* =====================
        AVAILABILITY LOGIC
    ===================== */

    public function blockedRanges()
    {
        return $this->rentals()
            ->whereIn('status', ['approved', 'paid', 'completed'])
            ->get(['start_date', 'end_date']);
    }

    public function totalPhysicalUnits(): int
    {
        return $this->quantity_units * $this->unit_size;
    }

    public function availableUnitsForDates($start, $end): int
    {
        $booked = $this->rentals()
            ->whereIn('status', ['approved', 'paid', 'completed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end]);
            })
            ->sum('quantity_units');

        return max($this->quantity_units - $booked, 0);
    }
}