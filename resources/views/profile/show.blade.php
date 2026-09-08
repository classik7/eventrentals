@extends('layouts.app')

@section('title', $user->name)

@section('content')

<div class="max-w-5xl mx-auto mt-6 bg-white rounded-2xl shadow overflow-hidden">

    {{-- COVER --}}
    <div class="relative">
        @if($user->cover_photo)
            <img src="{{ asset('storage/'.$user->cover_photo) }}"
                 class="w-full h-48 object-cover">
        @else
            <div class="w-full h-48 bg-gray-200"></div>
        @endif

        {{-- AVATAR --}}
        <div class="absolute -bottom-10 left-6">
            @if($user->profile_photo)
                <img src="{{ asset('storage/'.$user->profile_photo) }}"
                     class="w-24 h-24 rounded-full border-4 border-white object-cover">
            @else
                <div class="w-24 h-24 rounded-full bg-gray-400 flex items-center justify-center text-white text-2xl">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>
    </div>

    {{-- INFO --}}
    <div class="pt-14 px-6 pb-6">

        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold flex items-center gap-2">
                    {{ $user->name }}

                    {{-- 🔵 VERIFIED BADGE (TIER 3) --}}
                    @if($user->kyc_tier == 3)
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5 text-blue-500"
                             viewBox="0 0 24 24"
                             fill="currentColor">
                            <path d="M22.5 12c0-1.1-.9-2-2-2h-.3c-.2-.7-.5-1.4-.9-2l.2-.2c.8-.8.8-2 0-2.8l-1.4-1.4c-.8-.8-2-.8-2.8 0l-.2.2c-.6-.4-1.3-.7-2-.9V3.5c0-1.1-.9-2-2-2h-2c-1.1 0-2 .9-2 2v.3c-.7.2-1.4.5-2 .9l-.2-.2c-.8-.8-2-.8-2.8 0L2.1 5.9c-.8.8-.8 2 0 2.8l.2.2c-.4.6-.7 1.3-.9 2H1.5c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2h.3c.2.7.5 1.4.9 2l-.2.2c-.8.8-.8 2 0 2.8l1.4 1.4c.8.8 2 .8 2.8 0l.2-.2c.6.4 1.3.7 2 .9v.3c0 1.1.9 2 2 2h2c1.1 0 2-.9 2-2v-.3c.7-.2 1.4-.5 2-.9l.2.2c.8.8 2 .8 2.8 0l1.4-1.4c.8-.8.8-2 0-2.8l-.2-.2c.4-.6.7-1.3.9-2h.3c1.1 0 2-.9 2-2v-2zM10 15l-3-3 1.4-1.4L10 12.2l5.6-5.6L17 8l-7 7z"/>
                        </svg>
                    @endif
                </h2>

                {{-- 🔥 TIER BADGE --}}
                <div class="text-sm mt-1">
                    @if($user->kyc_tier == 1)
                        <span class="text-yellow-600 font-medium">🟡 Tier 1 (Basic)</span>
                    @elseif($user->kyc_tier == 2)
                        <span class="text-green-600 font-medium">🟢 Tier 2 Verified</span>
                    @elseif($user->kyc_tier == 3)
                        <span class="text-blue-600 font-medium">🔵 Tier 3 Verified</span>
                    @else
                        <span class="text-red-500">🔴 Unverified</span>
                    @endif
                </div>
            </div>

            @if(auth()->id() == $user->id)
                <a href="{{ route('profile.edit') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded">
                   Edit Profile
                </a>
            @endif
        </div>

        {{-- BIO --}}
        <div class="mt-4 text-gray-600">
            {{ $user->bio ?? 'No bio added yet.' }}
        </div>

        {{-- 🔥 KYC TIERS --}}
<div class="mt-6 p-4 border rounded-lg bg-gray-50">

    <h3 class="font-bold mb-2">Account Verification Levels</h3>

    <p class="mb-3 text-sm text-gray-600">
        Your level determines how your payments are released when you get booked.
    </p>

    <p class="mb-4">
        Current:
        <span class="font-semibold text-blue-600">
            Tier {{ $user->kyc_tier }}
        </span>
    </p>

    {{-- 🔹 TIER 1 --}}
    <div class="mb-4">
        <div class="text-yellow-600 font-medium">
            🟡 Tier 1 — Basic Account
        </div>
        <p class="text-xs text-gray-600 ml-2">
            ✔ 20% payment before job<br>
            ✔ 80% payment after job completion<br>
            ✔ Best for new users getting started
        </p>
    </div>

    {{-- 🔹 TIER 2 --}}
    @if($user->kyc_tier < 2)
        <a href="{{ route('kyc.tier2') }}"
           class="block mb-4 p-2 rounded hover:bg-gray-100 transition">
            <div class="text-green-600 font-medium">
                🔓 Upgrade to Tier 2 — ID + Selfie
            </div>
            <p class="text-xs text-gray-600 ml-2">
                ✔ 40% payment before job<br>
                ✔ 60% after job completion<br>
                ✔ Get paid earlier & build more trust
            </p>
        </a>
    @else
        <div class="mb-4 text-green-600 font-medium">
            🟢 Tier 2 Verified
        </div>
    @endif

    {{-- 🔹 TIER 3 --}}
    @if($user->kyc_tier < 3)
        <a href="{{ route('kyc.tier3') }}"
           class="block p-2 rounded hover:bg-gray-100 transition">
            <div class="text-blue-600 font-medium">
                🔓 Upgrade to Tier 3 — Premium Verification
            </div>
            <p class="text-xs text-gray-600 ml-2">
                ✔ 70% payment before job<br>
                ✔ 30% after job completion<br>
                ✔ Fastest payout + premium trust badge
            </p>
        </a>
    @else
        <div class="text-blue-600 font-medium">
            🔵 Tier 3 Fully Verified
        </div>
    @endif

</div>

        {{-- STATS --}}
        <div class="mt-6 grid grid-cols-3 gap-4 text-center">

            <div class="bg-gray-100 p-3 rounded">
                <div class="font-bold text-lg">{{ $user->jobs_completed }}</div>
                <div class="text-sm text-gray-500">Jobs</div>
            </div>

            <div class="bg-gray-100 p-3 rounded">
                <div class="font-bold text-lg">{{ $user->rating }}</div>
                <div class="text-sm text-gray-500">Rating</div>
            </div>

            <div class="bg-gray-100 p-3 rounded">
                <div class="font-bold text-lg">
                    ₦{{ number_format($user->wallet_available ?? 0, 2) }}
                </div>
                <div class="text-sm text-gray-500">Wallet</div>
            </div>

        </div>

    </div>

</div>

@endsection