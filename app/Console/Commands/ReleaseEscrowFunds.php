<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReleaseEscrowFunds extends Command
{
    protected $signature = 'escrow:release';
    protected $description = 'Release escrow funds to owners when event date has passed';

    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        parent::__construct();
        $this->walletService = $walletService;
    }

    public function handle()
{
    $now = Carbon::now();

    $payments = Payment::where('status', 'success')
        ->where('escrow_released', false)
        ->where('escrow_release_date', '<=', $now)
        ->get();

    foreach ($payments as $payment) {

        DB::transaction(function () use ($payment) {

            // 🔒 Lock row
            $lockedPayment = Payment::lockForUpdate()->find($payment->id);

            if (!$lockedPayment || $lockedPayment->escrow_released) {
                return;
            }

            // ✅ CHECK DISPUTE
            $rental = $lockedPayment->rental;

            if ($rental && $rental->dispute && $rental->dispute->status !== 'rejected') {
                return;
            }

            $cartItems = json_decode($lockedPayment->cart_snapshot, true);

            foreach ($cartItems as $cart) {

                $item = \App\Models\Item::find($cart['item_id']);
                if (!$item) continue;

                $owner = \App\Models\User::find($item->user_id);
                if (!$owner) continue;

                /*
                |--------------------------------------------------------------------------
                | 🔥 USE ACTUAL STORED PENDING (NOT RECALCULATED)
                |--------------------------------------------------------------------------
                */

                // Get total pending BEFORE change
                $pendingBefore = $owner->wallet_pending;

                if ($pendingBefore <= 0) {
                    continue; // nothing to release
                }

                // Move ALL pending safely
                $owner->wallet_pending = 0;
                $owner->wallet_available += $pendingBefore;

                $owner->save();

                // 🔐 UNIQUE reference per owner per payment
$reference = 'ESCROW-' . $lockedPayment->id . '-' . $owner->id;

// ✅ Check if already released
$alreadyReleased = DB::table('wallet_transactions')
    ->where('reference', $reference)
    ->exists();

if (!$alreadyReleased) {

    // 💰 Move money safely
    if ($pendingBefore > 0) {
        $owner->wallet_pending = 0;
        $owner->wallet_available += $pendingBefore;
        $owner->save();
    }

    // ⭐ AUDIT LOG
    DB::table('wallet_transactions')->insert([
        'user_id' => $owner->id,
        'amount' => $pendingBefore,
        'type' => 'credit',
        'reference' => $reference,
        'description' => 'Escrow auto release',
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

                \Log::info("Escrow released", [
                    'payment_id' => $lockedPayment->id,
                    'owner_id' => $owner->id,
                    'amount' => $pendingBefore
                ]);
            }

            // ✅ MARK PAYMENT RELEASED
            $lockedPayment->escrow_released = true;
            $lockedPayment->withdrawable_amount = 0;
            $lockedPayment->save();

        });

        $this->info("Released escrow for payment ID: {$payment->id}");
    }

    $this->info('Escrow release process completed.');

    return Command::SUCCESS;
}
}