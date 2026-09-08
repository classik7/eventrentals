<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PaystackService
{
    protected $secret;
    protected $baseUrl;

    public function __construct()
    {
        $this->secret = config('services.paystack.secret_key');
        $this->baseUrl = config('services.paystack.base_url') ?? 'https://api.paystack.co';
    }

    /**
     * 🔐 Headers
     */
    protected function headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->secret,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * 🏦 Create Recipient
     */
    public function createRecipient($withdrawal)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/transferrecipient', [
                'type' => 'nuban',
                'name' => $withdrawal->account_name,
                'account_number' => $withdrawal->account_number,
                'bank_code' => $withdrawal->bank_code, // ✅ FIXED
                'currency' => 'NGN'
            ]);

        return $response->json();
    }

    /**
     * 💸 Initiate Transfer
     */
    public function initiateTransfer($amount, $recipientCode, $reference)
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/transfer', [
                'source' => 'balance',
                'amount' => (int) ($amount * 100), // ✅ ensure integer
                'recipient' => $recipientCode,
                'reason' => 'Withdrawal payout',
                'reference' => $reference,
            ]);

        return $response->json();
    }

    /**
     * 🏦 Get Banks
     */
    public function getBanks()
    {
        return Cache::remember('paystack_banks', 86400, function () {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret_key'), // ✅ FIXED
            ])->get('https://api.paystack.co/bank', [
                'currency' => 'NGN'
            ]);

            if ($response->successful() && $response['status']) {
                return $response['data'];
            }

            return [];
        });
    }
}