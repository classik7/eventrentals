<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Withdrawal;
use App\Services\PaystackService;

class RetryFailedTransfers extends Command
{
    protected $signature = 'paystack:retry';
    protected $description = 'Retry failed Paystack transfers';

    public function handle()
    {
        $paystack = new PaystackService();

        $failedWithdrawals = Withdrawal::where('status', 'failed')->get();

        foreach ($failedWithdrawals as $withdrawal) {

            if (!$withdrawal->owner || !$withdrawal->owner->paystack_recipient_code) {
                continue;
            }

            $reference = 'RETRY-' . $withdrawal->id . '-' . time();

            $transfer = $paystack->initiateTransfer(
                $withdrawal->amount,
                $withdrawal->owner->paystack_recipient_code,
                $reference
            );

            if (isset($transfer['status']) && $transfer['status']) {
                $withdrawal->reference = $reference;
                $withdrawal->status = 'processing';
                $withdrawal->save();
            }
        }

        $this->info('Retry completed.');
    }
}