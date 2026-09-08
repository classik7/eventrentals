<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    /**
     * Get upfront release percentage based on trust level
     */
    public function getReleasePercentage(User $user): int
    {
        switch ($user->trust_level) {
            case 3:
                return 70;
            case 2:
                return 40;
            default:
                return 20; // ✅ FIXED (was 0 before ❌)
        }
    }

    /**
     * Calculate split amounts
     */
    public function splitPayment(User $user, float $amount): array
    {
        $percentage = $this->getReleasePercentage($user);

        $upfront = ($percentage / 100) * $amount;
        $escrow = $amount - $upfront;

        return [
            'upfront' => round($upfront, 2),
            'escrow' => round($escrow, 2),
        ];
    }

    /**
     * 🔥 NEW: Release escrow safely
     */
    public function release(Payment $payment): void
    {
        // ✅ Prevent double release
        if ($payment->escrow_released) {
            return;
        }

        DB::transaction(function () use ($payment) {

            foreach ($payment->rentals as $rental) {

                $owner = User::find($rental->owner_id);
                if (!$owner) continue;

                $total = $rental->total_price;

                // ✅ Remove platform fee (7%)
                $netAmount = $total * 0.93;

                // ✅ Split again (same logic used during payment)
                $split = $this->splitPayment($owner, $netAmount);

                $remainingAmount = $split['escrow'];

                /*
                |--------------------------------------------------------------------------
                | 🔥 MOVE ONLY ESCROW (THIS FIXES NEGATIVE BUG)
                |--------------------------------------------------------------------------
                */

                $owner->wallet_pending -= $remainingAmount;
                $owner->wallet_available += $remainingAmount;

                // ✅ Safety guard
                if ($owner->wallet_pending < 0) {
                    $owner->wallet_pending = 0;
                }

                $owner->save();
            }

            // ✅ Mark payment released
            $payment->escrow_released = true;
            $payment->save();
        });
    }
}