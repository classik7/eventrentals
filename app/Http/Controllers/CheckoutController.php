<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function verify($reference)
    {
        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->get("https://api.paystack.co/transaction/verify/$reference");

        $data = $response->json();

        if ($data['status'] && $data['data']['status'] == 'success') {

            $order = Order::updateOrCreate(
                ['reference' => $reference],
                [
                    'email' => $data['data']['customer']['email'],
                    'amount' => $data['data']['amount'] / 100,
                    'status' => 'paid'
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Payment verified & order saved',
                'order' => $order
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Payment failed'
        ]);
    }
}