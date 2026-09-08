<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;

class FraudDetectionService
{
    public function analyze(User $user, float $amount): array
    {
        $riskLevel = 'low';
        $reasons = [];

        // 🚨 Rule 1: Large withdrawal
        if ($amount > 500000) {
            $riskLevel = 'high';
            $reasons[] = 'Large withdrawal amount';
        }

        // 🚨 Rule 2: Low trust user
        if ($user->trust_level == 1 && $amount > 100000) {
            $riskLevel = 'high';
            $reasons[] = 'Low trust user trying high withdrawal';
        }

        // 🚨 Rule 3: Too many withdrawals
        $recentWithdrawals = Withdrawal::where('owner_id', $user->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentWithdrawals >= 3) {
            $riskLevel = 'medium';
            $reasons[] = 'Too many withdrawals in 24 hours';
        }

        return [
            'is_flagged' => $riskLevel !== 'low',
            'risk_level' => $riskLevel,
            'risk_reason' => implode(', ', $reasons)
        ];
    }
	
	public function check($user, $amount)
{
    $score = 0;

    if ($amount > 100000) $score += 40;
    if ($user->account_status !== 'active') $score += 30;
    if ($user->wallet_available < 1000) $score += 10;

    $flagCount = \App\Models\Withdrawal::where('owner_id', $user->id)
        ->where('is_flagged', true)
        ->count();

    if ($flagCount > 2) $score += 30;

    // 🎯 DETERMINE LEVEL
    if ($score >= 70) {
        $riskLevel = 'high';
    } elseif ($score >= 40) {
        $riskLevel = 'medium';
    } else {
        $riskLevel = 'low';
    }

    return [
        'is_flagged' => $score >= 40,
        'risk_level' => $riskLevel,
        'risk_score' => $score, // 🔥 THIS IS STEP 3
        'risk_reason' => 'Auto risk scoring',
    ];
}
}