<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    /* =====================
        RENTER: My Rentals
    ===================== */
    public function myRentals()
    {
        $rentals = Rental::with('item')
            ->where('renter_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rentals.my', compact('rentals'));
    }

    /* =====================
        OWNER: Rental Requests
    ===================== */
    public function requests()
    {
        $rentals = Rental::with(['item', 'renter'])
            ->where('owner_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rentals.requests', compact('rentals'));
    }

    /* =====================
        BOOKING SUMMARY (UPDATED – STEP 8)
    ===================== */
    public function summary(Request $request, Item $item)
    {
        $request->validate([
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['required', 'date', 'after_or_equal:start_date'], // ✅ FIXED
            'quantity_units' => ['required', 'integer', 'min:1'],
        ]);

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        $days     = $start->diffInDays($end) + 1;
        $quantity = (int) $request->quantity_units;

        // 🔒 AVAILABILITY CHECK
        if (! $this->isQuantityAvailable($item, $start, $end, $quantity)) {
            return back()->withErrors([
                'quantity_units' => 'Not enough stock available for the selected dates.'
            ])->withInput();
        }

        $totalPrice = $days * $item->price_per_day * $quantity;

        return view('rentals.summary', [
            'item'           => $item,
            'start_date'     => $start->toDateString(),
            'end_date'       => $end->toDateString(),
            'days'           => $days,
            'quantity_units' => $quantity,
            'unit_label'     => $item->unit_label,
            'totalPrice'     => $totalPrice,
        ]);
    }

    /* =====================
        CREATE RENTAL REQUEST (FINAL SUBMIT – UPDATED)
    ===================== */
    public function store(Request $request, Item $item)
    {
        $request->validate([
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['required', 'date', 'after_or_equal:start_date'], // ✅ FIXED
            'quantity_units' => ['required', 'integer', 'min:1'],
        ]);

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        $days     = $start->diffInDays($end) + 1;
        $quantity = (int) $request->quantity_units;

        return DB::transaction(function () use ($item, $start, $end, $quantity, $days) {

    // Lock the item row
    $lockedItem = Item::where('id', $item->id)->lockForUpdate()->first();

    if (! $this->isQuantityAvailable($lockedItem, $start, $end, $quantity)) {
        return back()->withErrors([
            'quantity_units' => 'Stock has just been booked by another user. Please reduce quantity or change dates.'
        ]);
    }

    $totalPrice = $days * $lockedItem->price_per_day * $quantity;

    Rental::create([
        'item_id'        => $lockedItem->id,
        'renter_id'      => auth()->id(),
        'owner_id'       => $lockedItem->user_id,
        'start_date'     => $start->toDateString(),
        'end_date'       => $end->toDateString(),
        'quantity_units' => $quantity,
        'total_price'    => $totalPrice,
        'status'         => 'pending',
    ]);

    return redirect()
        ->route('rentals.my')
        ->with('success', 'Your rental request has been sent.');

});
	}

    /* =====================
        APPROVE REQUEST
    ===================== */
    public function approve(Rental $rental)
    {
        abort_if($rental->owner_id !== auth()->id(), 403);

        $rental->update(['status' => 'accepted']);

        Rental::where('item_id', $rental->item_id)
            ->where('id', '!=', $rental->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($rental) {
                $q->whereBetween('start_date', [$rental->start_date, $rental->end_date])
                  ->orWhereBetween('end_date', [$rental->start_date, $rental->end_date]);
            })
            ->update(['status' => 'rejected']);

        return back()->with('success', 'Rental approved successfully.');
    }

    /* =====================
        REJECT REQUEST
    ===================== */
    public function reject(Rental $rental)
    {
        abort_if($rental->owner_id !== auth()->id(), 403);

        $rental->update(['status' => 'rejected']);

        return back()->with('success', 'Rental rejected.');
    }

    /* =====================
        STOCK PROTECTION LOGIC
    ===================== */
    private function isQuantityAvailable(Item $item, $startDate, $endDate, int $requestedQty): bool
    {
        $bookedQty = Rental::where('item_id', $item->id)
            ->whereIn('status', ['accepted', 'paid', 'completed'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
            })
            ->sum('quantity_units');

        return ($bookedQty + $requestedQty) <= $item->quantity_units;
    }
	/* =====================
    LIVE STOCK CHECK (AJAX)
===================== */
public function checkAvailability(Request $request, Item $item)
{
    $request->validate([
        'start_date' => ['required', 'date'],
        'end_date'   => ['required', 'date'],
    ]);

    $start = Carbon::parse($request->start_date);
    $end   = Carbon::parse($request->end_date);

    $bookedQty = Rental::where('item_id', $item->id)
        ->whereIn('status', ['approved', 'paid', 'completed'])
        ->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
              ->orWhereBetween('end_date', [$start, $end])
              ->orWhere(function ($q) use ($start, $end) {
                  $q->where('start_date', '<=', $start)
                    ->where('end_date', '>=', $end);
              });
        })
        ->sum('quantity_units');

    $available = max(0, $item->quantity_units - $bookedQty);

    return response()->json([
        'available' => $available
    ]);
}
/* =====================
    SMART BLOCKED DATES (FULLY BOOKED ONLY)
===================== */
public function blockedDates(Item $item)
{
    $rentals = Rental::where('item_id', $item->id)
        ->whereIn('status', ['approved', 'paid', 'completed'])
        ->get();

    $dailyTotals = [];

    foreach ($rentals as $rental) {

        $period = new \DatePeriod(
            new \DateTime($rental->start_date),
            new \DateInterval('P1D'),
            (new \DateTime($rental->end_date))->modify('+1 day')
        );

        foreach ($period as $date) {
            $formatted = $date->format('Y-m-d');

            if (!isset($dailyTotals[$formatted])) {
                $dailyTotals[$formatted] = 0;
            }

            $dailyTotals[$formatted] += $rental->quantity_units;
        }
    }

    $fullyBookedDates = [];

    foreach ($dailyTotals as $date => $qty) {
        if ($qty >= $item->quantity_units) {
            $fullyBookedDates[] = $date;
        }
    }

    return response()->json($fullyBookedDates);
}



}
