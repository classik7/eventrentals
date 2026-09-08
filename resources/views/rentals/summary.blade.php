@extends('layouts.app')

@section('title', 'Booking Summary')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">
        Booking summary
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- ITEM INFO --}}
        <div class="md:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 space-y-4">

            <div class="flex gap-4">
                <img
                    src="{{ $item->image
                        ? asset('storage/'.$item->image)
                        : 'https://via.placeholder.com/300x200?text=Event+Rental' }}"
                    class="w-32 h-24 object-cover rounded-lg"
                >

                <div>
                    <h2 class="font-semibold text-gray-900">
                        {{ $item->title }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ optional($item->category)->name }}
                    </p>
                    <p class="text-sm text-gray-500">
                        📍 {{ $item->location }}
                    </p>
                </div>
            </div>

            <div class="border-t pt-4 space-y-2 text-sm">
                <p>
                    <strong>Start date:</strong> {{ $start_date }}
                </p>
                <p>
                    <strong>End date:</strong> {{ $end_date }}
                </p>
                <p>
                    <strong>Duration:</strong> {{ $days }} day{{ $days > 1 ? 's' : '' }}
                </p>
                <p>
                    <strong>Quantity:</strong> {{ $quantity_units }} {{ $item->unit_label }}
                </p>
            </div>
        </div>

        {{-- PRICE SUMMARY --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">

            <h3 class="font-semibold text-gray-900">
                Price breakdown
            </h3>

            <div class="flex justify-between text-sm">
                <span>
                    ₦{{ number_format($item->price_per_day) }}
                    × {{ $days }} day(s)
                    × {{ $quantity_units }} {{ $item->unit_label }}
                </span>
                <span>₦{{ number_format($totalPrice) }}</span>
            </div>

            <div class="border-t pt-4 flex justify-between font-semibold">
                <span>Total</span>
                <span class="text-green-600">
                    ₦{{ number_format($totalPrice) }}
                </span>
            </div>

            {{-- FINAL SUBMIT --}}
            <form method="POST" action="{{ route('rentals.store', $item) }}">
                @csrf

                <input type="hidden" name="start_date" value="{{ $start_date }}">
                <input type="hidden" name="end_date" value="{{ $end_date }}">
                <input type="hidden" name="quantity_units" value="{{ $quantity_units }}">
                <input type="hidden" name="total_price" value="{{ $totalPrice }}">

                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-full
                           font-medium hover:bg-blue-700 transition mt-4"
                >
                    Confirm Booking
                </button>
            </form>

            <p class="text-xs text-gray-500 text-center">
                You won’t be charged until the owner approves.
            </p>
        </div>

    </div>

</div>
@endsection
