@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12">

    <h2 class="text-3xl font-semibold text-gray-900 mb-10">
        🏡 My Rentals
    </h2>

    @forelse($rentals as $rental)

        @php
            $now = \Carbon\Carbon::now();
            $endDate = \Carbon\Carbon::parse($rental->end_date);
            $totalDays = \Carbon\Carbon::parse($rental->start_date)->diffInDays($endDate) + 1;
            $daysPassed = \Carbon\Carbon::parse($rental->start_date)->diffInDays($now);
            $progress = $totalDays > 0 ? min(100, ($daysPassed / $totalDays) * 100) : 0;

            $releaseDate = optional($rental->payment)->escrow_release_date;

            $payment = $rental->payment;

            $canDispute = $payment
                && $payment->status === 'success'
                && !$payment->escrow_released
                && in_array($rental->status, ['accepted','pending','completed']);
        @endphp

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8 hover:shadow-md transition duration-300">

            <div class="grid grid-cols-1 md:grid-cols-4">

                {{-- IMAGE --}}
                <div class="md:col-span-1">
                    <img
                        src="{{ $rental->item->image
                            ? asset('storage/'.$rental->item->image)
                            : 'https://via.placeholder.com/400x300?text=Rental' }}"
                        class="w-full h-56 md:h-full object-cover">
                </div>

                {{-- DETAILS --}}
                <div class="md:col-span-3 p-6 flex flex-col justify-between">

                    <div>

                        <div class="flex justify-between items-start">

                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ $rental->item->title }}
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    📅 {{ $rental->start_date }} → {{ $rental->end_date }}
                                </p>

                                <div class="flex items-center gap-3 mt-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-semibold">
                                        {{ strtoupper(substr($rental->owner->name,0,1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ $rental->owner->name }}
                                        </p>
                                        <p class="text-xs text-yellow-500">
                                            ⭐ {{ number_format($rental->owner->average_rating ?? 4.8, 1) }} rating
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- STATUS --}}
                            <span class="px-4 py-1 text-xs font-medium rounded-full
                                @if($rental->status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($rental->status === 'approved') bg-blue-100 text-blue-700
                                @elseif($rental->status === 'rejected') bg-red-100 text-red-700
                                @elseif($rental->status === 'accepted') bg-indigo-100 text-indigo-700
                                @elseif($rental->status === 'completed') bg-green-100 text-green-700
                                @endif">
                                {{ ucfirst($rental->status) }}
                            </span>

                        </div>

                        @if($rental->status === 'accepted')
                        <div class="mt-5">
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full"
                                     style="width: {{ $progress }}%">
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Rental progress {{ round($progress) }}%
                            </p>
                        </div>
                        @endif

                    </div>

                    {{-- ACTIONS --}}
                    <div class="mt-6">

                        @if($releaseDate && $rental->status === 'completed')
                            <div class="mb-4 text-sm text-gray-600">
                                💰 Escrow releases in:
                                <span class="font-semibold text-green-600"
                                      data-countdown="{{ $releaseDate }}">
                                </span>
                            </div>
                        @endif

                        <button onclick="toggleDetails({{ $rental->id }})"
                                class="text-sm text-gray-500 hover:underline mb-4">
                            View details
                        </button>

                        <div id="details-{{ $rental->id }}"
                             class="hidden text-sm text-gray-600 mb-4">
                            <p>Total Paid: ₦{{ number_format($rental->total_price) }}</p>
                            <p>Quantity: {{ $rental->quantity_units }}</p>
                        </div>

                        {{-- DISPUTE SECTION --}}
                        @if($rental->dispute)

                            <div class="px-4 py-2 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-700">
                                Dispute Status:
                                <strong>{{ ucfirst($rental->dispute->status) }}</strong>
                            </div>

                            <a href="{{ route('disputes.show', $rental->dispute->id) }}"
                               class="text-blue-600 text-sm hover:underline mt-2 block">
                                View Dispute Conversation
                            </a>

                        @elseif($canDispute)

                            <details>
                                <summary class="cursor-pointer text-red-600 text-sm font-medium hover:underline">
                                    ⚠️ Report an issue
                                </summary>

                                <form method="POST"
                                      action="{{ route('dispute.store', $rental->id) }}"
                                      enctype="multipart/form-data"
                                      class="mt-4 space-y-3">
                                    @csrf

                                    <textarea name="reason"
                                              required
                                              placeholder="Describe the issue..."
                                              class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm"></textarea>

                                    <input type="file"
                                           name="evidence[]"
                                           multiple
                                           class="w-full text-sm">

                                    <button type="submit"
                                            class="bg-red-600 text-white px-5 py-2 rounded-full text-sm hover:bg-red-700 transition">
                                        Submit Dispute
                                    </button>
                                </form>
                            </details>

                        @endif

                    </div>

                </div>
            </div>

        </div>

    @empty
        <div class="text-center text-gray-500 py-16">
            You have no rentals yet.
        </div>
    @endforelse

</div>

{{-- JAVASCRIPT --}}
<script>
function toggleDetails(id) {
    const el = document.getElementById('details-' + id);
    el.classList.toggle('hidden');
}

// Countdown
document.querySelectorAll('[data-countdown]').forEach(el => {
    const releaseDate = new Date(el.dataset.countdown);

    function update() {
        const now = new Date();
        const diff = releaseDate - now;

        if (diff <= 0) {
            el.innerHTML = "Released";
            return;
        }

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff / (1000 * 60)) % 60);

        el.innerHTML = hours + "h " + minutes + "m";
    }

    update();
    setInterval(update, 60000);
});
</script>

@endsection