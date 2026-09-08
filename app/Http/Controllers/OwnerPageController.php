<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use App\Models\Rental;
use App\Models\Review;

class OwnerPageController extends Controller
{
    /**
     * Public owner profile page
     */
    public function show(User $user)
    {
        // 📦 ACTIVE ITEMS
        $items = Item::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->get();

        // 💰 COMPLETED TRANSACTIONS
        $completedTransactions = Rental::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // ⭐ GET OWNER RENTALS
        $rentalIds = Rental::where('owner_id', $user->id)
            ->pluck('id');

        // ⭐ REVIEWS (linked via rentals)
        $reviews = Review::whereIn('rental_id', $rentalIds)
            ->latest()
            ->get();

        // ⭐ AVERAGE RATING
        $averageRating = Review::whereIn('rental_id', $rentalIds)
            ->avg('rating') ?? 0;

        // 🏆 KYC TIER
        $tier = $user->kyc_tier ?? $user->kyc_level ?? 0;

        return view('owners.show', [
            'owner' => $user,
            'items' => $items,
            'completedTransactions' => $completedTransactions,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'tier' => $tier,
        ]);
    }
}