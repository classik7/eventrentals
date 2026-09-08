<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdrawal;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // 🔐 VERIFY SIGNATURE
        $signature = $request->header('x-paystack-signature');
        $secret = config('services.paystack.secret_key');

        if ($signature !== hash_hmac('sha512', $request->getContent(), $secret)) {
            abort(403, 'Invalid signature');
        }

        // ==============================
        // ✅ TRANSFER SUCCESS
        // ==============================
        if ($payload['event'] === 'transfer.success') {

            $reference = $payload['data']['reference'];

            $withdrawal = Withdrawal::where('transfer_reference', $reference)->first();

            if ($withdrawal) {
                $withdrawal->status = 'paid';
                $withdrawal->transfer_status = 'success';
                $withdrawal->save();
            }
        }

        // ==============================
        // ❌ TRANSFER FAILED
        // ==============================
        if ($payload['event'] === 'transfer.failed') {

            $reference = $payload['data']['reference'];

            $withdrawal = Withdrawal::where('transfer_reference', $reference)->first();

            if ($withdrawal) {
                $withdrawal->status = 'failed';
                $withdrawal->transfer_status = 'failed';
                $withdrawal->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }
}