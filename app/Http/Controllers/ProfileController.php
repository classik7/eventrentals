<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
{
    $user = auth()->user();

    return view('profile.show', compact('user'));
}

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // ✅ VALIDATION
        $request->validate([
            'phone' => 'nullable|regex:/^[0-9+\-\s]+$/|max:20',
            'profile_photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:4096',
            'bio' => 'nullable|string|max:500',

            // 🔥 KYC
            'id_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'selfie' => 'nullable|image|max:4096',
        ]);

        // ✅ BASIC INFO
        $user->phone = $request->phone;
        $user->bio = $request->bio;

        // ✅ PROFILE PHOTO
        if ($request->hasFile('profile_photo')) {
            $user->profile_photo = $request->file('profile_photo')
                ->store('profiles', 'public');
        }

        // ✅ COVER PHOTO
        if ($request->hasFile('cover_photo')) {
            $user->cover_photo = $request->file('cover_photo')
                ->store('profiles', 'public');
        }

        // 🔥 ===============================
        // 🔥 KYC LOGIC (IMPROVED)
        // 🔥 ===============================

        $idUploaded = false;
        $selfieUploaded = false;

        if ($request->hasFile('id_document')) {
            $user->id_document = $request->file('id_document')->store('kyc', 'public');
            $idUploaded = true;
        }

        if ($request->hasFile('selfie')) {
            $user->selfie = $request->file('selfie')->store('kyc', 'public');
            $selfieUploaded = true;
        }

        // ✅ Only trigger KYC when BOTH are uploaded
        if ($idUploaded && $selfieUploaded) {
            $user->kyc_status = 'pending';
            $user->kyc_tier = 1;
            $user->trust_level = 2; // optional
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
	}
    
	
	public function tier1()
{
    return view('kyc.tier1');
}

public function tier2()
{
    return view('kyc.tier2');
}

public function tier3()
{
    return view('kyc.tier3');
}

public function submitTier1(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'nin' => 'required|digits:11',
        'dob' => 'required|date',
    ]);

    // Save data
    $user->nin = $request->nin;
    $user->dob = $request->dob;

    // Update KYC
    $user->kyc_status = 'pending';
    $user->kyc_tier = 1;

    $user->save();

    return back()->with('success', 'Tier 1 verification submitted.');
}

public function submitTier2(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        'selfie' => 'required|image|max:4096',
    ]);

    // Upload files
    $user->id_document = $request->file('id_document')->store('kyc', 'public');
    $user->selfie = $request->file('selfie')->store('kyc', 'public');

    // Update status
    $user->kyc_status = 'pending';
    $user->kyc_tier = 2;

    $user->save();

    return back()->with('success', 'Tier 2 submitted successfully. Await approval.');
}

public function submitTier3(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'business_name' => 'required|string|max:255',
        'cac_number' => 'required|string|max:100',
        'business_address' => 'required|string|max:500',
        'proof_of_address' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
    ]);

    // Save fields
    $user->business_name = $request->business_name;
    $user->cac_number = $request->cac_number;
    $user->business_address = $request->business_address;

    // Upload file
    $user->proof_of_address = $request->file('proof_of_address')
        ->store('kyc', 'public');

    // Update status
    $user->kyc_status = 'pending';
    $user->kyc_tier = 3;

    $user->save();

    return back()->with('success', 'Tier 3 submitted. Await admin approval.');
}
}