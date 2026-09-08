<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dispute;
use App\Services\DisputeRefundService;
use App\Services\SmsService;

class AdminDisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::with(['rental.item', 'rental.renter'])
            ->latest()
            ->get();

        return view('admin.disputes.index', compact('disputes'));
    }

   public function show($id)
{
    $dispute = \App\Models\Dispute::findOrFail($id);

    // ✅ Mark unseen messages as seen (for admin)
    \App\Models\DisputeMessage::where('dispute_id', $dispute->id)
        ->whereNull('seen_at')
        ->where('sender_id', '!=', auth()->id())
        ->update(['seen_at' => now()]);

    // ✅ Reload everything fresh
    $dispute->load([
        'rental.item',
        'rental.renter',
        'rental.owner',
        'messages.sender'
    ]);

    return view('admin.disputes.show', compact('dispute'));
}

  public function update(Request $request, $id, DisputeRefundService $refundService)
{
    $dispute = Dispute::with([
        'rental.renter',
        'rental.owner'
    ])->findOrFail($id);

    $action = $request->action;

    if ($action === 'under_review') {

        $dispute->status = 'under_review';
        $dispute->save();

    } elseif ($action === 'resolve') {

        $refundService->processFullRefund($dispute);
        $dispute->refresh();

    } elseif ($action === 'reject') {

        $dispute->status = 'rejected';
        $dispute->save();
    }

    // ✅ Send SMS
    $sms = new \App\Services\SmsService();

    $message = "Your dispute status has been updated to: " . ucfirst($dispute->status);

    if ($dispute->rental->renter->phone) {
        $sms->send($dispute->rental->renter->phone, $message);
    }

    if ($dispute->rental->owner->phone) {
        $sms->send($dispute->rental->owner->phone, $message);
    }

    // ✅ Create system message
    \App\Models\DisputeMessage::create([
        'dispute_id' => $dispute->id,
        'sender_id' => auth()->id(),
        'message' => 'Admin updated dispute status to ' . $dispute->status . '.',
		'type' => 'system'
    ]);

    return back()->with('success', 'Dispute updated successfully.');
}
public function logCall(Request $request, $id)
{
    \App\Models\CallLog::create([
        'dispute_id' => $id,
        'admin_id' => auth()->id(),
        'phone_called' => $request->phone
    ]);

    return response()->json(['status' => 'logged']);
}
}