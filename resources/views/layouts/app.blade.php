<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Event Rentals')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	

    <style>
        html { scroll-behavior: smooth; }
    </style>

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind & Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- External Styles --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.css">
</head>

<body class="bg-gray-50 text-gray-900 antialiased">

    {{-- NAVBAR --}}
    <div class="sticky top-0 z-50 bg-white shadow-sm">
        @include('partials.navbar')
    </div>

    {{-- GLOBAL ALERTS --}}
    @if(session('success'))
        <div class="max-w-6xl mx-auto mt-6 px-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-6xl mx-auto mt-6 px-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- PAGE CONTENT --}}
    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-16 border-t bg-white py-10 text-center text-sm text-gray-500">
        <div class="max-w-6xl mx-auto px-4 space-y-2">
            <p>© {{ date('Y') }} Event Rentals</p>
            <p class="text-xs text-gray-400">
                Secure payments • Escrow protected • Trusted vendors
            </p>
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.js"></script>
	
<script>
document.addEventListener('DOMContentLoaded', function () {

    const btn = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');
    const list = document.getElementById('notifList');
    const count = document.getElementById('notifCount');
    const markRead = document.getElementById('markAllRead');

    function loadNotifications() {
        fetch('/notifications')
            .then(res => res.json())
            .then(data => {

                list.innerHTML = '';

                let unread = 0;

                data.forEach(n => {

                    if (!n.read_at) unread++;

                    let content = '';

                    if (n.data.type === 'new_item') {
                        content = `
                            <a href="/items/${n.data.item_id}" class="block p-3 hover:bg-gray-100">
                                🆕 ${n.data.title}
                            </a>
                        `;
                    } else {
                        content = `
                            <div class="p-3">${n.data.message}</div>
                        `;
                    }

                    list.innerHTML += content;
                });

                // 🔥 Update badge dynamically
                if (unread > 0) {
                    count.innerText = unread;
                    count.classList.remove('hidden');
                } else {
                    count.classList.add('hidden');
                }

                if (data.length === 0) {
                    list.innerHTML = '<p class="p-4 text-gray-500">No notifications</p>';
                }

            });
    }

    btn.addEventListener('click', () => {
        dropdown.classList.toggle('hidden');
        loadNotifications();
    });

    markRead.addEventListener('click', () => {
        fetch('/notifications/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => {
            loadNotifications();
        });
    });

});
</script>
    @stack('scripts')

</body>
</html>