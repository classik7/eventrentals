<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class KycController extends Controller
{
    /**
     * Show all KYC submissions
     */
    public function index()
    {
        $users = User::where('kyc_status', 'pending')
            ->latest()
            ->get();

        return view('admin.kyc.index', compact('users'));
    }

    /**
     * Approve KYC
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        // 🔥 DETECT TIER AUTOMATICALLY

        // Tier 3 (Business)
        if ($user->business_name && $user->cac_number && $user->proof_of_address) {
            $user->kyc_tier = 3;
        }

        // Tier 2 (ID + Selfie)
        elseif ($user->id_document && $user->selfie) {
            $user->kyc_tier = 2;
        }

        // Tier 1 (Basic)
        elseif ($user->nin && $user->dob) {
            $user->kyc_tier = 1;
        }

        // ✅ FINAL STATUS
        $user->kyc_status = 'verified';
        $user->kyc_verified_at = now();

        // ⭐ Boost trust
        $user->trust_level = 3;

        $user->save();

        return back()->with('success', 'KYC approved successfully');
    }

    /**
     * Reject KYC
     */
    public function reject(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $user->kyc_status = 'rejected';
        $user->kyc_rejection_reason = $request->reason;

        $user->save();

        return back()->with('error', 'KYC rejected');
    }
}