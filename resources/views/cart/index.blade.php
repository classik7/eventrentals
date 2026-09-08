@extends('layouts.app')

@section('title','Your Cart')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">

    <h2 class="text-2xl font-semibold mb-6">Your Cart 🛒</h2>

    @php
        $grandTotal = 0;
    @endphp

    @forelse($cartItems as $cart)

        @php
            $start = \Carbon\Carbon::parse($cart->start_date);
            $end   = \Carbon\Carbon::parse($cart->end_date);

            $days = $start->diffInDays($end) + 1;

            $itemTotal = $cart->item->price_per_day
                        * $cart->quantity_units
                        * $days;

            $grandTotal += $itemTotal;
        @endphp

        <div class="bg-white border rounded-xl p-6 mb-4 shadow-sm hover:shadow-md transition">

            <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">

                {{-- Item Info --}}
                <div>
                    <h3 class="font-semibold text-lg">
                        {{ $cart->item->title }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        📅 {{ $start->format('M d, Y') }} → {{ $end->format('M d, Y') }}
                        ({{ $days }} day{{ $days > 1 ? 's' : '' }})
                    </p>

                    <p class="text-sm text-gray-500">
                        ₦{{ number_format($cart->item->price_per_day) }} / day
                    </p>

                    <p class="text-sm text-gray-500">
                        Quantity: {{ $cart->quantity_units}}
                    </p>
                </div>

                {{-- Price + Remove --}}
                <div class="text-right space-y-3">

                    <p class="text-xl font-bold text-green-600">
                        ₦{{ number_format($itemTotal) }}
                    </p>

                    <form method="POST"
                          action="{{ route('cart.remove', $cart) }}"
                          onsubmit="return confirm('Remove this item from cart?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="text-sm text-red-600 hover:underline">
                            🗑 Remove
                        </button>
                    </form>

                </div>

            </div>

        </div>

    @empty

        {{-- Empty Cart UI --}}
        <div class="text-center py-20">

            <div class="text-6xl mb-4">🛒</div>

            <p class="text-gray-500 text-lg mb-6">
                Your cart is empty.
            </p>

            <a href="{{ route('items.index') }}"
               class="bg-blue-600 text-white px-6 py-3 rounded-xl
                      hover:bg-blue-700 transition">
                Browse Rentals
            </a>

        </div>

    @endforelse


    {{-- Checkout Section --}}
@if($cartItems->count())

<div class="bg-gray-50 border rounded-xl p-6 mt-8">

    <div class="space-y-2 text-sm">

    <div class="flex justify-between">
        <span>Subtotal</span>
        <span>₦{{ number_format($grandTotal) }}</span>
    </div>

    <div class="border-t pt-3 flex justify-between text-lg font-semibold">
        <span>Total</span>
        <span>₦{{ number_format($grandTotal) }}</span>
    </div>

</div>

    {{-- 🔴 CLEAR CART --}}
    <form method="POST" action="{{ route('cart.clear') }}"
          onsubmit="return confirm('Are you sure you want to clear your cart?')">
        @csrf
        @method('DELETE')

        <button type="submit"
            class="block w-full text-center bg-red-600 text-white py-3 rounded-xl mb-4 hover:bg-red-700 transition">
            Clear Cart
        </button>
    </form>

    {{-- 🔵 CHECKOUT --}}
    <form method="POST" action="{{ route('cart.checkout') }}">
        @csrf

        <button type="submit"
            class="block w-full text-center bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition">
            Proceed to Checkout
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('items.index') }}"
           class="inline-block px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-100 transition">
            ← Continue Shopping
        </a>
    </div>

</div>

@endif


</div>
@endsection
