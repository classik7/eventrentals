<?php

namespace App\Services;

use App\Models\Dispute;
use App\Services\WalletService;
use App\Models\PlatformWalletTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class DisputeRefundService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function processFullRefund(Dispute $dispute)
    {
        if ($dispute->status === 'resolved') {
            throw new Exception("Dispute already resolved.");
        }

        DB::transaction(function () use ($dispute) {

            $rental = $dispute->rental;
            $payment = $rental->payment;

            if (!$payment || $payment->status !== 'success') {
                throw new Exception("Valid payment not found for this rental.");
            }

            // =============================
            // IF ESCROW NOT RELEASED
            // =============================
            if (!$payment->escrow_released) {

                $payment->status = 'refunded';
                $payment->save();

            } else {

                // =============================
                // ESCROW ALREADY RELEASED
                // =============================

                $owner = $rental->owner;

                // Reverse owner wallet
                $this->walletService->debit(
                    $owner,
                    $payment->owner_earnings,
                    'Dispute Refund - Owner Reversal',
                    'REFUND-' . $payment->id
                );

                // Reverse platform commission using ledger
                $currentBalance = PlatformWalletTransaction::latest()->value('balance_after') ?? 0;

                $newBalance = $currentBalance - $payment->service_fee;

                PlatformWalletTransaction::create([
                    'type' => 'debit',
                    'amount' => $payment->service_fee,
                    'balance_after' => $newBalance,
                    'description' => 'Commission reversal (Dispute Refund)',
                    'reference' => 'REFUND-' . $payment->reference
                ]);
            }

            // =============================
            // UPDATE DISPUTE
            // =============================
            $dispute->status = 'resolved';
            $dispute->refund_amount = $payment->total_amount;
            $dispute->save();

            // =============================
            // MARK PAYMENT REFUNDED
            // =============================
            $payment->status = 'refunded';
            $payment->save();
        });
    }
}