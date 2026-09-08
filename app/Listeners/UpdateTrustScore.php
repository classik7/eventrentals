<?php

namespace App\Listeners;

use App\Services\TrustScoreService;

class UpdateTrustScore
{
    /**
     * Handle the event.
     */
    public function handle($event)
    {
        // Get the user (vendor)
        $user = $event->user;

        // Recalculate trust score
        $score = app(TrustScoreService::class)->calculate($user);

        // Save it
        $user->trust_score = $score;
        $user->save();
    }
}