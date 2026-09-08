<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function index()
    {
        return view('owners.kyc.index');
    }

    public function submit(Request $request)
{
    $request->validate([
        'id_type' => 'required',
        'id_number' => 'required',
        'id_document' => 'required|image|max:2048',
        'selfie' => 'required|image|max:2048',
    ]);

    $user = auth()->user();

    $idDoc = $request->file('id_document')->store('kyc', 'public');
    $selfie = $request->file('selfie')->store('kyc', 'public');

    // 🔥 FORCE UPDATE (NO update() shortcut)
    $user->id_type = $request->id_type;
    $user->id_number = $request->id_number;
    $user->id_document = $idDoc;
    $user->selfie = $selfie;

    $user->kyc_status = 'pending'; // 🔥 CRITICAL LINE

    $user->save();

    return back()->with('success', 'KYC submitted for review.');
}
}