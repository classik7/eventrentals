<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    private function formatNumber($number)
    {
        // Remove spaces, +, dashes etc.
        $number = preg_replace('/[^0-9]/', '', $number);

        // Convert 0803xxxxxxx → 234803xxxxxxx
        if (str_starts_with($number, '0')) {
            $number = '234' . substr($number, 1);
        }

        return $number;
    }

    public function send($phone, $message)
    {
        if (!$phone) {
            return false;
        }

        $phone = $this->formatNumber($phone);

        $response = Http::post('https://api.ng.termii.com/api/sms/send', [
            "to" => $phone,
            "from" => env('TERMII_SENDER_ID'),
            "sms" => $message,
            "type" => "plain",
            "channel" => "generic",
            "api_key" => env('TERMII_API_KEY')
        ]);

        return $response->json();
    }
}