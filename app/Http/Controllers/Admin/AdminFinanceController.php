<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Withdrawal;

class AdminFinanceController extends Controller
{
    public function checkCommission(Request $request)
{
    $financePassword = env('FINANCE_VIEW_PASSWORD');

    if ($request->password !== $financePassword) {
        return response()->json([
            'success' => false
        ]);
    }

    // ✅ Use real admin wallet (correct system)
    $platformCommission = auth()->user()->admin_wallet ?? 0;

    return response()->json([
        'success' => true,
        'platformCommission' => (float) $platformCommission
    ]);
}
}