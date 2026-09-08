@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', $item->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">


{{-- LEFT: IMAGE --}}
<div class="mb-6">
    @php
        $mainImage = $item->images->first() 
            ? Storage::url($item->images->first()->image) 
            : ($item->image 
                ? asset('storage/'.$item->image) 
                : asset('images/no-image.png'));
    @endphp
    
    <img id="main_image"
         src="{{ $mainImage }}"
         class="w-full h-96 object-cover rounded-lg shadow">
</div>

<div class="grid grid-cols-4 gap-3">
    @if($item->images->count())
        @foreach($item->images as $image)
            <img src="{{ Storage::url($image->image) }}"
                 class="cursor-pointer rounded-lg border hover:opacity-75 transition transform hover:scale-105"
                 onclick="changeImage('{{ Storage::url($image->image) }}')">
        @endforeach
    @elseif($item->image)
        <img src="{{ asset('storage/'.$item->image) }}"
             class="cursor-pointer rounded-lg border hover:opacity-75 transition transform hover:scale-105"
             onclick="changeImage('{{ asset('storage/'.$item->image) }}')">
    @endif
</div>

        {{-- RIGHT: DETAILS + BOOKING --}}
        <div class="space-y-6">

            {{-- Title & Category --}}
            <div>
                <h1 class="text-3xl font-semibold text-gray-900">
                    {{ $item->title }}
                </h1>
                <p class="text-gray-500 mt-1">
                    {{ optional($item->category)->name }}
                </p>
            </div>

            {{-- Owner + Rating --}}
            @if($item->user)
                @php
                    $owner = $item->user;
                    $rating = number_format($owner->averageRating() ?? 0, 1);
                @endphp

                <div class="text-sm text-gray-600">
                    Owned by
                    <a href="{{ route('owners.show', $item->user->id) }}"
                       class="text-blue-600 hover:underline font-medium">
                        {{ $item->user->name }}
                    </a>

                    <a href="#reviews"
                       class="flex items-center gap-2 mt-2 text-sm text-yellow-500 hover:underline">
                        <span>⭐</span>
                        <span>{{ $rating }}</span>
                        <span class="text-gray-500">
                            ({{ $owner->reviewsReceived->count() }} reviews)
                        </span>
                    </a>
                </div>
            @endif

            {{-- Price --}}
            <div class="flex items-center gap-2">
                <span class="text-2xl font-semibold text-green-600">
                    ₦{{ number_format($item->price_per_day) }}
                </span>
                <span class="text-gray-500">
                    / {{ $item->unit_label }}
                </span>
            </div>

            {{-- Location --}}
            <p class="text-gray-600">
                📍 {{ $item->location }}
            </p>

            {{-- Description --}}
            <div class="pt-4 border-t">
                <h3 class="font-semibold text-gray-900 mb-2">
                    Item description
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ $item->description }}
                </p>
            </div>

            {{-- Availability --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                <p class="text-sm text-gray-600">
                    Available: <strong>{{ $item->quantity_units }}</strong> {{ $item->unit_label }}(s)
                </p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- BOOKING --}}
            @auth
                @if(auth()->id() !== $item->user_id)

                    <form method="POST"
                          action="{{ route('rentals.summary', $item) }}"
                          class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                        @csrf

                       {{-- Dates - Single Range Picker --}}
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700">
        Select Rental Dates
    </label>
    
    <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
            📅
        </span>
        
        <input
            type="text"
            id="date-range-picker"
            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl cursor-pointer"
            placeholder="Click to select start and end dates..."
            readonly
        >
    </div>
    
    <p class="text-xs text-gray-500">
        Click to open calendar and select your rental period
    </p>
    
    <!-- Hidden inputs for form submission -->
    <input type="hidden" name="start_date" id="start-date-hidden">
    <input type="hidden" name="end_date" id="end-date-hidden">
</div>

                        {{-- Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Quantity ({{ $item->unit_label ?? 'units' }})
                            </label>

                            <input type="number"
                                name="quantity_units"
                                min="1"
                                max="{{ $item->quantity_units }}"
                                required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter quantity">

                            <p class="text-xs text-gray-500 mt-1">
                                Available: {{ $item->quantity_units }} {{ $item->unit_label ?? 'units' }}
                            </p>

                            <div id="stock-warning" class="mt-2 text-sm"></div>
                        </div>

                        {{-- Live Price --}}
                        <div id="live-price-box"
                             class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800 hidden">
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 text-white py-3 rounded-full font-medium hover:bg-blue-700 transition">
                            Request booking
                        </button>

						<button type="submit"
        formaction="{{ route('cart.add', $item) }}"
        class="w-full bg-pink-600 text-white py-3 rounded-full
               font-medium hover:bg-pink-700 transition mt-2">
    Add to Cart
</button>

                        <p class="text-xs text-gray-500 text-center">
                            You won’t be charged until the owner approves.
                        </p>

                    </form>

                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
                        You are the owner of this item.
                    </div>
                @endif
            @else
                <div class="bg-white border border-gray-200 rounded-xl p-6 text-center">
                    <p class="text-gray-600 mb-4">
                        Please log in to request this item.
                    </p>
                    <a href="{{ route('login') }}"
                       class="inline-block bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition">
                        Log in
                    </a>
                </div>
            @endauth

        </div>
    </div>

    {{-- ================= REVIEWS SECTION ================= --}}
    @if($item->user)
        <hr id="reviews" class="my-16">

        <div class="max-w-4xl mx-auto">
		@if($canReview)
    <a href="{{ route('reviews.create', $item->id) }}"
       class="inline-block mb-6 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">
        Write a Review
    </a>
@endif
			{{-- ⭐ Rating Summary --}}
@if($totalReviews > 0)

<div class="mb-10">

    <div class="flex items-center gap-3 mb-4">
        <span class="text-3xl font-bold text-gray-900">
            {{ number_format($averageRating, 1) }}

        </span>

        <div class="flex items-center gap-1">
    @for($i = 1; $i <= 5; $i++)
        <svg class="w-5 h-5 {{ $i <= floor($averageRating) ? 'text-yellow-500' : 'text-gray-300' }}"
             fill="currentColor"
             viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.955
                     4.162.012c.969.003 1.371 1.24.588 1.81l-3.37
                     2.448 1.272 3.99c.285.893-.755 1.63-1.54
                     1.087L10 13.347l-3.349 2.882c-.785.543-1.825-.194-1.54-1.087
                     l1.272-3.99-3.37-2.448c-.783-.57-.38-1.807.588-1.81
                     l4.162-.012 1.286-3.955z"/>
        </svg>
    @endfor
</div>


        <span class="text-gray-500 text-sm">
            {{ $totalReviews }} reviews
        </span>
    </div>

    {{-- Breakdown Bars --}}
    @foreach($ratingBreakdown as $star => $count)
        @php
            $percentage = $totalReviews > 0
                ? ($count / $totalReviews) * 100
                : 0;
        @endphp

        <div class="flex items-center gap-3 mb-2">
            <span class="w-8 text-sm text-gray-600">
                {{ $star }} ★
            </span>

            <div class="flex-1 bg-gray-200 h-2 rounded-full overflow-hidden">
                <div class="bg-yellow-500 h-2"
                     style="width: {{ $percentage }}%">
                </div>
            </div>

            <span class="w-6 text-sm text-gray-600 text-right">
                {{ $count }}
            </span>
        </div>
    @endforeach

</div>

@endif

            <h3 class="text-2xl font-semibold mb-6">Reviews</h3>

           @forelse($item->user->reviewsReceived as $review)
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4 shadow-sm">

        <div class="flex justify-between items-start">

            <div>
                <div class="flex items-center gap-2">
                    <strong class="text-gray-900">
                        {{ $review->reviewer->name }}
                    </strong>

                    {{-- VERIFIED BADGE --}}
                    @if($review->rental && $review->rental->status === 'completed')
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                            ✔ Verified Renter
                        </span>
                    @endif
                </div>

                <div class="text-sm text-gray-400 mt-1">
                    {{ $review->created_at->format('F d, Y') }}
                </div>
            </div>

            <span class="text-yellow-500 font-medium">
                ⭐ {{ $review->rating }}
            </span>

        </div>

        <p class="text-gray-600 mt-4">
            {{ $review->comment }}
        </p>

    </div>
	@empty

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-gray-500">
                    No reviews yet.
                </div>
            @endforelse
        </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    
    // Load blocked dates from server
    let fullyBookedDates = [];
    try {
        const blockedResponse = await fetch("{{ route('rentals.blockedDates', $item) }}");
        fullyBookedDates = await blockedResponse.json();
    } catch (error) {
        console.error('Error loading blocked dates:', error);
    }

    // Initialize Flatpickr with range mode
    const dateRangePicker = flatpickr("#date-range-picker", {
        mode: "range",
        minDate: "today",
        disable: fullyBookedDates,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        allowInput: false,
        clickOpens: true,
        showMonths: 2,
        
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                const startDate = selectedDates[0];
                const endDate = selectedDates[1];
                
                // Format dates as YYYY-MM-DD
                const formatDate = (date) => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };
                
                // 🔥 CRITICAL: Fill hidden inputs
                const startInput = document.getElementById('start-date-hidden');
                const endInput = document.getElementById('end-date-hidden');
                
                if (startInput && endInput) {
                    startInput.value = formatDate(startDate);
                    endInput.value = formatDate(endDate);
                    console.log('Start date set:', startInput.value);
                    console.log('End date set:', endInput.value);
                } else {
                    console.error('Hidden inputs not found!');
                }
                
                // Calculate days and price
                const oneDay = 24 * 60 * 60 * 1000;
                const days = Math.round(Math.abs((endDate - startDate) / oneDay)) + 1;
                
                calculatePrice(days);
                checkAvailability(formatDate(startDate), formatDate(endDate));
            }
        }
    });

    // Quantity input
    const qtyInput = document.querySelector('input[name="quantity_units"]');
    const warningBox = document.getElementById('stock-warning');
    const priceBox = document.getElementById('live-price-box');
    const pricePerDay = {{ $item->price_per_day }};

    function calculatePrice(days) {
        if (!days || !qtyInput.value) {
            priceBox.classList.add('hidden');
            return;
        }

        const quantity = parseInt(qtyInput.value) || 1;
        const total = days * pricePerDay * quantity;

        priceBox.innerHTML = `
            <div class="font-medium">
                ${days} day(s) × ₦${pricePerDay.toLocaleString()} × ${quantity} {{ $item->unit_label }}
            </div>
            <div class="text-lg font-bold mt-1">
                Total: ₦${total.toLocaleString()}
            </div>
        `;

        priceBox.classList.remove('hidden');
    }

    async function checkAvailability(startDate, endDate) {
        try {
            const response = await fetch("{{ route('rentals.checkAvailability', $item) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    start_date: startDate,
                    end_date: endDate
                })
            });

            const data = await response.json();

            if (data.available <= 0) {
                warningBox.innerHTML = `<span class="text-red-600 font-medium">Fully booked for selected dates</span>`;
                qtyInput.max = 0;
            } else {
                warningBox.innerHTML = `<span class="text-green-600">Available: ${data.available} {{ $item->unit_label }}</span>`;
                qtyInput.max = Math.min(data.available, {{ $item->quantity_units }});
            }
        } catch (error) {
            console.error('Availability check failed:', error);
        }
    }

    qtyInput.addEventListener('input', function() {
        const startInput = document.getElementById('start-date-hidden');
        const endInput = document.getElementById('end-date-hidden');
        
        if (startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            const oneDay = 24 * 60 * 60 * 1000;
            const days = Math.round(Math.abs((end - start) / oneDay)) + 1;
            calculatePrice(days);
        }
    });

});
</script>

<script>
function changeImage(imageUrl){
    document.getElementById("main_image").src = imageUrl;
}
</script>
@endsection


