@if($cartItems->count())
    @foreach($cartItems as $cart)
        <div class="mb-3 border-b pb-2">
            <p class="font-medium">{{ $cart->item->title }}</p>
            <p class="text-sm text-gray-500">
                ₦{{ number_format($cart->item->price_per_day) }}
            </p>
        </div>
    @endforeach

    <a href="{{ route('cart.index') }}"
       class="block w-full text-center bg-gray-200 py-2 rounded-lg mt-3">
        View Full Cart
    </a>
@else
    <p class="text-gray-500">Your cart is empty.</p>
@endif