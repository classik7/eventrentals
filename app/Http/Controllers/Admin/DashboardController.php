<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\Dispute;
use App\Models\PlatformWalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        // Only completed payments count as revenue
        $payments = Payment::where('status', 'success')->get();

        $totalGMV = $payments->sum('total_amount');

        // 🔥 PLATFORM WALLET (CORRECT SOURCE)
        $platformRevenue = PlatformWalletTransaction::latest()->value('balance_after') ?? 0;
        $totalPlatformEarnings = PlatformWalletTransaction::sum('amount');

        $ownerEarnings = $payments->sum('owner_earnings');
        $totalTransactions = $payments->count();

        $averageOrderValue = $totalTransactions > 0
            ? $totalGMV / $totalTransactions
            : 0;

        // 🔥 MONTHLY PLATFORM REVENUE (CORRECT SOURCE)
        $monthlyRevenue = PlatformWalletTransaction::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as revenue')
            ->groupBy('month')
            ->pluck('revenue', 'month');

        /*
        |--------------------------------------------------------------------------
        | 🔥 PERFORMANCE FIX (MOVED FROM BLADE)
        |--------------------------------------------------------------------------
        */
        $openDisputes = Dispute::whereIn('status', ['open','under_review'])->count();

        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();

        $refundedTransactions = Payment::where('status', 'refunded')->count();

        /*
        |--------------------------------------------------------------------------
        | Top Renting Items
        |--------------------------------------------------------------------------
        */
        $topRentingItems = DB::table('rentals')
            ->join('items', 'rentals.item_id', '=', 'items.id')
            ->select(
                'items.title as item_name',
                DB::raw('COUNT(rentals.id) as total_rentals')
            )
            ->groupBy('items.title')
            ->orderByDesc('total_rentals')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Vendor Performance
        |--------------------------------------------------------------------------
        */
        $vendorPerformance = DB::table('items')
            ->join('users', 'items.user_id', '=', 'users.id')
            ->join('rentals', 'rentals.item_id', '=', 'items.id')
            ->select(
                'users.name',
                DB::raw('COUNT(rentals.id) as total_rentals')
            )
            ->groupBy('users.name')
            ->orderByDesc('total_rentals')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Marketplace Growth
        |--------------------------------------------------------------------------
        */
        $currentMonthBookings = DB::table('rentals')
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonthBookings = DB::table('rentals')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Monthly Bookings Chart
        |--------------------------------------------------------------------------
        */
        $monthlyBookings = DB::table('rentals')
            ->selectRaw('MONTH(created_at) as month, COUNT(id) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->pluck('total', 'month');

        /*
        |--------------------------------------------------------------------------
        | Trending Categories
        |--------------------------------------------------------------------------
        */
        $trendingCategories = DB::table('rentals')
            ->join('items', 'rentals.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select(
                'categories.name',
                DB::raw('COUNT(rentals.id) as total_rentals')
            )
            ->groupBy('categories.name')
            ->orderByDesc('total_rentals')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalGMV',
            'platformRevenue',
            'totalPlatformEarnings',
            'ownerEarnings',
            'totalTransactions',
            'averageOrderValue',
            'monthlyRevenue',
            'topRentingItems',
            'vendorPerformance',
            'currentMonthBookings',
            'lastMonthBookings',
            'monthlyBookings',
            'trendingCategories',

            // 🔥 NEW PERFORMANCE VARIABLES
            'openDisputes',
            'pendingWithdrawals',
            'refundedTransactions'
        ));
    }


    public function finance()
    {
        // 🔥 FIXED: Use platform wallet instead of service_fee
        $totalCommission = PlatformWalletTransaction::sum('amount');

        $monthlyCommission = PlatformWalletTransaction::whereMonth('created_at', now()->month)
            ->sum('amount');

        $totalPayouts = Withdrawal::where('status', 'approved')
            ->sum('amount');

        $pendingEscrow = Payment::where('status', 'success')
            ->where('escrow_released', false)
            ->sum('owner_earnings');

        return view('admin.finance', compact(
            'totalCommission',
            'monthlyCommission',
            'totalPayouts',
            'pendingEscrow'
        ));
    }


    public function typing($id)
    {
        cache()->put("dispute_typing_{$id}_" . auth()->id(), true, now()->addSeconds(5));

        return response()->json([
            'status' => 'ok'
        ]);
    }
}