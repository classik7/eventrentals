<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\WalletNotification;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = \App\Models\WalletTransaction::where('user_id', $user->id);

        // 🔍 FILTER TYPE
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // 🔎 SEARCH
        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->latest()->paginate(10);

        // 💰 TOTALS
        $credits = \App\Models\WalletTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->sum('amount');

        $debits = \App\Models\WalletTransaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->sum('amount');

        // 🚨 FRAUD STATS (OPTION A FIX)
        $fraudStats = [
            'total_flagged' => \App\Models\Withdrawal::where('owner_id', $user->id)
                ->where('is_flagged', true)
                ->count(),

            'high_risk' => \App\Models\Withdrawal::where('owner_id', $user->id)
                ->where('risk_level', 'high')
                ->count(),

            'total_amount' => \App\Models\Withdrawal::where('owner_id', $user->id)
                ->where('is_flagged', true)
                ->sum('amount'),
        ];

        return view('owners.wallet.index', compact(
            'user',
            'transactions',
            'credits',
            'debits',
            'fraudStats' // 🔥 ADDED
        ));
    }

    public function export(Request $request)
    {
        $user = auth()->user();

        $query = \App\Models\WalletTransaction::where('user_id', $user->id);

        // 🔍 FILTER
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->latest()->get();

        $response = new StreamedResponse(function () use ($transactions) {

            $handle = fopen('php://output', 'w');

            // CSV HEADER
            fputcsv($handle, ['Type', 'Description', 'Amount', 'Date']);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    strtoupper($tx->type),
                    $tx->description,
                    $tx->amount,
                    $tx->created_at->format('d M Y H:i')
                ]);
            }

            fclose($handle);
        });

        $filename = 'wallet_transactions_' . now()->format('Ymd_His') . '.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=$filename");

        return $response;
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();

        $query = \App\Models\WalletTransaction::where('user_id', $user->id);

        // Filters
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->latest()->get();

        $pdf = Pdf::loadView('owners.wallet.pdf', [
            'user' => $user,
            'transactions' => $transactions
        ]);

        return $pdf->download('wallet_statement.pdf');
    }
}