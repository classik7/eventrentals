@extends('layouts.app')

@section('title', $owner->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- ===== PROFILE HEADER ===== --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden mb-10">

        {{-- COVER PHOTO --}}
        <div class="h-40 bg-gray-200 relative">
            @if($owner->cover_photo)
                <img src="{{ asset('storage/' . $owner->cover_photo) }}"
                     class="w-full h-full object-cover">
            @endif
        </div>

        {{-- PROFILE INFO --}}
        <div class="p-6 flex flex-col sm:flex-row sm:items-center gap-6">

            {{-- AVATAR --}}
            @if($owner->profile_photo)
                <img src="{{ asset('storage/' . $owner->profile_photo) }}"
                     class="w-24 h-24 rounded-full border-4 border-white -mt-16 bg-white object-cover">
            @else
                <div class="w-24 h-24 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-3xl font-semibold -mt-16">
                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                </div>
            @endif

            {{-- INFO --}}
            <div class="flex-1">

                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
				@auth
<form method="POST" action="{{ route('vendor.follow', $owner->id) }}">
    @csrf

    @php
        $isFollowing = \App\Models\VendorFollow::where('user_id', auth()->id())
            ->where('vendor_id', $owner->id)
            ->exists();
    @endphp

    <button type="submit"
        class="mt-3 px-4 py-2 rounded-lg text-sm font-semibold transition
        {{ $isFollowing 
            ? 'bg-gray-200 text-gray-700' 
            : 'bg-blue-600 text-white hover:bg-blue-700' }}">
        
        {{ $isFollowing ? 'Following ✓' : '+ Follow Vendor' }}
    </button>
</form>
@endauth
                    {{ $owner->name }}
				
			

                    @if($owner->kyc_status === 'verified')
                        <span class="text-blue-600">✔</span>
                    @endif
                </h1>

                <p class="text-gray-600 mt-1">
                    📍 {{ $items->first()->location ?? 'Nigeria' }}
                </p>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $items->count() }} item{{ $items->count() !== 1 ? 's' : '' }} listed
                </p>

                <p class="text-sm text-gray-500 mt-2">
                    {{ $owner->bio ?? 'No description provided yet.' }}
                </p>

                {{-- STATS INLINE --}}
                <div class="flex gap-4 mt-3 text-sm">
                    <span class="text-green-600 font-semibold">
                        ⭐ {{ number_format($averageRating, 1) }}
                    </span>

                    <span class="text-gray-600">
                        📦 {{ $completedTransactions }} completed
                    </span>

                    <span class="text-blue-600">
                        🏆 Tier {{ $tier }}
                    </span>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== TRUST BADGES ===== --}}
    <div class="flex flex-wrap gap-3 mb-8">

        @if($owner->kyc_status === 'verified')
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                ✔ Verified Vendor
            </span>
        @endif

        @if($completedTransactions > 5)
            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm">
                🔥 Trusted Seller
            </span>
        @endif

        @if($averageRating >= 4)
            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                ⭐ Top Rated
            </span>
        @endif

    </div>

    {{-- ===== STATS CARDS ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-10">

        <div class="bg-white p-5 rounded-xl shadow text-center">
            <p class="text-gray-500 text-sm">Completed Jobs</p>
            <p class="text-xl font-bold">{{ $completedTransactions }}</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow text-center">
            <p class="text-gray-500 text-sm">Rating</p>
            <p class="text-xl font-bold">⭐ {{ number_format($averageRating, 1) }}</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow text-center">
            <p class="text-gray-500 text-sm">Tier</p>
            <p class="text-xl font-bold">Tier {{ $tier }}</p>
        </div>

    </div>

    {{-- ===== ITEMS SECTION ===== --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900">
            Items by {{ $owner->name }}
        </h2>
        <p class="text-gray-600 mt-1">
            Browse all rentals offered by this vendor.
        </p>
    </div>

    @if($items->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

            @foreach($items as $item)
                <div
                    class="bg-white rounded-2xl border border-gray-200
                           shadow-sm hover:shadow-xl hover:-translate-y-1 transition overflow-hidden">

                    {{-- IMAGE --}}
                    <a href="{{ route('items.show', $item) }}">
                        <img
                            src="{{ $item->image
                                ? asset('storage/'.$item->image)
                                : 'https://via.placeholder.com/600x400?text=Event+Rental' }}"
                            alt="{{ $item->title }}"
                            class="w-full h-56 object-cover"
                        >
                    </a>

                    {{-- CONTENT --}}
                    <div class="p-4 space-y-2">

                        <h3 class="font-semibold text-gray-900 truncate">
                            {{ $item->title }}
                        </h3>

                        <p class="text-sm text-gray-500">
                            {{ optional($item->category)->name }}
                        </p>

                        <p class="text-green-600 font-semibold">
                            ₦{{ number_format($item->price_per_day) }}
                            <span class="text-sm font-normal text-gray-500">/ day</span>
                        </p>

                        <p class="text-sm text-gray-500">
                            📍 {{ $item->location }}
                        </p>

                        <a
                            href="{{ route('items.show', $item) }}"
                            class="inline-block mt-3 text-blue-600 text-sm font-medium hover:underline">
                            Check availability →
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
    @else
        <div class="text-center text-gray-500 py-20">
            This owner has no active listings.
        </div>
    @endif

</div>
@endsection