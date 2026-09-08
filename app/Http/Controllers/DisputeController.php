<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;           // ✅ ADD THIS
use App\Models\Dispute;          // ✅ ADD THIS
use App\Models\DisputeEvidence;  // ✅ ADD THIS (if using evidence)
use App\Models\DisputeMessage;
use App\Services\SmsService;

class DisputeController extends Controller
{
   public function store(Request $request, $rentalId)
{
    $rental = Rental::findOrFail($rentalId);

    // Prevent multiple disputes
    if ($rental->dispute) {
        return back()->with('error', 'Dispute already exists.');
    }

    $request->validate([
        'reason' => 'required|string',
        'evidence.*' => 'image|max:2048'
    ]);

    $dispute = Dispute::create([
        'rental_id' => $rental->id,
        'renter_id' => auth()->id(),
        'owner_id' => $rental->owner_id,
        'reason' => $request->reason,
    ]);

    if ($request->hasFile('evidence')) {
        foreach ($request->file('evidence') as $file) {
            $path = $file->store('disputes', 'public');

            $dispute->evidence()->create([
                'file_path' => $path
            ]);
        }
    }

    $sms = new SmsService();

$message = "A dispute has been opened for your rental: ".$rental->item->title;

$sms->send($rental->owner->phone, $message);

return back()->with('success', 'Dispute submitted.');
}


public function sendMessage($id, \Illuminate\Http\Request $request)
{
	
    $dispute = \App\Models\Dispute::with([
        'rental.renter',
        'rental.owner'
    ])->findOrFail($id);

    // 🔒 Prevent messaging if dispute is closed
    if (in_array($dispute->status, ['resolved','rejected'])) {
        abort(403);
    }

    // 🔐 Authorization check
    if (
        auth()->id() !== $dispute->rental->renter_id &&
        auth()->id() !== $dispute->rental->owner_id &&
        auth()->user()->role !== 'admin'
    ) {
        abort(403);
    }

    // ✅ Validate input
    $request->validate([
        'message' => 'nullable|string',
        'attachment' => 'nullable|file|max:5120'
    ]);

    // ❌ Prevent empty message + no attachment
    if (!$request->message && !$request->hasFile('attachment')) {
        return back()->with('error', 'Message or attachment required.');
    }

    $filePath = null;

    if ($request->hasFile('attachment')) {
        $filePath = $request->file('attachment')
            ->store('disputes', 'public');
    }

    // 💬 Save message
    \App\Models\DisputeMessage::create([
        'dispute_id' => $dispute->id,
        'sender_id' => auth()->id(),
        'message' => $request->message,
        'attachment' => $filePath
    ]);

    // 📲 Send SMS notification
    $sms = new SmsService();

    $receiverPhone = null;

    if (auth()->id() == $dispute->rental->renter_id) {
        $receiverPhone = $dispute->rental->owner->phone;
    } else {
        $receiverPhone = $dispute->rental->renter->phone;
    }

    if ($receiverPhone) {
        $sms->send(
            $receiverPhone,
            "You have a new message in your dispute conversation."
        );
    }

    return back();
}

public function show($id)
{
    $dispute = \App\Models\Dispute::with([
        'rental.item',
        'rental.renter',
        'rental.owner',
        'messages.sender'
    ])->findOrFail($id);

    // Authorization
    if (
        auth()->id() !== $dispute->rental->renter_id &&
        auth()->id() !== $dispute->rental->owner_id &&
        auth()->user()->role !== 'admin'
    ) {
        abort(403);
    }

    // ✅ Mark messages as seen
    $dispute->messages()
        ->whereNull('seen_at')
        ->where('sender_id', '!=', auth()->id())
        ->update(['seen_at' => now()]);

    return view('disputes.show', compact('dispute'));
}

public function close($id)
{
    $dispute = Dispute::findOrFail($id);

    if(auth()->id() !== $dispute->rental->renter_id){
        abort(403);
    }

    $dispute->status = 'resolved';
    $dispute->save();

    DisputeMessage::create([
        'dispute_id' => $dispute->id,
        'sender_id' => auth()->id(),
        'message' => 'Renter closed this dispute.',
		'type' => 'system'
    ]);

    return back();
}

public function fetchMessages($id)
{
    $dispute = \App\Models\Dispute::with('messages.sender')
        ->findOrFail($id);

    // Authorization
    if (
        auth()->id() != $dispute->renter_id &&
        auth()->id() != $dispute->owner_id &&
        optional(auth()->user())->role !== 'admin'
    ) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    return response()->json($dispute->messages);
}

public function fetch($id)
{
    $dispute = Dispute::with('messages')
        ->findOrFail($id);

    $lastMessage = $dispute->messages->last();

    return response()->json([
        'last_id' => $lastMessage ? $lastMessage->id : 0
    ]);
}
}
