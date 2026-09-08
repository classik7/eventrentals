<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * CREDIT → wallet_available
     */
    public function credit(User $user, float $amount, string $description = null, string $reference = null)
    {
        DB::transaction(function () use ($user, $amount, $description, $reference) {

            $before = $user->wallet_available;

            $user->wallet_available += $amount;
            $user->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $user->wallet_available,
                'description' => $description,
                'reference' => $reference,
            ]);
        });
    }

    /**
     * DEBIT → wallet_available (SAFE)
     */
    public function debit(User $user, float $amount, string $description = null, string $reference = null)
    {
        // 🔥 HARD STOP (NO NEGATIVE EVER)
        if ($user->wallet_available < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }

        DB::transaction(function () use ($user, $amount, $description, $reference) {

            $before = $user->wallet_available;

            $user->wallet_available -= $amount;
            $user->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $user->wallet_available,
                'description' => $description,
                'reference' => $reference,
            ]);
        });
    }
	
	public function creditPlatform(float $amount, string $reference, string $description = null)
{
    $lastBalance = \App\Models\PlatformWalletTransaction::latest()->value('balance_after') ?? 0;

    $newBalance = $lastBalance + $amount;

    \App\Models\PlatformWalletTransaction::create([
        'type' => 'credit',
        'amount' => $amount,
        'balance_before' => $lastBalance,
        'balance_after' => $newBalance,
        'reference' => $reference,
        'description' => $description ?? 'Platform fee',
    ]);
}
}