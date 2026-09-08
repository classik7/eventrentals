<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
   public function toggle(\App\Models\Item $item)
{
    $wishlist = \App\Models\Wishlist::where('user_id', auth()->id())
        ->where('item_id', $item->id)
        ->first();

    if ($wishlist) {
        $wishlist->delete();
        return response()->json([
            'status' => 'removed'
        ]);
    }

    \App\Models\Wishlist::create([
        'user_id' => auth()->id(),
        'item_id' => $item->id,
    ]);

    return response()->json([
        'status' => 'added'
    ]);
}


    public function index()
    {
        $items = auth()->user()
            ->wishlistItems()
            ->with('user','category')
            ->latest()
            ->get();

        return view('wishlist.index', compact('items'));
    }
}
