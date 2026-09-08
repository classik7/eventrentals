<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Payment;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\WebhookLog;
use App\Models\PlatformWalletTransaction;
use App\Services\EscrowService;

class PaymentController extends Controller
{
    public function verify($reference)
    {
        $payment = Payment::where('reference', $reference)->firstOrFail();

        // Verify with Paystack
        $response = Http::withOptions(['verify' => false])
            ->withToken(env('PAYSTACK_SECRET_KEY'))
            ->get("https://api.paystack.co/transaction/verify/".$reference)
            ->json();

        if (
            !$response['status'] ||
            $response['data']['status'] !== 'success'
        ) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment verification failed.');
        }

        $paidAmount = round($response['data']['amount'] / 100, 2);
        $expectedAmount = round($payment->total_amount, 2);

        if ($paidAmount !== $expectedAmount) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment amount mismatch.');
        }

        // Process payment safely
        $this->processSuccessfulPayment($payment);

        return redirect()->route('payment.success');
    }


    public function handleWebhook(\Illuminate\Http\Request $request)
    {
        try {

            $secret = env('PAYSTACK_SECRET_KEY');

            $signature = $request->header('x-paystack-signature');

            if (
                !$signature ||
                $signature !== hash_hmac('sha512', $request->getContent(), $secret)
            ) {
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $payload = $request->all();
            $event = $payload['event'] ?? null;
            $reference = $payload['data']['reference'] ?? null;

            $log = WebhookLog::create([
                'event' => $event,
                'reference' => $reference,
                'payload' => json_encode($payload),
                'processed' => false
            ]);

            if ($event === 'charge.success' && $reference) {

                $payment = Payment::where('reference', $reference)->first();

                if ($payment) {
                    $this->processSuccessfulPayment($payment);
                }

                $log->processed = true;
                $log->save();
            }

            return response()->json(['status' => 'Webhook handled successfully'], 200);

        } catch (\Exception $e) {

            \Log::error('Webhook error: ' . $e->getMessage());

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }


   private function processSuccessfulPayment(Payment $payment)
{		
    // ✅ Prevent duplicate execution
    if ($payment->status === 'success' && $payment->wallet_processed) {
    return;
	
}

    DB::transaction(function () use ($payment) {

        $cartItems = json_decode($payment->cart_snapshot, true);

        $ownersToIncrement = [];

        foreach ($cartItems as $cart) {

            $item = Item::find($cart['item_id']);
            if (!$item) continue;

            $startDate = Carbon::parse($cart['start_date']);
            $endDate   = Carbon::parse($cart['end_date']);

            $days = $startDate->diffInDays($endDate) + 1;
            $total = $days * $item->price_per_day * $cart['quantity_units'];

            Rental::create([
                'item_id'     => $item->id,
                'renter_id'   => $payment->user_id,
                'owner_id'    => $item->user_id,
                'start_date'  => $cart['start_date'],
                'end_date'    => $cart['end_date'],
                'quantity'    => $cart['quantity_units'],
                'total_price' => $total,
                'status'      => 'pending',
                'payment_id'  => $payment->id
            ]);

            $ownersToIncrement[] = $item->user_id;
        }

        // ✅ Update owner stats
        foreach (array_unique($ownersToIncrement) as $ownerId) {
            $owner = User::find($ownerId);
            if ($owner) {
                $owner->increment('total_completed_rentals');
            }
        }

        // ✅ Escrow release date
        $latestEndDate = Rental::where('payment_id', $payment->id)
            ->latest()
            ->value('end_date') ?? now();

        $payment->status = 'success';
        $payment->escrow_release_date = Carbon::parse($latestEndDate)->addDay();
        $payment->withdrawable_amount = 0;
        $payment->escrow_released = false;
        $payment->save();

        /*
        |--------------------------------------------------------------------------
        | Escrow + Tier Split Logic (FIXED + SAFE)
        |--------------------------------------------------------------------------
        */

        foreach ($cartItems as $index => $cart) {

            $item = Item::find($cart['item_id']);
            if (!$item) continue;

            $owner = User::find($item->user_id);
            if (!$owner) continue;

            $days = Carbon::parse($cart['start_date'])
                ->diffInDays(Carbon::parse($cart['end_date'])) + 1;

            $total = $days * $item->price_per_day * $cart['quantity_units'];

            // ✅ PLATFORM FEE (7%)
            $platformFee = round($total * 0.07, 2);

            // ✅ NET AMOUNT
            $netAmount = $total - $platformFee;

            // ✅ TIER SPLIT
            switch ($owner->kyc_tier) {
                case 3:
                    $upfrontPercent = 0.70;
                    break;
                case 2:
                    $upfrontPercent = 0.40;
                    break;
                default:
                    $upfrontPercent = 0.20;
                    break;
            }

            $upfrontAmount = round($netAmount * $upfrontPercent, 2);
            $remainingAmount = $netAmount - $upfrontAmount;

            /*
|--------------------------------------------------------------------------
| 🔥 WALLET UPDATE (IDEMPOTENT - FINAL FIX)
|--------------------------------------------------------------------------
*/

$walletReference = 'WALLET_' . $payment->reference . '_ITEM_' . $item->id;

// ✅ Check if already processed
$alreadyProcessed = DB::table('wallet_transactions')
    ->where('reference', $walletReference)
    ->exists();

if (!$alreadyProcessed) {

    // 💰 Update wallet safely
    $owner->wallet_available += $upfrontAmount;
    $owner->wallet_pending += $remainingAmount;
    $owner->save();

    // 🧾 Log upfront
DB::table('wallet_transactions')->insert([
    'user_id' => $owner->id,
    'amount' => $upfrontAmount,
    'type' => 'credit',
    'reference' => $walletReference . '_UPFRONT',
    'description' => 'Upfront payment from booking',
    'created_at' => now(),
    'updated_at' => now()
]);

// 🧾 Log escrow (VERY IMPORTANT)
DB::table('wallet_transactions')->insert([
    'user_id' => $owner->id,
    'amount' => $remainingAmount,
    'type' => 'pending',
    'reference' => $walletReference . '_ESCROW',
    'description' => 'Escrow amount from booking',
    'created_at' => now(),
    'updated_at' => now()
]);
}

            /*
            |--------------------------------------------------------------------------
            | PLATFORM WALLET (FIXED)
            |--------------------------------------------------------------------------
            */

            // ✅ UNIQUE reference per item (CRITICAL FIX)
            $reference = $payment->reference . '_ITEM_' . $item->id;

            // ✅ Prevent duplicate (idempotency)
            $exists = PlatformWalletTransaction::where('reference', $reference)->exists();

            if (!$exists) {

                $currentBalance = PlatformWalletTransaction::latest()->value('balance_after') ?? 0;
                $newBalance = $currentBalance + $platformFee;

                PlatformWalletTransaction::create([
                    'type' => 'credit',
                    'amount' => $platformFee,
                    'balance_after' => $newBalance,
                    'description' => 'Commission from item ' . $item->id,
                    'reference' => $reference,
                    'booking_id' => null // optional
                ]);
            }
        }
		
		$payment->wallet_processed = true;
$payment->save();

    });
}
}