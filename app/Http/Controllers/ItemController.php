<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\ItemImage;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 
use Illuminate\Support\Facades\Storage;
use App\Models\VendorFollow;
use App\Notifications\NewItemNotification;

class ItemController extends Controller
{		use AuthorizesRequests; 
    public function index(Request $request)
    {
        $itemsQuery = Item::with('category')
            ->where('status', 'active')

            ->when($request->search, function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            })

            ->when($request->state, function ($q) use ($request) {
                $q->where('state', $request->state);
            })

            ->when($request->local_government, function ($q) use ($request) {
                $q->where('local_government', $request->local_government);
            })
			
			->when($request->category, function ($q) use ($request) {
			$q->where('category_id', $request->category);
			})

            ->when($request->min_price, function ($q) use ($request) {
                $q->where('price_per_day', '>=', $request->min_price);
            })

            ->when($request->max_price, function ($q) use ($request) {
                $q->where('price_per_day', '<=', $request->max_price);
            });

        /*
        |--------------------------------------------------------------------------
        | 🔥 SORTING LOGIC (NEW)
        |--------------------------------------------------------------------------
        */

        if ($request->sort) {

            switch ($request->sort) {

                case 'price_low':
                    $itemsQuery->orderBy('price_per_day', 'asc');
                    break;

                case 'price_high':
                    $itemsQuery->orderBy('price_per_day', 'desc');
                    break;

                case 'most_booked':
                    $itemsQuery->withCount('rentals')
                               ->orderBy('rentals_count', 'desc');
                    break;

                default:
                    $itemsQuery->latest();
                    break;
            }

        } else {
            $itemsQuery->latest();
        }

       $items = $itemsQuery
    ->withAvg('reviews', 'rating')
    ->withCount('reviews')
    ->paginate(12)
    ->withQueryString();




        /*
        |--------------------------------------------------------------------------
        | LGA COUNT FOR SELECTED STATE
        |--------------------------------------------------------------------------
        */

        $lgaCounts = [];

        if ($request->state) {
            $lgaCounts = Item::where('status', 'active')
                ->where('state', $request->state)
                ->selectRaw('local_government, COUNT(*) as total')
                ->groupBy('local_government')
                ->pluck('total', 'local_government');
        }


        /*
        |--------------------------------------------------------------------------
        | 🔥 DYNAMIC MAX PRICE FOR SLIDER
        |--------------------------------------------------------------------------
        */

        $maxPrice = Item::where('status', 'active')->max('price_per_day') ?? 100000;


        $categories = Category::orderBy('name')->get();
		
$trendingItems = \App\Models\Item::withCount('rentals')
    ->orderBy('rentals_count', 'desc')
    ->take(8)
    ->get();
	
$recentItems = \App\Models\Item::where('status', 'active')
    ->latest()
    ->take(8)
    ->get();


// =============================
// 🔥 FOLLOWED VENDORS FEED (ADD HERE)
// =============================
$feedItems = collect();

if (auth()->check()) {
    $followingIds = VendorFollow::where('user_id', auth()->id())
        ->pluck('vendor_id');

    $feedItems = Item::whereIn('user_id', $followingIds)
        ->where('status', 'active')
        ->latest()
        ->take(12)
        ->get();
}

/*
|--------------------------------------------------------------------------
| 🧠 SMART EVENT BUDGET PLANNER (AI PRO)
|--------------------------------------------------------------------------
*/

$smartSuggestions = collect();
$totalUsed = 0;
$remainingBudget = 0;

if ($request->filled('event_type') && $request->filled('budget')) {

    $eventType = strtolower($request->event_type);
    $budget = (int) $request->budget;
    $guests = max((int) ($request->guests ?? 100), 1);
    $days   = max((int) ($request->days ?? 1), 1);

    $remainingBudget = $budget;

    /*
    |--------------------------------------------------------------------------
    | 🎯 CATEGORY PRIORITY BY EVENT TYPE
    |--------------------------------------------------------------------------
    */

    $eventPriority = [
        'wedding' => ['decoration', 'furniture', 'sound', 'lighting'],
        'birthday' => ['furniture', 'decoration', 'sound'],
        'burial' => ['furniture', 'canopy', 'sound'],
        'conference' => ['sound', 'furniture', 'projector']
    ];

    $priorities = $eventPriority[$eventType] ?? ['furniture', 'sound', 'decoration'];

    /*
    |--------------------------------------------------------------------------
    | 🔥 SMART CATEGORY LOOP
    |--------------------------------------------------------------------------
    */

    foreach ($priorities as $categoryKeyword) {

    $item = Item::where('status', 'active')
        ->whereHas('category', function ($q) use ($categoryKeyword) {
            $q->where('name', 'like', '%' . $categoryKeyword . '%');
        })
        ->orderBy('price_per_day')
        ->first();

    if (!$item) continue;

    $quantity = 1;

    $categoryName = strtolower($item->category->name);

    if (str_contains($categoryName, 'chair')) {
        $quantity = $guests;
    }

    if (str_contains($categoryName, 'table')) {
        $quantity = ceil($guests / 8);
    }

    if (str_contains($categoryName, 'canopy')) {
        $quantity = ceil($guests / 50);
    }

    $estimatedCost = $item->price_per_day * $days * $quantity;

    $smartSuggestions->push([
        'item' => $item,
        'cost' => $estimatedCost,
        'quantity' => $quantity
    ]);

    $totalUsed += $estimatedCost;
    $remainingBudget -= $estimatedCost;
}

}

        return view('items.index', compact(
    'items',
    'categories',
    'lgaCounts',
    'maxPrice',
    'trendingItems',
    'recentItems',
    'smartSuggestions',
    'totalUsed',
    'remainingBudget',
	'feedItems' // ✅ ADD THIS
));
    }
	
	


public function show(Item $item)
{
    // Load images relationship
    $item->load('images');

    $owner = $item->user;

    $canReview = false;

    if(auth()->check()) {
        $canReview = \App\Models\Rental::where('item_id', $item->id)
            ->where('renter_id', auth()->id())
            ->where('status', 'completed')
            ->exists();
    }

    // ⭐ Rating Breakdown
    $reviews = $owner->reviewsReceived;

    $averageRating = round($reviews->avg('rating'), 1);

    $totalReviews = $reviews->count();

    $ratingBreakdown = [];

    for ($i = 5; $i >= 1; $i--) {
        $ratingBreakdown[$i] = $reviews->where('rating', $i)->count();
    }

    return view('items.show', compact(
        'item',
        'owner',
        'canReview',
        'averageRating',
        'totalReviews',
        'ratingBreakdown'
    ));
}



    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('items.create', compact('categories'));
    }


    /* =====================
        STORE ITEM (UPDATED)
    ===================== */
   public function store(Request $request)
{
    // Validation rules - make price_per_day nullable
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required',
        'listing_type' => 'required|in:rent,sell,both',
        'price_per_day' => 'nullable|numeric|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'original_price' => 'nullable|numeric|min:0',
        'location' => 'required|string',
        'state' => 'required|string',
        'local_government' => 'required|string',
        'quantity_units' => 'required|integer|min:1',
        'unit_size' => 'nullable|integer|min:1',
        'unit_label' => 'nullable|string|max:50',
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Prepare data for database
    $data = $validated;
    
    // 🔥 FIX: Ensure price_per_day is never null - default to 0
    $data['price_per_day'] = $validated['price_per_day'] ?? 0;
    
    // 🔥 FIX: Ensure selling_price is never null - default to 0  
    $data['selling_price'] = $validated['selling_price'] ?? 0;
    
    // 🔥 FIX: Ensure original_price is never null - default to 0
    $data['original_price'] = $validated['original_price'] ?? 0;

    // Handle listing type logic
    if ($request->listing_type === 'rent') {
        $data['is_rentable'] = true;
        $data['is_sellable'] = false;
    } elseif ($request->listing_type === 'sell') {
        $data['is_rentable'] = false;
        $data['is_sellable'] = true;
    } else { // both
        $data['is_rentable'] = true;
        $data['is_sellable'] = true;
    }

    // Handle category
    if ($request->category_id === 'other' && $request->other_category) {
        $category = Category::firstOrCreate(['name' => $request->other_category]);
        $data['category_id'] = $category->id;
    }

    $data['user_id'] = auth()->id();
    $data['status'] = 'active';

    $item = Item::create($data);


// =============================
// 🔔 NOTIFY FOLLOWERS (ADD THIS)
// =============================
$followers = VendorFollow::where('vendor_id', auth()->id())->get();

foreach ($followers as $follow) {
    if ($follow->user) {
    $follow->user->notify(new NewItemNotification($item));
}


// =============================
// 📸 SAVE IMAGES (FIXED)
// =============================
if ($request->hasFile('images')) {
    foreach ($request->file('images') as $image) {
        $path = $image->store('items', 'public');

        ItemImage::create([
            'item_id' => $item->id,
            'image' => $path
        ]);
    }
}

// ✅ CLOSE store() PROPERLY
return redirect()->route('items.show', $item)
    ->with('success', 'Item listed successfully!');
}
}


    public function myItems()
    {
        $items = Item::where('user_id', auth()->id())
            ->withCount('rentals')
            ->latest()
            ->get();

        return view('items.my-items', compact('items'));
    }


    public function edit(Item $item)
    {
        abort_if($item->user_id !== auth()->id(), 403);

        $categories = Category::all();

        return view('items.edit', compact('item', 'categories'));
    }


   public function update(Request $request, Item $item)
{
   
    
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'listing_type' => 'required|in:rent,sell,both',
        'price_per_day' => 'nullable|numeric|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'original_price' => 'nullable|numeric|min:0',
        'location' => 'required|string',
        'state' => 'required|string',
        'local_government' => 'required|string',
        'quantity_units' => 'required|integer|min:1',
        'unit_size' => 'nullable|integer|min:1',
        'unit_label' => 'nullable|string|max:50',
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    
    // Prepare data
    $data = $validated;
    $data['price_per_day'] = $validated['price_per_day'] ?? 0;
    $data['selling_price'] = $validated['selling_price'] ?? 0;
    $data['original_price'] = $validated['original_price'] ?? 0;
    
    // Handle listing type
    if ($request->listing_type === 'rent') {
        $data['is_rentable'] = true;
        $data['is_sellable'] = false;
    } elseif ($request->listing_type === 'sell') {
        $data['is_rentable'] = false;
        $data['is_sellable'] = true;
    } else {
        $data['is_rentable'] = true;
        $data['is_sellable'] = true;
    }
    
    // Update item
    $item->update($data);
    
    // Handle new images
    // Handle image deletions
if ($request->has('delete_images') && is_array($request->delete_images)) {
    foreach ($request->delete_images as $imageId) {
        $image = \App\Models\ItemImage::find($imageId);
        if ($image && $image->item_id === $item->id) {
            // Delete from storage
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image);
            // Delete from database
            $image->delete();
        }
    }
}
    
    return redirect()->route('items.show', $item)
        ->with('success', 'Item updated successfully!');
}


    public function destroy(Item $item)
    {
        abort_if($item->user_id !== auth()->id(), 403);

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()
            ->route('items.my')
            ->with('success', 'Item deleted successfully');
    }


    public function toggleStatus(Item $item)
    {
        abort_if($item->user_id !== auth()->id(), 403);

        $item->status = $item->status === 'active' ? 'paused' : 'active';
        $item->save();

        return back()->with('success', 'Item status updated');
    }
}
