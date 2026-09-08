@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-10">

    <h2 class="text-2xl font-semibold mb-8">My Wishlist ❤️</h2>

    <div id="wishlist-container"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-all duration-500">

        @forelse($items as $item)

            <div class="wishlist-card relative bg-white rounded-2xl border border-gray-200
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition duration-300 overflow-hidden opacity-0 translate-y-6">

                {{-- Remove Button --}}
                <button class="remove-wishlist-btn absolute top-3 right-3 text-red-500 text-xl
                               hover:scale-110 transition"
                        data-item="{{ $item->id }}">
                    ❤️
                </button>

                <a href="{{ route('items.show', $item) }}">
                    <img
                        src="{{ $item->image
                            ? asset('storage/'.$item->image)
                            : 'https://via.placeholder.com/600x400?text=Event+Rental' }}"
                        class="w-full h-48 object-cover"
                        alt="{{ $item->title }}">
                </a>

                <div class="p-4 space-y-2">

                    <h3 class="font-semibold text-gray-900 truncate">
                        {{ $item->title }}
                    </h3>

                    <p class="text-green-600 font-semibold">
                        ₦{{ number_format($item->price_per_day) }}
                        <span class="text-sm text-gray-500 font-normal">
                            / {{ $item->unit_label }}
                        </span>
                    </p>

                </div>

            </div>

        @empty

            <div class="col-span-full text-center py-20">
                <div class="text-6xl mb-4 animate-bounce">💔</div>
                <h2 class="text-xl font-semibold text-gray-700">
                    Your wishlist is empty
                </h2>
                <p class="text-gray-500 mt-2">
                    Start saving items you love ❤️
                </p>
            </div>

        @endforelse

    </div>

</div>

@endsection


{{-- ===== Styles ===== --}}
@push('styles')
<style>
.wishlist-card {
    transition: all 0.4s ease;
}
</style>
@endpush


{{-- ===== Scripts ===== --}}
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    // 🔥 Smooth stagger fade in
    const cards = document.querySelectorAll('.wishlist-card');

    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.remove('opacity-0', 'translate-y-6');
            card.classList.add('opacity-100', 'translate-y-0');
        }, index * 100);
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.remove-wishlist-btn').forEach(button => {

        button.addEventListener('click', function (e) {

            e.preventDefault();
            e.stopPropagation();

            const itemId = this.dataset.item;
            const card = this.closest('.wishlist-card');

            fetch("{{ url('/wishlist') }}/" + itemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {

                if (data.status === 'removed') {

                    // Smooth fade out
                    card.classList.add('opacity-0', 'scale-95');

                    setTimeout(() => {
                        card.remove();

                        // If no cards left
                        if (document.querySelectorAll('.wishlist-card').length === 0) {

                            document.getElementById('wishlist-container').innerHTML = `
                                <div class="col-span-full text-center py-20">
                                    <div class="text-6xl mb-4 animate-bounce">💔</div>
                                    <h2 class="text-xl font-semibold text-gray-700">
                                        Your wishlist is empty
                                    </h2>
                                    <p class="text-gray-500 mt-2">
                                        Start saving items you love ❤️
                                    </p>
                                </div>
                            `;
                        }

                    }, 300);
                }

            });

        });

    });

});
</script>

@endpush
