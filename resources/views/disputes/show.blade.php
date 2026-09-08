@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">

    <h2 class="text-2xl font-semibold mb-6">
        Dispute Conversation
    </h2>

    <div class="bg-white p-6 rounded-2xl shadow-sm border space-y-6">

        {{-- DISPUTE INFO --}}
        <div>
            <strong>Item:</strong> {{ $dispute->rental->item->title }}
        </div>

        <div>
            <strong>Status:</strong>
            <span class="text-yellow-600 font-medium">
                {{ ucfirst($dispute->status) }}
            </span>
        </div>

        <hr>

        {{-- MESSAGE THREAD --}}
        <div id="chatBox" class="h-96 overflow-y-auto space-y-4 pr-2">

        @forelse($dispute->messages as $msg)

@if($msg->type === 'system')

    <div class="text-center text-xs text-gray-400 my-3">
        {{ $msg->message }}
    </div>

@else

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

        @if($msg->sender_id === auth()->id())
            <p class="text-[10px] text-gray-400 mt-1">
                {{ $msg->seen_at ? 'Seen' : 'Delivered' }}
            </p>
        @endif

    </div>

@endif

@empty
    <p class="text-gray-500 text-sm">No messages yet.</p>
@endforelse

        </div>

        {{-- MESSAGE FORM --}}
        @if($dispute->status !== 'resolved' && $dispute->status !== 'rejected')

            <form method="POST"
                  action="{{ route('disputes.message', $dispute->id) }}"
                  enctype="multipart/form-data"
                  class="space-y-3">
                @csrf

                <textarea name="message"
                          placeholder="Type your message..."
                          class="w-full border rounded-xl px-4 py-3 text-sm"></textarea>

                <input type="file"
                       name="attachment"
                       class="w-full text-sm">

                <p class="text-xs text-gray-500">
                    You can attach PDF, JPG, PNG, DOC or other image/document formats (Max 5MB).
                </p>
				
				<p id="typingIndicator" class="text-xs text-gray-400 hidden">
					User is typing...
				</p>

                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-full text-sm hover:bg-blue-700">
                    Send Message
                </button>
            </form>

        @else
            <div class="text-sm text-gray-500 mt-4">
                This dispute is closed. Messaging is disabled.
            </div>
        @endif
		

    </div>
</div>

</div>

<audio id="messageSound" preload="auto">
    <source src="/sounds/message.mp3" type="audio/mpeg">
</audio>

@endsection

{{-- REAL-TIME POLLING SCRIPT --}}

<script>
const chatBox = document.getElementById('chatBox');
const disputeId = {{ $dispute->id }};
var lastMessageId = {{ $dispute->messages->last()?->id ?? 0 }};
let isTyping = false;

const textarea = document.querySelector('textarea');

if(textarea){
    textarea.addEventListener('focus', () => isTyping = true);
    textarea.addEventListener('blur', () => isTyping = false);

    textarea.addEventListener('input', () => {
        fetch("{{ route('disputes.typing', $dispute->id) }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            }
        });
    });
}

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
    if(isTyping) return;

    fetch(`/disputes/${disputeId}/messages`, {
        method: "GET",
        credentials: "same-origin"
    })
    .then(res => {
        if(!res.ok){
            console.log("Fetch blocked:", res.status);
            return [];
        }
        return res.json();
    })
    .then(data => {

        const newMessages = data.filter(msg => msg.id > lastMessageId);

        newMessages.forEach(msg => {
            renderMessage(msg);
            lastMessageId = msg.id;
        });
    })
    .catch(err => console.log("Fetch error:", err));
}

document.addEventListener('click', function unlockAudio() {
    const sound = document.getElementById('messageSound');
    sound.play().then(() => {
        sound.pause();
        sound.currentTime = 0;
    }).catch(()=>{});
    document.removeEventListener('click', unlockAudio);
});

setInterval(fetchMessages, 4000);
</script>