<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('support.{conversationId}', function ($user, $conversationId) {
    return true; // allow access (for now)
});