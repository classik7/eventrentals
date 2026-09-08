<?php

namespace App\Services;

use App\Models\User;

class AccountSecurityService
{
    public function check(User $user)
    {
        $score = $user->trust_score;

        if ($score < 20) {
            $user->account_status = 'blocked';
        } elseif ($score < 30) {
            $user->account_status = 'restricted';
        } elseif ($score < 40) {
            $user->account_status = 'warning';
        } else {
            $user->account_status = 'active';
        }

        $user->save();
    }
}