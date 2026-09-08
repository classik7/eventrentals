@extends('layouts.app')

@section('title', 'Event Rentals')

@section('content')

@php 
use Illuminate\Support\Facades\Storage;

function getItemImage($item) {
    // Check for new images (relationship)
    if ($item->images && $item->images->first()) {
        return Storage::url($item->images->first()->image);
    }
    // Check for old image (single column)
    elseif ($item->image) {
        return asset('storage/' . $item->image);
    }
    // Fallback placeholder
    else {
        return 'https://via.placeholder.com/600x400?text=Event+Rental';
    }
}
@endphp

{{-- ===== PREMIUM HERO SECTION ===== --}}
<section class="relative h-[40vh] w-full overflow-hidden mb-16">

    <!-- Background -->
    <div class="absolute inset-0">
        <img src="/images/hero-wedding.jpg"
             class="w-full h-full object-cover scale-105 animate-heroZoom"
             alt="Event Rentals">
        <div class="absolute inset-0 bg-black/60"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex items-center justify-center h-full px-4">
        <div class="text-center text-white max-w-4xl">

            <h1 class="fade-up text-4xl md:text-6xl font-bold leading-tight mb-6">
                Find the perfect rentals for your event
            </h1>

            <p class="fade-up fade-delay-1 text-lg md:text-xl text-gray-200 mb-8">
                Chairs, décor, sound systems, canopies and more — all in one place.
            </p>

            <form method="GET"
                  action="{{ route('items.index') }}"
                  class="fade-up fade-delay-2 bg-white rounded-2xl shadow-xl flex flex-col md:flex-row gap-3 p-3 items-center max-w-4xl mx-auto">

                {{-- State --}}
                <select name="state"
                        id="stateFilter"
                        class="px-4 py-3 rounded-lg border border-gray-300 text-gray-700 w-full md:w-auto">

                    <option value="">All States</option>

                    @foreach([
                        "Abia","Adamawa","Akwa Ibom","Anambra","Bauchi","Bayelsa","Benue","Borno",
                        "Cross River","Delta","Ebonyi","Edo","Ekiti","Enugu","FCT","Gombe","Imo",
                        "Jigawa","Kaduna","Kano","Katsina","Kebbi","Kogi","Kwara","Lagos",
                        "Nasarawa","Niger","Ogun","Ondo","Osun","Oyo","Plateau","Rivers",
                        "Sokoto","Taraba","Yobe","Zamfara"
                    ] as $state)
                        <option value="{{ $state }}"
                            {{ request('state') == $state ? 'selected' : '' }}>
                            {{ $state }}
                        </option>
                    @endforeach

                </select>

                {{-- LGA --}}
                <select name="local_government"
                        id="lgaFilter"
                        class="px-4 py-3 rounded-lg border border-gray-300 text-gray-700 w-full md:w-auto">
                    <option value="">All LGAs</option>
                </select>

                {{-- Search --}}
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search items..."
                       class="flex-1 px-4 py-3 rounded-lg border border-gray-300 text-gray-700 w-full">
					   
				<div class="w-full md:w-64 px-4">
                    <div id="price-slider"></div>

                    <div class="flex justify-between text-sm mt-2 text-gray-700">
                        <span id="price-min-display"></span>
                        <span id="price-max-display"></span>
                    </div>

                    <input type="hidden" name="min_price" id="min_price">
                    <input type="hidden" name="max_price" id="max_price">
                </div>

                {{-- Sort By --}}
                <select name="sort"
                        class="px-4 py-3 rounded-lg border border-gray-300 text-gray-700 w-full md:w-auto">
                    <option value="">Sort By</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                        Lowest Price
                    </option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                        Highest Price
                    </option>
                    <option value="most_booked" {{ request('sort') == 'most_booked' ? 'selected' : '' }}>
                        Most Booked
                    </option>
                </select>

                <button class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg font-semibold transition w-full md:w-auto">
                    Search
                </button>

            </form>

        </div>
    </div>

</section>
{{-- ===== END PREMIUM HERO ===== --}}

<div class="max-w-7xl mx-auto px-4 py-6">

{{-- ===== ULTRA PREMIUM CATEGORY GRID ===== --}}
<div id="category-section" class="mb-16">

    <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">
        Browse by Category
    </h2>

    @php
    $icons = [
        'Decoration' => '🎊',
        'Chairs' => '🪑',
        'Tables' => '🍽️',
        'Wedding Gown' => '👰',
        'Sound System' => '🔊',
        'Lighting' => '💡',
        'Canopy' => '⛺',
        'Generator' => '⚡',
        'Birthday' => '🎂',
        'Conference' => '🎤',
    ];
    @endphp

    <div class="relative">

        {{-- Category Grid --}}
        <div id="category-wrapper"
             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6
                    max-h-[260px] overflow-hidden
                    transition-all duration-500 ease-in-out">
		
		@php
    $activeCategory = request('category');

    $sortedCategories = $categories->sortByDesc(function($cat) use ($activeCategory) {
        return $cat->id == $activeCategory;
    });
@endphp


            @foreach($sortedCategories as $category)


                <a href="{{ route('items.index', ['category' => $category->id]) }}"
   class="group relative p-5 rounded-2xl
          border shadow-sm transition-all duration-300
          flex flex-col items-center justify-center text-center overflow-hidden

          {{ request('category') == $category->id
                ? 'bg-green-100 border-green-500 ring-2 ring-green-400 shadow-lg'
                : 'bg-gradient-to-br from-white to-purple-50 border-purple-100 hover:shadow-xl hover:-translate-y-2'
          }}">


                    {{-- Hover Glow --}}
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100
                                bg-gradient-to-r from-purple-500/10 to-pink-500/10
                                transition duration-500 rounded-2xl"></div>

                    {{-- Icon --}}
                   <div class="relative z-10 w-14 h-14 rounded-full
    flex items-center justify-center text-2xl transition-all duration-300

    {{ request('category') == $category->id
        ? 'bg-purple-600 text-white'
        : 'bg-purple-100 group-hover:bg-purple-600 group-hover:text-white'
    }}">


                        {{ $icons[$category->name] ?? '✨' }}

                    </div>

                    {{-- Name --}}
                    <h3 class="relative z-10 mt-3 font-semibold text-gray-800
                               group-hover:text-purple-700 transition">
                        {{ $category->name }}
                    </h3>

                    {{-- Underline Animation --}}
                    <span class="relative z-10 mt-2 w-0 h-1 bg-purple-600
                                 group-hover:w-8 transition-all duration-300 rounded-full">
                    </span>

                </a>

            @endforeach

        </div>

        {{-- Fade Effect --}}
        <div id="category-fade"
             class="absolute bottom-0 left-0 w-full h-20
                    bg-gradient-to-t from-white to-transparent
                    pointer-events-none transition-opacity duration-500">
        </div>

    </div>

    {{-- View Toggle Button --}}
    <div class="text-center mt-8">
        <button id="toggle-categories"
                class="px-6 py-2 rounded-full
                       bg-purple-600 text-white
                       hover:bg-purple-700
                       transition-all duration-300
                       shadow-md hover:shadow-lg">
            View All Categories
        </button>
    </div>

</div>
{{-- ===== END ULTRA PREMIUM CATEGORY GRID ===== --}}


{{-- 🧠 AI BUDGET PLANNER --}}
<div class="grid md:grid-cols-4 gap-4">

    <select id="event_type" class="border rounded-lg p-3">
        <option value="wedding">Wedding</option>
        <option value="birthday">Birthday</option>
        <option value="burial">Burial</option>
        <option value="conference">Conference</option>
		<option value="OTHERS">OTHERS</option>
    </select>

    <input type="number" id="budget" placeholder="Budget (₦)" class="border rounded-lg p-3">
    <input type="number" id="guests" placeholder="Number of Guests" class="border rounded-lg p-3">
    <input type="number" id="days" placeholder="Days" value="1" class="border rounded-lg p-3">
	<div class="mb-4">
    <label class="text-sm font-medium block mb-1">
        Event Start Date
    </label>

    <input type="date"
           id="start_date"
           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
</div>

    <button id="generate-plan"
            class="bg-purple-600 text-white rounded-lg p-3 col-span-full md:col-span-4">
        Generate Smart Plan
    </button>
</div>

<div id="ai-results" class="mt-8 hidden">
    <h3 class="font-semibold mb-4">Suggested Items</h3>
    <div id="ai-items" class="grid md:grid-cols-3 gap-4"></div>

    <div class="mt-6 border-t pt-4 space-y-2">
        <p class="text-sm text-gray-600">
            Total Used:
            <span id="ai-total" class="font-semibold text-black">0</span>
        </p>

        <p class="text-sm text-gray-600">
            Remaining:
            <span id="ai-remaining" class="font-semibold text-green-600">0</span>
        </p>

        @auth
        <button id="add-all-to-cart"
            class="mt-3 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition">
            Add All To Cart
        </button>
        @endauth
    </div>
</div>

{{-- 🔥 TRENDING SECTION --}}
@if(isset($trendingItems) && $trendingItems->count())

<div class="mb-16">

    <div class="flex items-center justify-between cursor-pointer section-toggle"
         data-target="trending-section">

        <h2 class="text-2xl font-bold flex items-center gap-2">
            🔥 Trending This Week
        </h2>

        <span class="text-xl transition-transform duration-300 arrow">
            ▾
        </span>
    </div>

    {{-- IMPORTANT WRAPPER --}}
    <div id="trending-section" class="section-content mt-6">

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-6">

            @foreach($trendingItems as $item)

                <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden border">

                    <a href="{{ route('items.show', $item) }}">
                       <img
    src="{{ getItemImage($item) }}"
    alt="{{ $item->title }}"
    class="w-full h-40 object-cover"
>
                    </a>

                    <div class="p-4 space-y-2">

                        <h3 class="font-semibold text-gray-900 truncate flex items-center gap-2">
                            {{ $item->title }}

                            @if($item->user && $item->user->is_verified)
                                <span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">
                                    ✓ Verified
                                </span>
                            @endif
                        </h3>
						
					<a href="{{ route('owners.show', $item->user->id) }}"
						class="text-sm font-semibold text-blue-600 hover:underline">
						👤 {{ $item->user->name }}
					</a>

                        @php
                            $hasDiscount = $item->original_price 
                                           && $item->original_price > $item->price_per_day;

                            if($hasDiscount) {
                                $discountPercent = round(
                                    (($item->original_price - $item->price_per_day) 
                                    / $item->original_price) * 100
                                );
                            }
                        @endphp

                        @if($hasDiscount)

                            <div class="space-y-1">

                                <div class="flex items-center gap-2">

                                    <span class="text-gray-400 line-through text-sm">
                                        ₦{{ number_format($item->original_price) }}
                                    </span>

                                    <span class="text-green-600 font-bold text-lg">
                                        ₦{{ number_format($item->price_per_day) }}
                                    </span>

                                    <span class="bg-red-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                                        -{{ $discountPercent }}%
                                    </span>

                                </div>

                                <span class="text-xs text-red-500 font-medium">
                                    🔥 Limited Offer
                                </span>

                            </div>

                        @else

                            <p class="text-green-600 font-semibold text-lg">
                                ₦{{ number_format($item->price_per_day) }}
                                <span class="text-sm font-normal text-gray-500">/ day</span>
                            </p>

                        @endif

                        <p class="text-xs text-orange-500">
                            🔥 {{ $item->rentals_count }} bookings
                        </p>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

@endif

@if(isset($feedItems) && $feedItems->count())
<div class="mb-16">

    <h2 class="text-2xl font-bold mb-4">
        ❤️ From Vendors You Follow
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

        @foreach($feedItems as $item)
            <div class="bg-white rounded-xl shadow p-3">

                <img src="{{ getItemImage($item) }}"
                     class="w-full h-40 object-cover rounded-lg">

                <p class="font-semibold mt-2 truncate">
                    {{ $item->title }}
                </p>

                <p class="text-green-600 text-sm">
                    ₦{{ number_format($item->price_per_day) }}
                </p>

            </div>
        @endforeach

    </div>
</div>
@endif

{{-- ===== 🆕 RECENTLY ADDED ===== --}}
@if(isset($recentItems) && $recentItems->count())

<div class="mb-16">

    <div class="flex items-center justify-between cursor-pointer section-toggle"
         data-target="recent-section">

        <h2 class="text-2xl font-bold flex items-center gap-2">
            🆕 Recently Added
        </h2>

        <span class="text-xl transition-transform duration-300 arrow">
            ▾
        </span>
    </div>

    <div id="recent-section" class="mt-6 section-content">

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-6">

            @foreach($recentItems as $item)

                <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden border">

                    <a href="{{ route('items.show', $item) }}">
                        <img
                            src="{{ getItemImage($item) }}"
                            alt="{{ $item->title }}"
                            class="w-full h-40 object-cover"
                        >
                    </a>

                    <div class="p-4 space-y-2">

                        {{-- TITLE --}}
                        <h3 class="font-semibold truncate text-gray-900">
                            {{ $item->title }}
                        </h3>

                        {{-- ✅ NEW: VENDOR LINK --}}
                        <a href="{{ route('owners.show', $item->user->id) }}"
                           class="text-sm text-gray-500 hover:text-blue-600">
                            👤 {{ $item->user->name }}
                        </a>

                        {{-- PRICE --}}
                        <p class="text-green-600 font-semibold text-sm">
                            ₦{{ number_format($item->price_per_day) }} / day
                        </p>

                        {{-- DATE --}}
                        <p class="text-xs text-gray-500">
                            Added {{ $item->created_at->diffForHumans() }}
                        </p>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

@endif
{{-- ===== 🛍 ALL RENTALS ===== --}}
@if($items->count())

<div class="mb-16">

    <div class="flex items-center justify-between cursor-pointer section-toggle"
         data-target="all-listings">

        <h2 class="text-2xl font-bold flex items-center gap-2">
            🛍 All Rentals
        </h2>

        <span class="text-xl transition-transform duration-300 arrow">
            ▾
        </span>
    </div>

    <div id="all-listings" class="mt-6 section-content">

        <div class="fade-up fade-delay-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

            @foreach($items as $item)

                <div class="relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden">

                    @auth
                    <div class="absolute top-3 right-3 z-20">
                        <button
                            class="wishlist-btn text-2xl drop-shadow-md transition transform hover:scale-110"
                            data-item="{{ $item->id }}">
                            @if(auth()->user()->wishlist->where('item_id', $item->id)->count())
                                ❤️
                            @else
                                🤍
                            @endif
                        </button>
                    </div>
                    @endauth

                    <a href="{{ route('items.show', $item) }}">
                        <img
                            src="{{ getItemImage($item) }}"
                            alt="{{ $item->title }}"
                            class="w-full h-56 object-cover">
                    </a>

                    <div class="p-4 space-y-2">

                        {{-- TITLE --}}
                        <h3 class="font-semibold text-gray-900 truncate">
                            {{ $item->title }}
                        </h3>

                        {{-- ✅ VENDOR LINK --}}
                        @if($item->user)
                        <a href="{{ route('owners.show', $item->user->id) }}"
                           class="text-sm text-gray-500 hover:text-blue-600">
                            👤 {{ $item->user->name }}
                        </a>
                        @endif

                        {{-- CATEGORY --}}
                        <p class="text-sm text-gray-500">
                            {{ optional($item->category)->name }}
                        </p>

                        {{-- PRICE --}}
                        <p class="text-green-600 font-semibold">
                            ₦{{ number_format($item->price_per_day) }}
                            <span class="text-sm font-normal text-gray-500">/ day</span>
                        </p>

                        {{-- LOCATION --}}
                        <p class="text-sm text-gray-500">
                            📍 {{ $item->location }}
                        </p>

                        {{-- CTA --}}
                        <a href="{{ route('items.show', $item) }}"
                           class="inline-block mt-3 text-blue-600 text-sm font-medium hover:underline">
                            Check availability →
                        </a>

                    </div>
                </div>

            @endforeach

        </div>

    </div>

</div>

@else
<div class="text-center text-gray-500 py-20">
    No items found.
</div>
@endif


{{-- ===== Styles ===== --}}
@push('styles')
<style>
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.fade-up { opacity: 0; animation: fadeUp 0.8s ease forwards; }
.fade-delay-1 { animation-delay: 0.2s; }
.fade-delay-2 { animation-delay: 0.4s; }
.fade-delay-3 { animation-delay: 0.6s; }

@keyframes heroZoom {
    from { transform: scale(1.1); }
    to { transform: scale(1); }
}
.animate-heroZoom { animation: heroZoom 8s ease-out forwards; }
</style>
@endpush

{{-- ===== Scripts ===== --}}
@push('scripts')

<script>
// FULL Nigeria LGA data is already in your create page.
// For homepage we only need dynamic mapping if state selected.
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const slider = document.getElementById('price-slider');

    if (slider) {

        const dbMaxPrice = {{ $maxPrice }};
        const minPrice = {{ request('min_price', 0) }};
        const maxPrice = {{ request('max_price') ?? $maxPrice }};

        noUiSlider.create(slider, {
            start: [minPrice, maxPrice],
            connect: true,
            step: 1000,
            range: {
                'min': 0,
                'max': dbMaxPrice
            }
        });

        const minInput = document.getElementById('min_price');
        const maxInput = document.getElementById('max_price');
        const minDisplay = document.getElementById('price-min-display');
        const maxDisplay = document.getElementById('price-max-display');

        slider.noUiSlider.on('update', function (values) {

            const min = Math.round(values[0]);
            const max = Math.round(values[1]);

            minInput.value = min;
            maxInput.value = max;

            minDisplay.innerHTML = "₦" + min.toLocaleString();
            maxDisplay.innerHTML = "₦" + max.toLocaleString();
        });
    }

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const lgaData = {!! json_encode(
        \App\Models\Item::select('state','local_government')
        ->distinct()
        ->get()
        ->groupBy('state')
        ->map(fn($group) => $group->pluck('local_government'))
    ) !!};

    const stateFilter = document.getElementById('stateFilter');
    const lgaFilter = document.getElementById('lgaFilter');

    function populateLGA(selectedState, selectedLGA = null) {

        lgaFilter.innerHTML = '<option value="">All LGAs</option>';

        if (lgaData[selectedState]) {
            lgaData[selectedState].forEach(function(lga) {

                const option = document.createElement('option');
                option.value = lga;

                let count = 0;

                @if(isset($lgaCounts))
                    const lgaCounts = @json($lgaCounts);
                    if (lgaCounts[lga]) {
                        count = lgaCounts[lga];
                    }
                @endif

                option.textContent = count ? `${lga} (${count})` : lga;

                if (selectedLGA && selectedLGA === lga) {
                    option.selected = true;
                }

                lgaFilter.appendChild(option);
            });
        }
    }

    const selectedState = "{{ request('state') }}";
    const selectedLGA = "{{ request('local_government') }}";

    if (selectedState) {
        populateLGA(selectedState, selectedLGA);
    }

    stateFilter.addEventListener('change', function() {
        populateLGA(this.value);
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.wishlist-btn').forEach(button => {

        button.addEventListener('click', function (e) {

            e.preventDefault();
            e.stopPropagation();

            const itemId = this.dataset.item;

            fetch("{{ url('/wishlist') }}/" + itemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {

                const badge = document.getElementById('wishlist-count');
                if (!badge) return;

                let rawValue = badge.innerText.replace(/,/g, '');
                let current = Number(rawValue) || 0;

                if (data.status === 'added') {
                    this.innerHTML = '❤️';
                    current++;
                    badge.innerText = current.toLocaleString();
                    badge.classList.remove('hidden');
                }

                if (data.status === 'removed') {
                    this.innerHTML = '🤍';
                    current--;
                    if (current <= 0) {
                        badge.innerText = '';
                        badge.classList.add('hidden');
                    } else {
                        badge.innerText = current.toLocaleString();
                    }
                }

            })
            .catch(error => console.error('Wishlist error:', error));

        });

    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const generateBtn = document.getElementById('generate-plan');
    const addAllBtn = document.getElementById('add-all-to-cart');

    if (!generateBtn) return;

    let originalBudget = 0;

    function recalculateTotals() {

        let total = 0;

        document.querySelectorAll('.ai-item').forEach(card => {

            const price = Number(card.dataset.price);
            const qty = Number(card.querySelector('.quantity').innerText);

            total += price * qty;
        });

        document.getElementById('ai-total').innerText = total.toLocaleString();

        const remaining = originalBudget - total;

        document.getElementById('ai-remaining').innerText = remaining.toLocaleString();
    }

    // 🔥 GENERATE PLAN
    generateBtn.addEventListener('click', function () {

        const eventType = document.getElementById('event_type').value;
        const budget = document.getElementById('budget').value;
        const guests = document.getElementById('guests').value;
        const days = document.getElementById('days').value;

        if (!budget) {
            alert("Please enter a budget.");
            return;
        }

        originalBudget = Number(budget);

        fetch("{{ route('ai.plan') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                event_type: eventType,
                budget: budget,
                guest_count: guests,
                days: days
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => {
                    console.error("Server Error:", text);
                    throw new Error("Server returned error");
                });
            }
            return res.json();
        })
        .then(data => {

            const container = document.getElementById('ai-items');
            container.innerHTML = '';

            data.items.forEach(item => {

                container.innerHTML += `
                    <div class="border rounded-xl p-4 relative ai-item"
                         data-price="${item.price_per_day}"
                         data-id="${item.id}">

                        <button class="absolute top-2 right-2 text-red-500 remove-item">
                            ✖
                        </button>

                        <h4 class="font-semibold mb-2">${item.title}</h4>

                        <div class="flex items-center gap-3 mb-2">
                            <button class="decrease bg-gray-200 px-3 py-1 rounded">-</button>
                            <span class="quantity font-semibold">${item.quantity ?? 1}</span>
                            <button class="increase bg-gray-200 px-3 py-1 rounded">+</button>
                        </div>

                        <p class="text-green-600 font-semibold">
                            ₦${Number(item.price_per_day).toLocaleString()}
                        </p>
                    </div>
                `;
            });

            document.getElementById('ai-total').innerText = Number(data.total_used).toLocaleString();
            document.getElementById('ai-remaining').innerText = Number(data.remaining).toLocaleString();

            document.getElementById('ai-results').classList.remove('hidden');
        })
        .catch(error => console.error("AI Planner error:", error));
    });

if (addAllBtn) {

    addAllBtn.addEventListener('click', function () {

        const items = [];

        document.querySelectorAll('.ai-item').forEach(card => {

            const id = card.dataset.id;
            const qty = Number(card.querySelector('.quantity').innerText);

            items.push({
                id: id,
                quantity: qty
            });
        });

        if (items.length === 0) {
            alert("Generate a smart plan first.");
            return;
        }

        // ✅ GET START DATE
        const startInput = document.getElementById('start_date');
        const daysInput = document.getElementById('days');

        if (!startInput || !startInput.value) {
            alert("Please select a start date.");
            return;
        }

        const days = parseInt(daysInput.value);

        if (!days || days < 1) {
            alert("Invalid number of days.");
            return;
        }

        // ✅ AUTO CALCULATE END DATE FROM DAYS
        const startDateObj = new Date(startInput.value);
        const endDateObj = new Date(startDateObj);
        endDateObj.setDate(endDateObj.getDate() + (days - 1));

        const startDate = startInput.value;
        const endDate = endDateObj.toISOString().split('T')[0];

        fetch("{{ route('ai.addToCart') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
            },
            body: JSON.stringify({
                items: items,
                start_date: startDate,
                end_date: endDate
            })
        })
        .then(res => {

            if (res.status === 401) {
                alert("Please login to add items to cart.");
                window.location.href = "/login";
                return;
            }

            if (!res.ok) {
                return res.text().then(text => {
                    console.error("Server Error:", text);
                    throw new Error("Server returned error");
                });
            }

            return res.json();
        })
        .then(data => {
    if (data && data.success) {

        alert("Items successfully added to cart!");

        // ✅ UPDATE CART BADGE COUNT
        const badge = document.querySelector('#cart-toggle span.absolute');

        if (badge) {
            const currentCount = parseInt(badge.innerText || 0);
            badge.innerText = currentCount + items.length;
        } else {
            // If badge doesn't exist yet, create it
            const cartBtn = document.getElementById('cart-toggle');

            const newBadge = document.createElement('span');
            newBadge.className = "absolute -top-2 -right-2 bg-red-600 text-white text-xs px-2 py-0.5 rounded-full";
            newBadge.innerText = items.length;

            cartBtn.appendChild(newBadge);
        }

    }
})
        .catch(error => console.error("Add to cart error:", error));

    });
}

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggles = document.querySelectorAll('.section-toggle');

    toggles.forEach(toggle => {

        toggle.addEventListener('click', function () {

            const targetId = this.getAttribute('data-target');
            const content = document.getElementById(targetId);
            const arrow = this.querySelector('.arrow');

            if (!content) return;

            content.classList.toggle('hidden');

            if (content.classList.contains('hidden')) {
                arrow.style.transform = 'rotate(-90deg)';
            } else {
                arrow.style.transform = 'rotate(0deg)';
            }
        });

    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const wrapper = document.getElementById('category-wrapper');
    const fade = document.getElementById('category-fade');
    const toggleBtn = document.getElementById('toggle-categories');

    if (!wrapper || !toggleBtn) return;

    let expanded = false;

    toggleBtn.addEventListener('click', function () {

        if (!expanded) {

            // Expand
            wrapper.classList.remove('max-h-[260px]', 'overflow-hidden');
            if (fade) fade.style.opacity = 0;

            toggleBtn.innerText = "Show Less";
            expanded = true;

        } else {

            // Collapse
            wrapper.classList.add('max-h-[260px]', 'overflow-hidden');
            if (fade) fade.style.opacity = 1;

            toggleBtn.innerText = "View All Categories";
            expanded = false;
        }

    });

});
</script>

@endpush

@endsection
