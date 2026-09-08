<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\CartItem;

class AiPlannerController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'budget' => 'required|numeric',
            'days' => 'required|integer',
            'event_type' => 'nullable|string',
            'guest_count' => 'nullable|integer'
        ]);

        $budget = (int) $request->budget;
        $eventType = strtolower($request->event_type ?? 'wedding');
        $guestCount = (int) ($request->guest_count ?? 100);
        $days = (int) ($request->days ?? 1);
        $remainingBudget = $budget;

        $allocation = $this->getEventCategoryMap($eventType);

        $requiredCategories = [];
        if ($eventType === 'wedding') {
            $requiredCategories = ['Chairs', 'Tables', 'Decoration', 'Sound System', 'Wedding Gown'];
        }

        $results = [];
        $totalUsed = 0;

        $categoryMap = [
            'Decoration' => ['Event Decoration','Floral Arrangements','Backdrops','Balloon Decoration','Stage Decoration','Wedding Decoration','Traditional Decor'],
            'Chairs' => ['Chairs','Banquet Chairs','Plastic Chairs','Throne Chairs','VIP Lounge Chairs'],
            'Sound System' => ['Sound System','DJ Equipment','Microphones','Speakers'],
            'Lighting' => ['Stage Lighting','LED Screens','Projectors'],
            'Tables' => ['Tables','Cocktail Tables'],
            'Wedding Gown' => ['Wedding Gown','Bridal Dress'],
            'Generator' => ['Generator'],
            'Canopy' => ['Canopy'],
            'Misc' => ['Misc']
        ];

        foreach ($allocation as $categoryName => $percent) {

            $categoryBudget = $budget * ($percent / 100);

            $items = Item::where('status', 'active')
                ->whereHas('category', function ($q) use ($categoryMap, $categoryName) {
                    if (isset($categoryMap[$categoryName])) {
                        $q->whereIn('name', $categoryMap[$categoryName]);
                    }
                })
                ->orderBy('price_per_day')
                ->get();

            foreach ($items as $item) {

                $quantity = $this->calculateQuantity(
                    $categoryName,
                    $guestCount,
                    $eventType,
                    $item
                );

                if ($quantity <= 0) continue;

                $estimatedCost = $item->price_per_day * $days * $quantity;

                if ($estimatedCost > $categoryBudget || $estimatedCost > $remainingBudget) {

                    if (in_array($categoryName, $requiredCategories)) {

                        $maxAffordableQty = floor(
                            min($categoryBudget, $remainingBudget)
                            / ($item->price_per_day * $days)
                        );

                        if ($maxAffordableQty > 0) {
                            $quantity = $maxAffordableQty;
                            $estimatedCost = $item->price_per_day * $days * $quantity;
                        } else {
                            continue;
                        }

                    } else {
                        continue;
                    }
                }

                $results[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'price_per_day' => $item->price_per_day,
                    'quantity' => $quantity,
                    'unit_size' => $item->unit_size ?? 1,
                    'unit_label' => $item->unit_label ?? 'unit',
                    'category_name' => $item->category ? $item->category->name : null,
                    'cost' => $estimatedCost
                ];

                $totalUsed += $estimatedCost;
                $remainingBudget -= $estimatedCost;

                break;
            }
        }

        return response()->json([
            'items' => $results,
            'total_used' => $totalUsed,
            'remaining' => $remainingBudget
        ]);
    }

    // ✅ MOVED OUTSIDE generate()
   public function addToCart(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => 'Please login to add items to cart.'
        ], 401);
    }

$request->validate([
    'items' => 'required|array',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after_or_equal:start_date',
]);

foreach ($request->items as $item) {

    CartItem::create([
        'user_id' => auth()->id(),
        'item_id' => $item['id'],
        'quantity' => $item['quantity'],
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
    ]);
}

    return response()->json([
        'success' => true
    ]);
}



    private function getEventCategoryMap($eventType)
    {
        switch ($eventType) {

            case 'wedding':
                return [
                    'Decoration' => 25,
                    'Chairs' => 20,
                    'Sound System' => 15,
                    'Tables' => 10,
                    'Wedding Gown' => 15,
                    'Lighting' => 5,
                    'Canopy' => 5,
                    'Generator' => 3,
                    'Misc' => 2,
                ];

            case 'birthday':
                return [
                    'Decoration' => 25,
                    'Chairs' => 25,
                    'Sound System' => 20,
                    'Lighting' => 10,
                    'Tables' => 10,
                    'Misc' => 10,
                ];

            case 'burial':
                return [
                    'Chairs' => 35,
                    'Canopy' => 20,
                    'Sound System' => 15,
                    'Tables' => 15,
                    'Generator' => 10,
                    'Misc' => 5,
                ];

            default:
                return [
                    'Decoration' => 20,
                    'Chairs' => 15,
                    'Sound System' => 15,
                    'Canopy' => 10,
                    'Lighting' => 10,
                    'Tables' => 10,
                    'Generator' => 10,
                    'Misc' => 10,
                ];
        }
    }

    private function calculateQuantity($categoryName, $guestCount, $eventType, $item = null)
    {
        $unitSize = $item->unit_size ?? 1;
        if ($unitSize <= 0) $unitSize = 1;

        switch (strtolower($categoryName)) {
            case 'chairs':
            case 'tables':
            case 'canopy':
                return ceil($guestCount / $unitSize);
            default:
                return 1;
        }
    }
}
