<?php

namespace App\Jobs;

use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessWithdrawalTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $withdrawalId;

    public $tries = 3;

    public function __construct($withdrawalId)
    {
        $this->withdrawalId = $withdrawalId;
    }

    public function handle()
    {
        DB::beginTransaction();

        try {

            // 🔒 Lock withdrawal row
            $withdrawal = Withdrawal::where('id', $this->withdrawalId)
                ->lockForUpdate()
                ->with('owner')
                ->first();

            if (!$withdrawal || $withdrawal->status !== 'processing') {
                DB::rollBack();
                return;
            }

            $owner = $withdrawal->owner;

            if ($owner->wallet_balance < $withdrawal->amount) {
                throw new \Exception('Insufficient wallet balance.');
            }

            // Generate unique reference (idempotent safe)
            $reference = $withdrawal->transfer_reference ?? 'WD-' . Str::uuid();

            // 1️⃣ Create recipient only if not stored
            if (!$withdrawal->recipient_code) {

                $recipient = Http::withToken(config('services.paystack.secret'))
                    ->post('https://api.paystack.co/transferrecipient', [
                        'type' => 'nuban',
                        'name' => $withdrawal->account_name,
                        'account_number' => $withdrawal->account_number,
                        'bank_code' => $withdrawal->bank_code, // store bank_code directly
                        'currency' => 'NGN'
                    ]);

                if (!$recipient->successful() || !$recipient['status']) {
                    throw new \Exception('Recipient creation failed.');
                }

                $withdrawal->recipient_code = $recipient['data']['recipient_code'];
                $withdrawal->save();
            }

            // 2️⃣ Initiate transfer
            $transfer = Http::withToken(config('services.paystack.secret'))
                ->post('https://api.paystack.co/transfer', [
                    'source' => 'balance',
                    'amount' => $withdrawal->amount * 100,
                    'recipient' => $withdrawal->recipient_code,
                    'reason' => 'Marketplace Withdrawal',
                    'reference' => $reference
                ]);

            if (!$transfer->successful() || !$transfer['status']) {
                throw new \Exception('Transfer failed: ' . ($transfer['message'] ?? 'Unknown error'));
            }

            // 3️⃣ Debit wallet using ledger system
app(WalletService::class)->debit(
    $owner,
    $withdrawal->amount,
    'Withdrawal Transfer',
    $reference
);

            // 4️⃣ Update withdrawal
            $withdrawal->update([
                'status' => 'approved',
                'transfer_reference' => $reference,
                'transfer_response' => json_encode($transfer->json())
            ]);

            // 6️⃣ Audit log (no auth() in job)
            AuditLog::create([
                'admin_id' => null,
                'action' => 'Withdrawal Transfer Completed',
                'details' => 'Withdrawal ID: ' . $withdrawal->id
            ]);

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

            // Mark as failed safely
            Withdrawal::where('id', $this->withdrawalId)->update([
                'status' => 'pending',
                'admin_note' => 'Transfer failed: ' . $e->getMessage()
            ]);

            throw $e; // allow retry
        }
    }
}