<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\WalletTransaction;
use App\Models\User; // 🔥 ADD THIS
use Illuminate\Support\Facades\Auth;
use App\Notifications\WalletNotification;

class WithdrawalController extends Controller
{
    /**
     * Display withdrawal list
     */
    public function index(Request $request)
{
    $status = $request->status;
    $admin  = $request->admin_id;
    $risk   = $request->risk;
    $from   = $request->date_from;
    $to     = $request->date_to;
    $min    = $request->min_amount;
    $max    = $request->max_amount;

    $query = Withdrawal::with(['owner', 'approver']);

    // STATUS
    if ($status && in_array($status, ['pending', 'processing', 'paid', 'rejected'])) {
        $query->where('status', $status);
    }

    // ADMIN
    if ($admin) {
        $query->where('approved_by', $admin);
    }

    // RISK
    if ($risk === 'high') {
        $query->where('is_flagged', true);
    }

    // DATE RANGE
    if ($from) {
        $query->whereDate('created_at', '>=', $from);
    }

    if ($to) {
        $query->whereDate('created_at', '<=', $to);
    }

    // AMOUNT RANGE
    if ($min) {
        $query->where('amount', '>=', $min);
    }

    if ($max) {
        $query->where('amount', '<=', $max);
    }

    $withdrawals = $query->latest()->paginate(10);

    // 🔥 ADMINS
    $admins = \App\Models\User::where('role', 'admin')->get();

    // 🔥 FRAUD ANALYTICS
    $fraudStats = [
        'total_flagged' => Withdrawal::where('is_flagged', true)->count(),
        'high_risk'     => Withdrawal::where('risk_level', 'high')->count(),
        'total_amount'  => Withdrawal::where('is_flagged', true)->sum('amount'),
    ];
	
	// 📊 WITHDRAWAL TREND (LAST 7 DAYS)
$trend = \App\Models\Withdrawal::selectRaw('DATE(created_at) as date, SUM(amount) as total')
    ->groupBy('date')
    ->orderBy('date', 'ASC')
    ->limit(7)
    ->get();

// 🚨 FRAUD TREND
$fraudTrend = \App\Models\Withdrawal::selectRaw('DATE(created_at) as date, COUNT(*) as total')
    ->where('is_flagged', true)
    ->groupBy('date')
    ->orderBy('date', 'ASC')
    ->limit(7)
    ->get();

    return view('admin.withdrawals.index', [
        'withdrawals'   => $withdrawals,
        'admins'        => $admins,
        'fraudStats'    => $fraudStats,
		'trend'         => $trend,       // 🔥 ADD THIS
		'fraudTrend'    => $fraudTrend,  // 🔥 ADD THIS
        'totalPending'  => Withdrawal::where('status', 'pending')->sum('amount'),
        'totalProcessing' => Withdrawal::where('status', 'processing')->sum('amount'),
		'totalPaid' => Withdrawal::where('status', 'paid')->sum('amount'),
        'totalRejected' => Withdrawal::where('status', 'rejected')->sum('amount'),
    ]);
}

    /**
     * 🔒 Check Permission
     */
    private function authorizeApproval()
    {
        if (!Auth::user()->can_approve_withdrawals) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * ✅ APPROVE WITHDRAWAL
     */
  public function approve($id)
{
    $this->authorizeApproval();

    try {

        // ==============================
        // 🔒 STEP 1: DATABASE ONLY
        // ==============================

        \DB::transaction(function () use ($id, &$withdrawal, &$user, &$reference) {

            // 🔒 LOCK ROW
            $withdrawal = Withdrawal::lockForUpdate()->findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                throw new \Exception('Already processed');
            }

            $user = $withdrawal->owner;

            if (!$user) {
                throw new \Exception('User not found');
            }

            // 🔒 KYC CHECK
            if ($user->kyc_status !== 'verified') {
                throw new \Exception('User must complete KYC before withdrawal.');
            }

            // 🔥 LIMIT
            switch ($user->kyc_tier) {
                case 3:
                    $limitPercent = 1.0;
                    break;
                case 2:
                    $limitPercent = 0.50;
                    break;
                default:
                    $limitPercent = 0.20;
                    break;
            }

            $maxWithdraw = $user->wallet_available * $limitPercent;

            if ($withdrawal->amount > $maxWithdraw) {
                throw new \Exception('Exceeds limit. Max: ₦' . number_format($maxWithdraw, 2));
            }

            if ($user->wallet_available < $withdrawal->amount) {
                throw new \Exception('Insufficient balance');
            }

            // 🔥 REFERENCE
            $reference = 'WD-' . $withdrawal->id;

            // ✅ SET PROCESSING
            $withdrawal->status = 'processing';
            $withdrawal->transfer_reference = $reference;
            $withdrawal->approved_by = auth()->id();
            $withdrawal->save();
        });

        // ==============================
        // 🔥 STEP 2: PAYSTACK (OUTSIDE TRANSACTION)
        // ==============================

        $paystack = new \App\Services\PaystackService();

        // 🏦 CREATE RECIPIENT
        if (!$user->paystack_recipient_code) {

            $recipient = $paystack->createRecipient($withdrawal);

            if (!isset($recipient['status']) || !$recipient['status']) {
                return back()->with('error', $recipient['message'] ?? 'Recipient failed');
            }

            $user->paystack_recipient_code = $recipient['data']['recipient_code'];
            $user->save();
        }

        // 💸 TRANSFER
        $transfer = $paystack->initiateTransfer(
            $withdrawal->amount,
            $user->paystack_recipient_code,
            $reference
        );

        // ❌ FAIL (NO THROW)
        if (!isset($transfer['status']) || !$transfer['status']) {

            $withdrawal->transfer_status = 'failed';
            $withdrawal->save();

            return back()->with('error', $transfer['message'] ?? 'Transfer failed');
        }

        // ⚠️ OTP REQUIRED
        if (isset($transfer['data']['status']) && $transfer['data']['status'] === 'otp') {

            $withdrawal->transfer_status = 'otp_pending';
            $withdrawal->save();

            return back()->with('success', 'OTP required. Check your Paystack email.');
        }

        // ==============================
        // 💰 DEDUCT WALLET
        // ==============================

        if ($user->role !== 'admin') {
            $user->wallet_available = max(0, $user->wallet_available - $withdrawal->amount);
            $user->save();
        }

        // ==============================
        // 💾 SAVE RESPONSE
        // ==============================

        $withdrawal->transfer_status = 'processing';
        $withdrawal->transfer_response = $transfer;
        $withdrawal->save();

        // ==============================
        // 📊 ADMIN LOG
        // ==============================

        \App\Models\AdminLog::create([
            'admin_id' => auth()->id(),
            'action'   => 'Approved withdrawal #' . $withdrawal->id .
                          ' (₦' . number_format($withdrawal->amount) . ')'
        ]);

        // ==============================
        // 💳 WALLET LOG
        // ==============================

        WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $withdrawal->amount,
            'type' => 'debit',
            'description' => 'Withdrawal via Paystack #' . $reference,
        ]);

        // ==============================
        // 🔔 NOTIFY
        // ==============================

        $user->notify(new WalletNotification(
            "💸 Withdrawal Processing\n₦" . number_format($withdrawal->amount)
        ));

        return back()->with('success', 'Withdrawal sent successfully 🚀');

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

    /**
     * ❌ REJECT WITHDRAWAL
     */
    public function reject($id)
    {
        $this->authorizeApproval();

        try {
            $withdrawal = Withdrawal::findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                return back()->with('error', 'Already processed');
            }

            $user = $withdrawal->owner;

            if (!$user) {
                return back()->with('error', 'User not found');
            }

            if ($user->role === 'admin') {

                $user->admin_wallet += $withdrawal->amount;

            } else {

                if ($user->wallet_pending < $withdrawal->amount) {
                    return back()->with('error', 'Invalid pending balance');
                }

                $user->wallet_pending = max(0, $user->wallet_pending - $withdrawal->amount);
                $user->wallet_available += $withdrawal->amount;
            }

            $user->save();

            $withdrawal->status = 'rejected';
            $withdrawal->approved_by = auth()->id();
            $withdrawal->save();
			
			// 🔥 LOG ADMIN ACTION
			\App\Models\AdminLog::create([
    'admin_id' => auth()->id(),
    'action'   => 'Rejected withdrawal #' . $withdrawal->id . 
                  ' (₦' . number_format($withdrawal->amount) . ')'
]);
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $withdrawal->amount,
                'type' => 'credit',
                'description' => 'Withdrawal rejected & refunded by admin #' . Auth::id(),
            ]);

            $user->notify(new WalletNotification(
                "❌ Withdrawal Rejected\n₦" . number_format($withdrawal->amount) . " refunded"
            ));

            return back()->with('success', 'Withdrawal rejected & refunded.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 🔥 BULK ACTION
     */
    public function bulk(Request $request)
{
    $this->authorizeApproval();

    $ids = $request->selected;
    $action = $request->action;

    if (!$ids || !in_array($action, ['approve', 'reject'])) {
        return back()->with('error', 'Invalid bulk action');
    }

    foreach ($ids as $id) {

        $withdrawal = Withdrawal::find($id);

        if (!$withdrawal || $withdrawal->status !== 'pending') {
            continue;
        }

        // ❌ Disable bulk approve properly
        if ($action === 'approve') {
            return back()->with('error', 'Bulk approve disabled. Use individual approve.');
        }

        $user = $withdrawal->owner;

        if (!$user) continue;

        // ✅ REJECT ONLY
        if ($user->role !== 'admin') {
            $user->wallet_pending = max(0, $user->wallet_pending - $withdrawal->amount);
            $user->wallet_available += $withdrawal->amount;
        } else {
            $user->admin_wallet += $withdrawal->amount;
        }

        $withdrawal->status = 'rejected';
        $withdrawal->approved_by = auth()->id();

        $user->save();
        $withdrawal->save();
    }

    return back()->with('success', 'Bulk rejection completed');
}
	
	public function export(Request $request)
{
    $withdrawals = Withdrawal::with(['owner', 'approver'])->latest()->get();

    $filename = "withdrawals.csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function () use ($withdrawals) {
        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'ID',
            'Owner',
            'Amount',
            'Status',
            'Approved By',
            'Date'
        ]);

        foreach ($withdrawals as $w) {
            fputcsv($file, [
                $w->id,
                $w->owner->name ?? '',
                $w->amount,
                $w->status,
                $w->approver->name ?? 'N/A',
                $w->created_at
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

}