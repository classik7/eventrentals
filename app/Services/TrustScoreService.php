<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;

class TrustScoreService
{
    public function calculate(User $user)
    {
        $score = 50; // base score

        // ✅ Successful withdrawals (approved)
        $approved = Withdrawal::where('owner_id', $user->id)
            ->where('status', 'approved')
            ->count();

        $score += min($approved * 2, 20);

        // ❌ Failed / rejected withdrawals
        $rejected = Withdrawal::where('owner_id', $user->id)
            ->where('status', 'rejected')
            ->count();

        $score -= min($rejected * 3, 20);

        // 🚨 Fraud flags
        $flagged = Withdrawal::where('owner_id', $user->id)
            ->where('is_flagged', true)
            ->count();

        $score -= min($flagged * 5, 30);

        // ✅ Clean behavior bonus
        if ($flagged == 0) {
            $score += 10;
        }

        // 🔒 Clamp between 0–100
        return max(0, min(100, $score));
    }
}