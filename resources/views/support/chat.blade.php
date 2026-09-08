@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gray-100 pb-20">

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-purple-500 to-purple-700 text-white p-6 rounded-b-3xl">
        <h1 class="text-2xl font-bold">Service Center</h1>
		
		 <div class="mt-4 bg-white text-black rounded-xl p-3 flex justify-between items-center shadow">
            <div>
                <div class="font-semibold">Live Chat</div>
                <div class="text-sm text-gray-500">24/7 Support</div>
            </div>
            <div class="bg-purple-600 text-white px-3 py-1 rounded-full text-sm">
                Online
            </div>
        </div>
    </div>

    {{-- CHAT --}}
    <div class="max-w-2xl mx-auto mt-4 bg-white rounded-2xl shadow-lg flex flex-col h-[60vh]">

        {{-- CHAT BODY --}}
        <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">

            @foreach($messages as $msg)

            <div 
    id="msg-{{ $msg->id }}"
    data-id="{{ $msg->id }}"
    data-sender-id="{{ $msg->sender_id }}"
    class="{{ $msg->sender_id == auth()->id() ? 'mine' : '' }} flex {{ $msg->sender_id == auth()->id() ? 'justify-end' : 'justify-start' }}">

                <div class="max-w-xs px-4 py-2 rounded-2xl text-sm shadow
                    {{ $msg->sender_id == auth()->id() ? 'bg-purple-600 text-white' : 'bg-gray-200 text-black' }}">

                    {{-- MESSAGE --}}
                    @if($msg->message)
                        <div>{{ $msg->message }}</div>
                    @endif

                    {{-- FILE --}}
                    @if($msg->file)
                        @php $ext = strtolower(pathinfo($msg->file, PATHINFO_EXTENSION)); @endphp

                        @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                            <img src="{{ asset('storage/'.$msg->file) }}" class="mt-2 rounded-lg max-w-full">

                        @elseif(in_array($ext, ['webm','mp3','wav']))
                            <div class="mt-2 bg-black/10 p-2 rounded-lg">
                                <audio controls class="w-full" id="audio-{{ $msg->id }}">
                                    <source src="{{ asset('storage/'.$msg->file) }}">
                                </audio>

                                <button onclick="changeSpeed('audio-{{ $msg->id }}')" class="text-xs text-purple-600 mt-1">
                                    1x / 2x
                                </button>
                            </div>

                        @else
                            <a href="{{ asset('storage/'.$msg->file) }}" target="_blank"
                               class="block mt-2 underline text-sm">
                                📎 Open File
                            </a>
                        @endif
                    @endif

                    {{-- TIME --}}
                    <div class="text-[10px] text-right opacity-60 mt-1">
                        {{ $msg->created_at->format('H:i') }}
                    </div>

                    {{-- SEEN --}}
                    @if($msg->sender_id == auth()->id())
                        <div class="status text-[10px] text-right">
    {!! $msg->seen 
        ? '<span style="color:#3b82f6;">✓✓ Seen</span>' 
        : '✓ Sent' 
    !!}
</div>
                    @endif

                </div>

            </div>

            @endforeach

        </div>

        {{-- TYPING --}}
        <div id="typing-indicator" class="px-4 py-1 text-sm text-gray-500 hidden">
            typing...
        </div>

        {{-- PREVIEW --}}
        <div id="preview" class="p-2 hidden"></div>

        {{-- RECORDING STATUS --}}
        <div id="recordingStatus" class="text-sm text-gray-500 px-4 hidden">
            🎙️ Recording...
        </div>

        {{-- FORM --}}
        <form id="chatForm" action="/support/send" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">

            <input type="file" name="file" id="fileInput" class="text-sm">

            <input type="text" name="message" id="messageInput"
                   placeholder="Type message..."
                   class="flex-1 border rounded-full px-4 py-2">

            <button type="button" id="recordBtn"
                class="bg-gray-200 px-4 py-2 rounded-full relative">
                🎙️
                <span id="recordingDot"
                    class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full hidden animate-pulse"></span>
            </button>

            <button type="submit"
                class="bg-purple-600 text-white px-4 py-2 rounded-full">
                ➤
            </button>
        </form>

    </div>

</div>
{{-- MOBILE NAV --}}
<div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-md flex justify-around items-center py-2 z-50">
    <a href="/" class="flex flex-col items-center text-gray-500 text-sm">🏠<span>Home</span></a>
    <a href="/support" class="flex flex-col items-center text-purple-600 text-sm font-semibold">🎧<span>Support</span></a>
    <a href="#" class="flex flex-col items-center text-gray-500 text-sm">📈<span>Savings</span></a>
    <a href="#" class="flex flex-col items-center text-gray-500 text-sm">☰<span>More</span></a>
</div>

{{-- 🔊 SOUND --}}
<audio id="msgSound" src="/sounds/notification.mp3"></audio>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    const chatBox = document.getElementById('chat-box');
    const input = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('preview');
    const typingDiv = document.getElementById('typing-indicator');
    const form = document.getElementById('chatForm');

    const recordBtn = document.getElementById('recordBtn');
    const recordingDot = document.getElementById('recordingDot');
    const recordingStatus = document.getElementById('recordingStatus');

    const userId = {{ auth()->id() }};
    const conversationId = {{ $conversation->id }};

    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;

    chatBox.scrollTop = chatBox.scrollHeight;

    // =========================
    // 🔥 RENDER MESSAGE
    // =========================
    function renderMyMessage(message, file, tempId) {

        let fileHtml = '';

        if (file) {
            const url = URL.createObjectURL(file);

            if (file.type.startsWith('image')) {
                fileHtml = `<img src="${url}" style="max-width:200px;margin-top:5px;border-radius:10px;">`;
            } else if (file.type.includes('audio')) {
                fileHtml = `<audio controls style="margin-top:5px;"><source src="${url}"></audio>`;
            } else {
                fileHtml = `<span>📎 File</span>`;
            }
        }

        const html = `
            <div id="${tempId}" data-temp="${tempId}" class="mine flex justify-end" style="margin:10px 0;">
                <div style="background:#9333ea;color:white;padding:10px;border-radius:10px;max-width:70%;">
                    ${message ?? ''}
                    ${fileHtml}
                    <div class="status" style="font-size:10px;text-align:right;">⏳ Sending...</div>
                </div>
            </div>
        `;

        chatBox.insertAdjacentHTML('beforeend', html);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // =========================
    // 🔥 SAFE STATUS UPDATE
    // =========================
    function updateMessageStatus(tempId, status) {

        const el = document.getElementById(tempId);
        if (!el) return;

        const statusDiv = el.querySelector('.status');
        if (!statusDiv) return;

        const current = statusDiv.innerText;

        // 🚫 NEVER downgrade Seen → Sent
        if (current.includes('Seen') && status === 'sent') return;

        if (status === 'sent') {
            statusDiv.innerText = '✓ Sent';
            statusDiv.style.color = '';
        }

        if (status === 'seen') {
            statusDiv.innerHTML = '<span style="color:#3b82f6;">✓✓ Seen</span>';
        }

        if (status === 'failed') {
            statusDiv.innerText = '❌ Failed';
        }
    }

    // =========================
    // 🔥 SEND MESSAGE (AJAX)
    // =========================
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const message = formData.get('message');
        const file = fileInput.files[0];

        const tempId = 'temp-' + Date.now();

        renderMyMessage(message, file, tempId);

        fetch('/support/send', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            const el = document.getElementById(tempId);

            if (el) {
                el.id = 'msg-' + data.id;
                el.dataset.id = data.id;
                el.classList.add('mine');
                el.removeAttribute('data-temp');

                const statusDiv = el.querySelector('.status');

                // Only set sent if not already seen
                if (!statusDiv.innerHTML.includes('Seen')) {
                    statusDiv.innerText = '✓ Sent';
                }
            }

        })
        .catch(() => updateMessageStatus(tempId, 'failed'));

        form.reset();
        preview.innerHTML = '';
    });

    // =========================
    // 🔥 ENTER SEND
    // =========================
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });

    // =========================
    // 🔥 IMAGE PREVIEW
    // =========================
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" style="max-width:100px;border-radius:10px;">`;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    // =========================
    // 🔥 TYPING
    // =========================
    input.addEventListener('input', function () {
        fetch('/typing', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: new URLSearchParams({ conversation_id: conversationId })
        });
    });

    if (typeof Echo === 'undefined') return;

    const channel = Echo.private('support.' + conversationId);

    // =========================
    // 🔥 RECEIVE MESSAGE
    // =========================
    channel.listen('.message.sent', (e) => {

        if (e.sender_id == userId) return;

        let fileHtml = '';

        if (e.file) {
            const ext = e.file.split('.').pop().toLowerCase();

            if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                fileHtml = `<img src="/storage/${e.file}" style="max-width:200px;border-radius:10px;">`;
            } else if (['webm','mp3','wav'].includes(ext)) {
                fileHtml = `<audio controls src="/storage/${e.file}"></audio>`;
            }
        }

        chatBox.insertAdjacentHTML('beforeend', `
            <div style="margin:10px 0;">
                <div style="background:#eee;padding:10px;border-radius:10px;">
                    ${e.message ?? ''}
                    ${fileHtml}
                </div>
            </div>
        `);

        chatBox.scrollTop = chatBox.scrollHeight;
    });

    // =========================
    // 🔥 SEEN (FINAL FIXED)
    // =========================
    channel.listen('.seen', (e) => {

        document.querySelectorAll('.mine[data-id]').forEach(el => {

            const statusDiv = el.querySelector('.status');
            if (!statusDiv) return;

            statusDiv.innerHTML = '<span style="color:#3b82f6;">✓✓ Seen</span>';

        });

    });

    // =========================
    // 🎤 VOICE RECORD
    // =========================
    recordBtn.addEventListener('mousedown', startRecording);
    document.addEventListener('mouseup', stopRecording);

    async function startRecording() {

        if (isRecording) return;
        isRecording = true;

        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        mediaRecorder = new MediaRecorder(stream);
        mediaRecorder.start();

        recordingDot.classList.remove('hidden');
        recordingStatus.classList.remove('hidden');

        audioChunks = [];

        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);

        mediaRecorder.onstop = () => {

            recordingDot.classList.add('hidden');
            recordingStatus.classList.add('hidden');

            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            const file = new File([blob], 'voice.webm');

            const tempId = 'temp-' + Date.now();

            renderMyMessage(null, file, tempId);

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('conversation_id', conversationId);
            formData.append('file', file);

            fetch('/support/send', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {

                const el = document.getElementById(tempId);

                if (el) {
                    el.id = 'msg-' + data.id;
                    el.dataset.id = data.id;
                    el.classList.add('mine');
                    el.removeAttribute('data-temp');

                    const statusDiv = el.querySelector('.status');
                    if (!statusDiv.innerHTML.includes('Seen')) {
                        statusDiv.innerText = '✓ Sent';
                    }
                }

            })
            .catch(() => updateMessageStatus(tempId, 'failed'));
        };
    }

    function stopRecording() {
        if (isRecording && mediaRecorder) {
            isRecording = false;
            mediaRecorder.stop();
        }
    }

});

// 🔥 AUDIO SPEED
function changeSpeed(id) {
    const audio = document.getElementById(id);
    audio.playbackRate = audio.playbackRate === 1 ? 2 : 1;
}
</script>