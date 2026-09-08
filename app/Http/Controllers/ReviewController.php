<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Rental;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function create(Item $item)
    {
        return view('reviews.create', compact('item'));
    }

    public function store(Request $request, Item $item)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required'
        ]);

        $rental = Rental::where('item_id', $item->id)
            ->where('renter_id', auth()->id())
            ->where('status', 'completed')
            ->firstOrFail();

        Review::create([
            'rental_id' => $rental->id,
            'reviewer_id' => auth()->id(),
            'reviewee_id' => $item->user_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('items.show', $item->id)
            ->with('success','Review submitted successfully.');
    }
}
