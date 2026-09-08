<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportConversation;
use App\Models\SupportMessage;

class SupportController extends Controller
{
    // Entry point
    public function index()
    {
        $conversation = SupportConversation::firstOrCreate([
            'user_id' => auth()->id(),
            'status' => 'open'
        ]);

        return redirect()->route('support.show', $conversation);
    }

    // Show chat
    public function show(SupportConversation $conversation)
    {
        if (
            $conversation->user_id !== auth()->id() &&
            !auth()->user()->canAccessSupport()
        ) {
            abort(403);
        }

        // ✅ Mark as seen
        SupportMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->update(['seen' => true]);

        // 🔥 BROADCAST SEEN UPDATE (REALTIME BLUE TICKS)
        broadcast(new \App\Events\MessageSeen(
            $conversation->id,
            auth()->id()
        ))->toOthers();

        $messages = $conversation->messages()->with('sender')->get();

        return view('support.chat', compact('conversation', 'messages'));
    }

    // SEND MESSAGE
    public function send(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:support_conversations,id',
            'message' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240'
        ]);

        if (!$request->message && !$request->hasFile('file')) {
            return response()->json(['error' => 'Message or file required'], 422);
        }

        $filePath = null;

        // ✅ FILE HANDLING
        if ($request->hasFile('file')) {

            $file = $request->file('file');

            $allowed = [
                'jpg','jpeg','png','gif','webp',
                'pdf','doc','docx',
                'mp3','wav','webm'
            ];

            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, $allowed)) {
                return response()->json(['error' => 'File type not allowed'], 422);
            }

            $filePath = $file->store('support_files', 'public');
        }

        // ✅ CREATE MESSAGE
        $message = SupportMessage::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'file' => $filePath,
            'seen' => false
        ]);

        // 🔥 BROADCAST MESSAGE
        broadcast(new \App\Events\NewSupportMessage($message))->toOthers();

        // ✅ RETURN JSON (IMPORTANT FOR AJAX)
        return response()->json([
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'message' => $message->message,
            'file' => $message->file,
            'seen' => $message->seen,
            'created_at' => $message->created_at->format('H:i')
        ]);
    }

    // Admin panel
    public function admin()
    {
        if (!auth()->user()->canAccessSupport()) {
            abort(403);
        }

        $conversations = SupportConversation::latest()->get();

        return view('support.admin', compact('conversations'));
    }
}