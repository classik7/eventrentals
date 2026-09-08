<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Payment;
use App\Models\Item;
use Carbon\Carbon;
use App\Models\Withdrawal;
use App\Services\PaystackService;

class OwnerDashboardController extends Controller
{
    public function index(PaystackService $paystack)
    {
        $owner = auth()->user();
        $ownerId = $owner->id;
		
		 // ⚡ Load all items once (performance optimization)
    $items = Item::all()->keyBy('id');

        // ==============================
        // BASIC RENTAL STATS
        // ==============================

        $rentals = Rental::where('owner_id', $ownerId)->get();

        $totalBookings = $rentals->count();
        $pendingRentals = $rentals->where('status', 'pending')->count();
        $completedRentals = $rentals->where('status', 'completed')->count();

        // ==============================
        // NET EARNINGS (AFTER 7%)
        // ==============================

        $totalEarnings = 0;
        $thisMonthEarnings = 0;

        $successfulPayments = Payment::where('status', 'success')->get();

        foreach ($successfulPayments as $payment) {

            $cartItems = json_decode($payment->cart_snapshot);

            foreach ($cartItems as $cart) {

                $item = $items[$cart->item_id] ?? null;
                if (!$item) continue;

                if ($item->user_id == $ownerId) {

                    $days = Carbon::parse($cart->start_date)
                        ->diffInDays(Carbon::parse($cart->end_date)) + 1;

                    $quantity = $cart->quantity_units ?? 1;

					$gross = $days * $item->price_per_day * $quantity;

                    $ownerShare = round($gross * 0.93, 2);

                    $totalEarnings += $ownerShare;

                    if (Carbon::parse($payment->created_at)->isCurrentMonth()) {
                        $thisMonthEarnings += $ownerShare;
                    }
                }
            }
        }

        // ==============================
        // MONTHLY CHART DATA
        // ==============================

        $monthlyEarnings = array_fill(1, 12, 0);

        foreach ($successfulPayments as $payment) {

            $cartItems = json_decode($payment->cart_snapshot);

            foreach ($cartItems as $cart) {

                $item = $items[$cart->item_id] ?? null;
                if (!$item) continue;

                if ($item->user_id == $ownerId) {

                    $month = Carbon::parse($payment->created_at)->month;

                    $days = Carbon::parse($cart->start_date)
                        ->diffInDays(Carbon::parse($cart->end_date)) + 1;

                    $quantity = $cart->quantity_units ?? 1;

$gross = $days * $item->price_per_day * $quantity;

                    $ownerShare = round($gross * 0.93, 2);

                    $monthlyEarnings[$month] += $ownerShare;
                }
            }
        }

        $months = [];
        $earnings = [];

        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
            $earnings[] = $monthlyEarnings[$i];
        }

        // ==============================
        // RECENT RENTALS
        // ==============================

        $recentRentals = Rental::where('owner_id', $ownerId)
            ->latest()
            ->take(5)
            ->get();

        // ==============================
        // BALANCES
        // ==============================

        // ✅ Available balance now comes from wallet system
        $availableBalance = $owner->wallet_available ?? 0;

        // Pending escrow still calculated from payments
        $pendingEscrow = $owner->wallet_pending ?? 0;
			
			$banks = $paystack->getBanks();
			
			// ==============================
// WITHDRAWAL HISTORY
// ==============================

$withdrawals = Withdrawal::where('owner_id', $ownerId)
    ->latest()
    ->get();

        return view('owners.dashboard', compact(
            'totalBookings',
            'pendingRentals',
            'completedRentals',
            'totalEarnings',
            'thisMonthEarnings',
            'recentRentals',
            'months',
            'earnings',
            'availableBalance',
            'pendingEscrow',
			'banks',
			 'withdrawals'
        ));
    }

    // ==============================
    // SIMPLE WITHDRAW (FULL BALANCE)
    // ==============================

    public function withdraw()
    {
        $owner = auth()->user();

        if ($owner->wallet_balance <= 0) {
            return back()->with('error', 'No available balance.');
        }

        Withdrawal::create([
            'owner_id' => $owner->id,
            'amount' => $owner->wallet_balance,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Withdrawal request submitted.');
    }

    // ==============================
    // CUSTOM AMOUNT WITHDRAW
    // ==============================

    public function store(Request $request)
    {
        $owner = auth()->user();

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        if ($request->amount > $owner->wallet_balance) {
            return back()->with('error', 'Insufficient balance.');
        }

        Withdrawal::create([
            'owner_id' => $owner->id,
            'amount' => $request->amount,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Withdrawal request submitted.');
    }
}