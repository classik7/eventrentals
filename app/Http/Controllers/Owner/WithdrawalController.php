<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Services\FraudDetectionService;
use App\Notifications\WalletNotification;

class WithdrawalController extends Controller
{
    /**
     * CREATE WITHDRAWAL
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $user = auth()->user();

        // 🔒 BLOCK / RESTRICT
        if ($user->account_status === 'blocked') {
            return back()->with('error', '🚫 Account blocked.');
        }

        if ($user->account_status === 'restricted') {
            return back()->with('error', '⛔ Account restricted.');
        }

        // 💰 BALANCE CHECK
        $balance = $user->role === 'admin'
            ? $user->admin_wallet
            : $user->wallet_available;

        if ($request->amount <= 0) {
            return back()->with('error', 'Invalid amount');
        }

        if ($balance < $request->amount) {
            return back()->with('error', 'Insufficient balance');
        }

        // 🚨 FRAUD CHECK
        $fraud = app(FraudDetectionService::class)
            ->analyze($user, $request->amount);

        // 🚨 DO NOT BLOCK — JUST FLAG
        if ($fraud['risk_level'] === 'high') {
            session()->flash('warning', '🚨 High-risk withdrawal submitted for admin review.');
        }

        DB::transaction(function () use ($user, $request, $fraud) {

            // ✅ CREATE RECORD (SAFE VERSION)
$withdrawal = Withdrawal::create([
    'owner_id' => $user->id,
    'amount' => $request->amount,
    'bank_code' => $request->bank_code,
    'account_number' => $request->account_number,
    'account_name' => $request->account_name,
    'status' => 'pending',

    'is_flagged' => $fraud['is_flagged'] ?? false,
    'risk_level' => $fraud['risk_level'] ?? 'low',
    'risk_reason' => $fraud['risk_reason'] ?? null,
    'risk_score' => $fraud['risk_score'] ?? 0,
]);

            // 💰 MOVE MONEY SAFELY
            if ($user->role === 'admin') {

                $user->admin_wallet = max(0, $user->admin_wallet - $request->amount);

            } else {

                $user->wallet_available = max(0, $user->wallet_available - $request->amount);
                $user->wallet_pending += $request->amount;
            }

            $user->save();

            // 📒 LOG
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'description' => 'Withdrawal request (pending)',
            ]);

            // 🚨 AUTO FREEZE (FIXED POSITION)
            $flagCount = Withdrawal::where('owner_id', $user->id)
                ->where('is_flagged', true)
                ->count();

            if ($flagCount >= 5) {
                $user->account_status = 'restricted';
                $user->save();
            }
        });

        // 🔔 NOTIFY
        $freshUser = User::find(auth()->id());

        if ($freshUser) {
            $freshUser->notify(
                new WalletNotification(
                    "💰 Withdrawal Request\n₦" . number_format($request->amount) . " is pending approval."
                )
            );
        }

        return back()->with('success', 'Withdrawal request submitted.');
    }

    /**
     * BULK ACTION
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'action' => 'required|in:approve,reject',
        ]);

        $withdrawals = Withdrawal::whereIn('id', $request->selected)->get();

        foreach ($withdrawals as $withdrawal) {

            if ($withdrawal->status !== 'pending') continue;

            $user = User::find($withdrawal->owner_id);
            if (!$user) continue;

            if ($request->action === 'approve') {

                if ($user->role !== 'admin') {

                    if ($user->wallet_pending < $withdrawal->amount) continue;

                    $user->wallet_pending = max(0, $user->wallet_pending - $withdrawal->amount);
                }

                $withdrawal->status = 'approved';

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $withdrawal->amount,
                    'type' => 'debit',
                    'description' => 'Withdrawal approved',
                ]);

                $user->notify(new WalletNotification(
                    "✅ Withdrawal Approved\n₦" . number_format($withdrawal->amount)
                ));

            } else {

                if ($user->role === 'admin') {

                    $user->admin_wallet += $withdrawal->amount;

                } else {

                    if ($user->wallet_pending < $withdrawal->amount) continue;

                    $user->wallet_pending = max(0, $user->wallet_pending - $withdrawal->amount);
                    $user->wallet_available += $withdrawal->amount;
                }

                $withdrawal->status = 'rejected';

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $withdrawal->amount,
                    'type' => 'credit',
                    'description' => 'Withdrawal rejected (refunded)',
                ]);

                $user->notify(new WalletNotification(
                    "❌ Withdrawal Rejected\n₦" . number_format($withdrawal->amount)
                ));
            }

            $user->save();
            $withdrawal->save();
        }

        return back()->with('success', 'Bulk action completed');
    }

    /**
     * SINGLE APPROVE
     */
    public function approve($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Already processed');
        }

        DB::transaction(function () use ($withdrawal) {

            $user = User::find($withdrawal->owner_id);

            if ($user && $user->role !== 'admin') {

                if ($user->wallet_pending >= $withdrawal->amount) {
                    $user->wallet_pending = max(0, $user->wallet_pending - $withdrawal->amount);
                }
            }

            if ($user) {
                $user->save();
            }

            $withdrawal->status = 'approved';
            $withdrawal->save();

            if ($user) {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $withdrawal->amount,
                    'type' => 'debit',
                    'description' => 'Withdrawal approved',
                ]);

                $user->notify(new WalletNotification(
                    "✅ Withdrawal Approved\n₦" . number_format($withdrawal->amount)
                ));
            }
        });

        return back()->with('success', 'Withdrawal approved');
    }
}