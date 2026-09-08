<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Approve KYC → Upgrade to Level 3
     */
    public function approveKyc($id)
    {
        $user = User::findOrFail($id);

        $user->kyc_status = 'verified';
        $user->trust_level = 3;

        $user->save();

        return back()->with('success', 'KYC approved. User is now Level 3.');
    }

    /**
     * Reject KYC → Back to Level 1
     */
    public function rejectKyc($id)
    {
        $user = User::findOrFail($id);

        $user->kyc_status = 'unverified';
        $user->trust_level = 1;

        $user->save();

        return back()->with('success', 'KYC rejected.');
    }
}