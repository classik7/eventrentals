<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | DATE FILTER LOGIC
        |--------------------------------------------------------------------------
        */

        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfYear();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | COMMISSION + PAYOUT CALCULATIONS (FILTERED)
        |--------------------------------------------------------------------------
        */

        $totalCommission = Payment::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('service_fee');

        $thisMonthCommission = $totalCommission;

        $totalPayouts = Withdrawal::where('status','approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | NET PLATFORM PROFIT
        |--------------------------------------------------------------------------
        */

        $netProfit = $totalCommission - $totalPayouts;

        /*
|--------------------------------------------------------------------------
| MONTHLY COMMISSION DATA (FILTER-AWARE)
|--------------------------------------------------------------------------
*/

$monthlyCommission = [];
$months = [];

// If custom filter is applied
if ($request->start_date && $request->end_date) {

    $current = $startDate->copy()->startOfMonth();

    while ($current <= $endDate) {

        $months[] = $current->format('M Y');

        $monthlyCommission[] = Payment::where('status', 'success')
            ->whereYear('created_at', $current->year)
            ->whereMonth('created_at', $current->month)
            ->sum('service_fee');

        $current->addMonth();
    }

} else {

    // Default: last 12 months
    for ($i = 11; $i >= 0; $i--) {

        $date = Carbon::now()->subMonths($i);

        $months[] = $date->format('M Y');

        $monthlyCommission[] = Payment::where('status', 'success')
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->sum('service_fee');
    }
}

        /*
        |--------------------------------------------------------------------------
        | TOP 5 EARNING OWNERS (ACCURATE + FILTERED)
        |--------------------------------------------------------------------------
        */

        $ownerTotals = [];

        $successfulPayments = Payment::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        foreach ($successfulPayments as $payment) {

            $cartItems = json_decode($payment->cart_snapshot);

            foreach ($cartItems as $cart) {

                $item = Item::find($cart->item_id);
                if (!$item) continue;

                $start = Carbon::parse($cart->start_date);
                $end   = Carbon::parse($cart->end_date);

                $days = $start->diffInDays($end) + 1;
				
				$quantity = $cart->quantity_units ?? 1;

				$gross = $days * $item->price_per_day * $quantity;

                $ownerShare = round($gross * 0.93, 2);

                $ownerTotals[$item->user_id] =
                    ($ownerTotals[$item->user_id] ?? 0) + $ownerShare;
            }
        }

        $topOwnersData = collect($ownerTotals)
            ->sortDesc()
            ->take(5)
            ->map(function ($earnings, $ownerId) {
                $owner = User::find($ownerId);

                return [
                    'name' => $owner?->name ?? 'Unknown',
                    'earnings' => $earnings
                ];
            })
            ->values();
$platformBalance = \App\Models\PlatformWalletTransaction::latest()
    ->value('balance_after') ?? 0;
        return view('admin.finance', compact(
            'totalCommission',
            'thisMonthCommission',
            'totalPayouts',
            'netProfit',
            'monthlyCommission',
            'months',
            'topOwnersData',
            'startDate',
            'endDate',
			'platformBalance'
        ));
    }
	
	public function export(Request $request)
{
    $startDate = $request->start_date
        ? Carbon::parse($request->start_date)->startOfDay()
        : Carbon::now()->startOfYear();

    $endDate = $request->end_date
        ? Carbon::parse($request->end_date)->endOfDay()
        : Carbon::now()->endOfDay();

    $payments = Payment::where('status','success')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    $filename = 'finance-report-' . now()->format('Y-m-d') . '.csv';

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate",
        "Expires" => "0"
    ];

    $columns = ['Payment ID','Reference','Commission','Owner Earnings','Date'];

    $callback = function() use ($payments, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($payments as $payment) {
            fputcsv($file, [
                $payment->id,
                $payment->reference,
                $payment->service_fee,
                $payment->owner_earnings,
                $payment->created_at
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}