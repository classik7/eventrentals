<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\CartItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CartController extends Controller
{
    public function add(Request $request, Item $item)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity_units' => 'required|integer|min:1'
        ]);

        CartItem::create([
            'user_id' => auth()->id(),
            'item_id' => $item->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'quantity_units' => $request->quantity_units,
        ]);

        return redirect()->route('cart.index')
            ->with('success', 'Item added to cart.');
    }

    // ✅ UPDATED: Support AJAX slide panel
    public function index(Request $request)
    {
        $cartItems = CartItem::with('item')
    ->where('user_id', auth()->id())
    ->get();

        // If request is coming from fetch() → return only panel content
        if ($request->ajax()) {
            return view('cart.panel', compact('cartItems'));
        }

        return view('cart.index', compact('cartItems'));
    }

  

    public function smartAddToCart(Request $request)
    {
        $request->validate([
            'items' => 'required|array'
        ]);

        foreach ($request->items as $item) {

            CartItem::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'item_id' => $item['id']
                ],
                [
                    'quantity_units' => $item['quantity'],
                    'start_date' => now(),
                    'end_date' => now()
                ]
            );
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function remove(CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== auth()->id(), 403);

        $cartItem->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'removed'
            ]);
        }

        return back()->with('success','Item removed from cart.');
    }

    public function checkout()
    {
        $cartItems = CartItem::where('user_id', auth()->id())
            ->with('item')
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error','Your cart is empty.');
        }

        $subtotal = 0;

        foreach ($cartItems as $cart) {

            $days = Carbon::parse($cart->start_date)
                ->diffInDays(Carbon::parse($cart->end_date)) + 1;

            $subtotal += $days * $cart->item->price_per_day * $cart->quantity_units;
        }

        $commissionRate = 0.07;
        $serviceFee = round($subtotal * $commissionRate, 2);
        $ownerEarnings = round($subtotal - $serviceFee, 2);
        $totalAmount = $subtotal;

        $reference = Str::uuid();

        Payment::create([
            'user_id' => auth()->id(),
            'reference' => $reference,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'owner_earnings' => $ownerEarnings,
            'total_amount' => $totalAmount,
            'amount' => $totalAmount,
            'cart_snapshot' => $cartItems->toJson(),
            'status' => 'pending'
        ]);

        return view('payments.paystack', [
            'amount' => $totalAmount,
            'reference' => $reference,
            'email' => auth()->user()->email,
            'publicKey' => env('PAYSTACK_PUBLIC_KEY')
        ]);
    }

    public function clear()
    {
        CartItem::where('user_id', auth()->id())->delete();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Cart cleared successfully.');
    }
}