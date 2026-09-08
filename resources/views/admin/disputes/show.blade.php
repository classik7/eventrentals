@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">

    <h2 class="text-2xl font-semibold mb-6">
        🛡 Dispute Details
    </h2>

    <hr class="my-6">

    <h3 class="text-lg font-semibold mb-4">Dispute Conversation</h3>

    <div id="chatBox" class="h-96 overflow-y-auto space-y-4 pr-2 mb-6">

        @forelse($dispute->messages as $msg)

            <div class="p-4 rounded-2xl max-w-xl
                {{ $msg->sender_id === auth()->id()
                    ? 'bg-blue-50 ml-auto text-right'
                    : 'bg-gray-100 mr-auto' }}">

                <p class="text-xs font-semibold mb-1">
                    {{ $msg->sender->name }}

                    @if($msg->sender->role === 'admin')
                        <span class="text-red-500 text-[10px]">(Admin)</span>
                    @endif
                </p>

                @if($msg->message)
                    <p class="text-sm">
                        {{ $msg->message }}
                    </p>
                @endif

                @if($msg->attachment)
                    @php
                        $ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION));
                    @endphp

                    @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                        <img src="{{ asset('storage/'.$msg->attachment) }}"
                             class="mt-2 rounded-lg max-w-xs">
                    @else
                        <a href="{{ asset('storage/'.$msg->attachment) }}"
                           target="_blank"
                           class="text-blue-600 text-xs underline block mt-2">
                            📎 View Attachment
                        </a>
                    @endif
                @endif

                <p class="text-[10px] text-gray-400 mt-2">
                    {{ $msg->created_at->diffForHumans() }}
                </p>

            </div>

        @empty
            <p class="text-gray-500 text-sm">No messages yet.</p>
        @endforelse

    </div>

    {{-- ADMIN MESSAGE FORM --}}
    @if($dispute->status !== 'resolved')
        <form method="POST"
              action="{{ route('admin.disputes.message', $dispute->id) }}"
              enctype="multipart/form-data"
              class="space-y-3 mb-8">
            @csrf

            <textarea name="message"
                      placeholder="Type your message..."
                      class="w-full border rounded-xl px-4 py-3 text-sm"></textarea>

            <input type="file"
                   name="attachment"
                   class="w-full text-sm">

            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-full text-sm hover:bg-blue-700">
                Send Message
            </button>
        </form>
    @endif

    {{-- STATUS BADGE --}}
    <div class="bg-white p-8 rounded-2xl shadow-sm border space-y-8">

        <div>
            <span class="px-4 py-1 text-xs font-medium rounded-full
                @if($dispute->status === 'open') bg-yellow-100 text-yellow-700
                @elseif($dispute->status === 'under_review') bg-blue-100 text-blue-700
                @elseif($dispute->status === 'resolved') bg-green-100 text-green-700
                @elseif($dispute->status === 'rejected') bg-red-100 text-red-700
                @endif">
                {{ ucfirst($dispute->status) }}
            </span>
        </div>

        {{-- BASIC INFO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

            <div>
                <strong>Item:</strong><br>
                {{ $dispute->rental->item->title }}
            </div>

            <div>
                <strong>Renter:</strong><br>
                {{ $dispute->rental->renter->name }}

                @if($dispute->rental->renter->phone)
                    <div class="mt-1 text-xs">
                        <a href="tel:{{ $dispute->rental->renter->phone }}"
                           class="text-blue-600 underline"
                           onclick="logCall({{ $dispute->id }}, '{{ $dispute->rental->renter->phone }}')">
                            📞 {{ $dispute->rental->renter->phone }}
                        </a>
                    </div>
                @endif
            </div>

            <div>
                <strong>Owner:</strong><br>
                {{ $dispute->rental->owner->name }}

                @if($dispute->rental->owner->phone)
                    <div class="mt-1 text-xs text-gray-600">
                        📞 {{ $dispute->rental->owner->phone }}
                    </div>
                @endif
            </div>

        </div>

        {{-- ADMIN ACTION BUTTONS --}}
        @if($dispute->status !== 'resolved')
            <div class="flex flex-wrap gap-4 pt-4 border-t">

                <form method="POST"
                      action="{{ route('admin.disputes.update', $dispute->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="under_review">
                    <button type="submit"
                            class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">
                        🔵 Mark Under Review
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('admin.disputes.update', $dispute->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="resolve">
                    <button type="submit"
                            class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">
                        🟢 Approve Refund
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('admin.disputes.update', $dispute->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <button type="submit"
                            class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700">
                        🔴 Reject
                    </button>
                </form>

            </div>
        @endif

    </div>

</div>
@endsection

{{-- AJAX POLLING --}}
<script>
const chatBox = document.getElementById('chatBox');
const disputeId = {{ $dispute->id }};
let lastMessageId = {{ $dispute->messages->last()->id ?? 0 }};

if(chatBox){
    chatBox.scrollTop = chatBox.scrollHeight;
}

function renderMessage(msg) {

    const isMine = msg.sender_id === {{ auth()->id() }};
    let attachmentHtml = '';

    if (msg.attachment) {
        const ext = msg.attachment.split('.').pop().toLowerCase();

        if(['jpg','jpeg','png','gif','webp'].includes(ext)){
            attachmentHtml = `<img src="/storage/${msg.attachment}" class="mt-2 rounded-lg max-w-xs">`;
        } else {
            attachmentHtml = `<a href="/storage/${msg.attachment}" target="_blank" class="text-blue-600 text-xs underline block mt-2">📎 View Attachment</a>`;
        }
    }

    const html = `
        <div class="p-4 rounded-2xl max-w-xl ${isMine ? 'bg-blue-50 ml-auto text-right' : 'bg-gray-100 mr-auto'}">
            <p class="text-xs font-semibold mb-1">${msg.sender.name}</p>
            ${msg.message ? `<p class="text-sm">${msg.message}</p>` : ''}
            ${attachmentHtml}
            <p class="text-[10px] text-gray-400 mt-2">Just now</p>
        </div>
    `;

    chatBox.insertAdjacentHTML('beforeend', html);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function fetchMessages(){
    fetch(`/disputes/${disputeId}/messages`)
        .then(res => res.json())
        .then(data => {

            const newMessages = data.filter(msg => msg.id > lastMessageId);

            newMessages.forEach(msg => {
                renderMessage(msg);
                lastMessageId = msg.id;
            });
        });
}

setInterval(fetchMessages, 5000);
</script>