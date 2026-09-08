<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <div class="flex items-center gap-2">
                <span class="text-xl">🎉</span>
                <a href="{{ route('items.index') }}" class="font-semibold text-lg text-gray-900">
                    Event Rentals
                </a>
            </div>

            {{-- Search --}}
            <div class="hidden md:block flex-1 px-8">
                <div class="relative">
                    <input
                        type="text"
                        placeholder="Search items for your event..."
                        class="w-full rounded-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>

            {{-- Right Actions --}}
            <div class="flex items-center gap-4">

            @auth

            {{-- ================= MINI CART ================= --}}
            @php
                $cartItemsMini = \App\Models\CartItem::with('item')
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->take(3)
                    ->get();

                $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->count();
                $miniTotal = 0;
            @endphp

            <div class="relative">

                <button type="button" id="cart-toggle"
    class="relative flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 hover:bg-gray-100 transition">

                    <span class="text-lg">🛒</span>

                    @if($cartCount > 0)
                        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs px-2 py-0.5 rounded-full">
                            {{ $cartCount }}
                        </span>
                    @endif
                </button>

               {{-- MINI CART DROPDOWN --}}
<div id="mini-cart"
     class="hidden absolute right-0 mt-3 w-96 bg-white border rounded-2xl shadow-2xl p-4 z-50 transition-all duration-300 scale-95 opacity-0">

    <h3 class="font-semibold mb-3 text-lg">Your Cart</h3>

    <div id="mini-cart-items">

        @forelse($cartItemsMini as $cart)

            @php
                $days = \Carbon\Carbon::parse($cart->start_date)
                    ->diffInDays(\Carbon\Carbon::parse($cart->end_date)) + 1;

                $total = $days * $cart->item->price_per_day * $cart->quantity;
                $miniTotal += $total;
            @endphp

            <div class="flex justify-between items-center mb-3 border-b pb-2 cart-item"
                 data-id="{{ $cart->id }}">

                <div class="flex-1">
                    <p class="text-sm font-medium truncate">
                        {{ $cart->item->title }}
                    </p>
                    <p class="text-xs text-gray-500">
                        ₦{{ number_format($total) }}
                    </p>
                </div>

                <button class="remove-mini-cart text-red-500 text-xs ml-3 hover:text-red-700">
                    ✕
                </button>

            </div>

        @empty
            <p class="text-gray-500 text-sm">Cart is empty.</p>
        @endforelse

    </div>

    @if($cartCount > 0)
        <a href="{{ route('cart.index') }}"
           class="block w-full text-center bg-gray-200 py-2 rounded-lg mt-3 hover:bg-gray-300 transition">
            View Cart
        </a>
    @endif

</div>

            </div>

            {{-- ================= WISHLIST ================= --}}
            @php
                $wishlistCount = \App\Models\Wishlist::where('user_id', auth()->id())->count();
            @endphp

            <a href="{{ route('wishlist.index') }}"
               class="relative flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 hover:bg-gray-100 transition">

                <span class="text-lg">❤️</span>

                <span id="wishlist-count"
                      class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs px-2 py-0.5 rounded-full {{ $wishlistCount ? '' : 'hidden' }}">
                    {{ $wishlistCount ?: '' }}
                </span>
            </a>
			
			<div class="relative ml-2">

    {{-- 🔔 ICON --}}
    <button id="notifBtn" class="relative text-lg">

        🔔

        @php
            $unreadCount = auth()->user()->unreadNotifications->count();
        @endphp

        <span id="notifCount"
              class="absolute -top-2 -right-2 bg-red-600 text-white text-xs px-1 rounded-full {{ $unreadCount ? '' : 'hidden' }}">
            {{ $unreadCount }}
        </span>

    </button>

    {{-- 🔽 DROPDOWN --}}
    <div id="notifDropdown"
         class="hidden absolute right-0 mt-3 w-80 bg-white shadow-xl rounded-xl z-50">

        <div class="p-4 border-b font-semibold">
            Notifications
        </div>

        <div id="notifList" class="max-h-80 overflow-y-auto">
            <p class="p-4 text-gray-500 text-sm">Loading...</p>
        </div>

        <div class="p-3 text-center border-t">
            <button id="markAllRead" class="text-sm text-blue-600">
                Mark all as read
            </button>
        </div>

    </div>

</div>

            {{-- + LIST ITEM --}}
            <a href="{{ route('items.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-blue-700 transition">
                + List Item
            </a>
			
	<div class="relative flex items-center gap-2">


    {{-- CLICKABLE BALANCE --}}
    <a href="{{ route('owner.dashboard') }}"
       class="bg-gray-100 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">

        <div class="flex flex-col leading-tight">

           {{-- Label --}}
<span class="text-[10px] text-gray-500">
    {{ auth()->user()->role === 'admin' ? 'Platform Earnings' : 'Wallet Balance' }}
</span>

{{-- Amount --}}
@php
    $balance = auth()->user()->role === 'admin'
        ? (\App\Models\PlatformWalletTransaction::latest()->value('balance_after') ?? 0)
        : (auth()->user()->wallet_available ?? 0);
@endphp

<span id="walletBalance"
      data-real="₦{{ number_format($balance, 2) }}"
      class="font-semibold text-green-600">

   ₦{{ number_format($balance, 2) }}
</span>

        </div>

    </a>

    {{-- EYE --}}
    <span onclick="toggleWallet(event)"
          class="cursor-pointer">
        👁
    </span>

    {{-- DROPDOWN TOGGLE --}}
    <button onclick="toggleWalletDropdown(event)"
        class="bg-gray-200 px-2 py-1 rounded hover:bg-gray-300">
        ⋮
    </button>

    {{-- DROPDOWN --}}
    <div id="walletDropdown"
        class="hidden absolute right-0 top-10 w-48 bg-white border rounded-xl shadow-lg z-50">

        <a href="{{ route('owner.wallet') }}"
   class="block px-4 py-2 hover:bg-gray-100">
    💰 View Wallet
</a>

        <a href="{{ route('owner.dashboard') }}"
            class="block px-4 py-2 hover:bg-gray-100">
            💸 Withdraw
        </a>

        <a href="{{ route('owner.dashboard') }}"
            class="block px-4 py-2 hover:bg-gray-100">
            📄 Transactions
        </a>

    </div>

</div>

<li class="flex items-center gap-3">

    <a href="{{ route('support.index') }}" class="hover:text-blue-600">
        💬 Support
    </a>

    @if(auth()->user()->canAccessSupport())
        <a href="{{ route('support.admin') }}" class="hover:text-blue-600 text-sm">
            🛠 Panel
        </a>
    @endif

</li>
    

            {{-- PROFILE DROPDOWN --}}
<div class="relative" id="profile-wrapper">

    <button id="profile-toggle"
        class="flex items-center gap-2 px-3 py-2 rounded-full border border-gray-300 hover:bg-gray-100 transition">

        <span class="bg-blue-100 text-blue-700 rounded-full w-8 h-8 flex items-center justify-center text-sm font-semibold">
            @if(auth()->user()->profile_photo)
    <img src="{{ asset('storage/'.auth()->user()->profile_photo) }}"
         class="w-8 h-8 rounded-full object-cover">
@else
    <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center text-white">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
    </div>
@endif
        </span>

        <span class="hidden md:block text-sm text-gray-700">
            {{ auth()->user()->name }}
        </span>
    </button>

    <div id="profile-menu"
        class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-50">

        <a href="{{ route('items.my') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            My Items
        </a>

        <a href="{{ route('rentals.requests') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            Requests
        </a>
		
		<a href="{{ route('owner.dashboard') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
           Owner Dashboard
        </a>
		
		
		
		

        <a href="{{ route('rentals.my') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            My Rentals
        </a>

        {{-- ================= ADMIN WITHDRAWALS ================= --}}
        @if(auth()->user()->role === 'admin')

            @php
                $pendingWithdrawals = \App\Models\Withdrawal::where('status','pending')->count();
            @endphp

            <div class="border-t border-gray-200"></div>

            <a href="{{ route('admin.withdrawals.index') }}"
               class="flex justify-between items-center px-4 py-2 text-sm hover:bg-gray-100">

                <span>💳 Admin Withdrawals</span>

                @if($pendingWithdrawals > 0)
                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
                        {{ $pendingWithdrawals }}
                    </span>
                @endif
            </a>
			
			<a href="{{ route('admin.dashboard') }}"
		class="block px-4 py-2 text-sm hover:bg-gray-100">
		🛠 Admin Dashboard
		</a>
		
		@auth
    @if(auth()->user()->role === 'admin')

        <a href="{{ route('admin.kyc') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            KYC Verification
        </a>

    @endif
@endauth
			
			 {{-- Finance Overview --}}
    <a href="{{ route('admin.finance') }}"
       class="block px-4 py-2 text-sm hover:bg-gray-100">
       📊 Finance Overview
    </a>
	
	<a href="{{ route('admin.disputes.index') }}"
       class="flex justify-between items-center px-4 py-2 text-sm hover:bg-gray-100">
        <span>🛡 Disputes</span>

       @php
    $openDisputesCount = \App\Models\Dispute::whereIn('status',['open','under_review'])->count();
@endphp

@if($openDisputesCount > 0)
    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
        {{ $openDisputesCount }}
    </span>
@endif
    </a>
        @endif
	
        {{-- ================= END ADMIN ================= --}}

        <div class="border-t border-gray-200"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
			<a href="{{ route('profile.show') }}"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
    👤 Profile
</a>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit"
        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
        Logout
    </button>
</form>

</div>

           @else

    @if(Route::has('login'))
        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:underline">
            Login
        </a>
    @endif

    @if(Route::has('register'))
        <a href="{{ route('register') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-blue-700 transition">
            Sign Up
        </a>
    @endif

@endauth
            </div>
        </div>
    </div>
	{{-- SLIDE CART PANEL --}}
<div id="cart-panel"
     class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transition-transform duration-300 z-[9999]"
     style="transform: translateX(100%);">

    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="font-semibold text-lg">Your Cart</h3>
        <button type="button" id="close-cart" class="text-gray-500 text-xl">✕</button>
    </div>

    <div id="cart-panel-content" class="p-5 overflow-y-auto h-full pb-24">
        Loading...
    </div>

</div>

<div id="cart-overlay"
     class="fixed inset-0 bg-black/40 hidden z-[9998]"></div>
	 {{-- 🔔 SOUND --}}
<audio id="notifSound" src="/sounds/notification.mp3" preload="auto"></audio>

{{-- 🔔 ANIMATION --}}
<style>
@keyframes bellShake {
    0% { transform: rotate(0); }
    25% { transform: rotate(-10deg); }
    50% { transform: rotate(10deg); }
    75% { transform: rotate(-10deg); }
    100% { transform: rotate(0); }
}

.bell-animate {
    animation: bellShake 0.5s ease;
}
</style>
</nav>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ================= CART =================
    const toggle = document.getElementById('cart-toggle');
    const cartPanel = document.getElementById('cart-panel');
    const overlay = document.getElementById('cart-overlay');
    const closeBtn = document.getElementById('close-cart');

    function loadCartPanel() {
        fetch("{{ route('cart.index') }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const panelContent = document.getElementById('cart-panel-content');
            if (panelContent) panelContent.innerHTML = html;
        });
    }

    function closeCart() {
        if (cartPanel) cartPanel.style.transform = "translateX(100%)";
        if (overlay) overlay.classList.add('hidden');
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            console.log("Cart clicked");

            if (cartPanel) cartPanel.style.transform = "translateX(0)";
            if (overlay) overlay.classList.remove('hidden');

            loadCartPanel();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeCart);
    }

    if (overlay) {
        overlay.addEventListener('click', closeCart);
    }

    // ================= PROFILE DROPDOWN =================
    const profileToggle = document.getElementById('profile-toggle');
    const profileMenu = document.getElementById('profile-menu');

    if (profileToggle && profileMenu) {

        profileToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', function (e) {
            if (!profileMenu.contains(e.target) && !profileToggle.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });
    }

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const balanceEl = document.getElementById("walletBalance");

    let walletVisible = localStorage.getItem("walletVisible");

    if (walletVisible === "false") {
        balanceEl.innerText = "₦******";
    }

    // 👁 TOGGLE
    window.toggleWallet = function(e){
        e.preventDefault();
        e.stopPropagation();

        let isVisible = localStorage.getItem("walletVisible") !== "false";

        if (isVisible) {
            balanceEl.innerText = "₦******";
            localStorage.setItem("walletVisible", "false");
        } else {
            balanceEl.innerText = balanceEl.dataset.real;
            localStorage.setItem("walletVisible", "true");
        }
    };

    // ⋮ DROPDOWN
    window.toggleWalletDropdown = function(e){
        e.stopPropagation();

        let dropdown = document.getElementById("walletDropdown");
        dropdown.classList.toggle("hidden");
    };

    // CLOSE ON OUTSIDE CLICK
    document.addEventListener('click', function(){
        let dropdown = document.getElementById("walletDropdown");
        if (dropdown) dropdown.classList.add("hidden");
    });

});
</script>

<script>
document.getElementById('notifBtn').addEventListener('click', () => {
    const notifBtn = document.getElementById('notifBtn');

if (notifBtn) {
    notifBtn.addEventListener('click', () => {
        document.getElementById('notifDropdown').classList.toggle('hidden');
    });
}
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let lastCount = 0;

    const notifBtn = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');

    // ================= 🔔 CLICK (TOGGLE + MARK AS READ) =================
    if (notifBtn && dropdown) {
        notifBtn.addEventListener('click', () => {

            // Toggle dropdown
            dropdown.classList.toggle('hidden');

            // 🔥 MARK AS READ
            fetch('/notifications/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(() => {
                const badge = document.getElementById('notifCount');
                if (badge) {
                    badge.classList.add('hidden');
                    badge.innerText = 0;
                }
            })
            .catch(err => console.error('Mark read error:', err));
        });
    }

    // ================= 🔔 LOAD NOTIFICATIONS =================
    function loadNotifications() {
        fetch('/notifications/fetch', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {

            let html = '';
            let count = data.length;

            if (count === 0) {
                html = `<div class="p-3 text-gray-400 text-sm">No notifications</div>`;
            } else {
                data.slice(0,5).forEach(n => {
                    html += `
                    <div class="p-3 border-b hover:bg-gray-50 transition">
                        <div class="text-sm font-medium text-gray-800">
                            ${n.data.message}
                        </div>
                    </div>`;
                });
            }

            const list = document.getElementById('notifList');
            if (list) list.innerHTML = html;

            const badge = document.getElementById('notifCount');
            const sound = document.getElementById('notifSound');
            const bell = document.getElementById('notifBtn');

            // 🔥 NEW NOTIFICATION DETECTED
            if (count > lastCount) {

                // 🔊 SOUND
                if (sound) {
                    sound.currentTime = 0;
                    sound.play().catch(() => {});
                }

                // ✨ ANIMATION
                if (bell) {
                    bell.classList.add('bell-animate');
                    setTimeout(() => bell.classList.remove('bell-animate'), 500);
                }
            }

            lastCount = count;

            // 🔴 BADGE UPDATE
            if (badge) {
                badge.innerText = count;
                badge.classList.toggle('hidden', count === 0);
            }

        })
        .catch(err => console.error('Notification error:', err));
    }

    // Initial load
    loadNotifications();

    // Refresh every 8 seconds
    setInterval(loadNotifications, 8000);

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const balanceEl = document.getElementById("walletBalance");

    let walletVisible = localStorage.getItem("walletVisible");

    if (walletVisible === "false") {
        balanceEl.innerText = "₦******";
    }

    // 👁 TOGGLE
    window.toggleWallet = function(e){
        e.preventDefault();
        e.stopPropagation();

        let isVisible = localStorage.getItem("walletVisible") !== "false";

        if (isVisible) {
            balanceEl.innerText = "₦******";
            localStorage.setItem("walletVisible", "false");
        } else {
            balanceEl.innerText = balanceEl.dataset.real;
            localStorage.setItem("walletVisible", "true");
        }
    };

    // ⋮ DROPDOWN
    window.toggleWalletDropdown = function(e){
        e.stopPropagation();

        let dropdown = document.getElementById("walletDropdown");
        dropdown.classList.toggle("hidden");
    };

    // CLOSE ON OUTSIDE CLICK
    document.addEventListener('click', function(){
        let dropdown = document.getElementById("walletDropdown");
        if (dropdown) dropdown.classList.add("hidden");
    });

});
</script>

<script>
document.getElementById('notifBtn').addEventListener('click', () => {
    const notifBtn = document.getElementById('notifBtn');

if (notifBtn) {
    notifBtn.addEventListener('click', () => {
        document.getElementById('notifDropdown').classList.toggle('hidden');
    });
}
});
</script>
@endpush
