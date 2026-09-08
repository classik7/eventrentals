<?php

namespace App\Http\Controllers;

use App\Models\VendorFollow;

class VendorFollowController extends Controller
{
    public function toggle($vendorId)
    {
        $user = auth()->user();

        $existing = VendorFollow::where('user_id', $user->id)
            ->where('vendor_id', $vendorId)
            ->first();

        if ($existing) {
            $existing->delete();
            return back();
        }

        VendorFollow::create([
            'user_id' => $user->id,
            'vendor_id' => $vendorId,
        ]);

        return back();
    }
}