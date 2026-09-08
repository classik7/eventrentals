@extends('layouts.app')

@section('title', 'My Wallet')

@section('content')

<div class="max-w-5xl mx-auto py-10 px-4">

<h2 class="text-3xl font-semibold mb-8">Wallet</h2>

{{-- BALANCE CARDS --}}
<div class="grid md:grid-cols-3 gap-6 mb-10">

    <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <p class="text-gray-400 text-sm mb-1">Available</p>
        <h2 class="text-2xl font-semibold text-black">
            ₦{{ number_format($user->wallet_available, 2) }}
        </h2>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <p class="text-gray-400 text-sm mb-1">Pending</p>
        <h2 class="text-2xl font-semibold text-yellow-500">
            ₦{{ number_format($user->wallet_pending, 2) }}
        </h2>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <p class="text-gray-400 text-sm mb-1">Total</p>
        <h2 class="text-2xl font-semibold text-blue-600">
            ₦{{ number_format($user->wallet_available + $user->wallet_pending, 2) }}
        </h2>
    </div>

</div>

{{-- 📊 CHART --}}
<div class="bg-white rounded-2xl shadow-sm border p-6 mb-8">
    <h3 class="text-lg font-semibold mb-4">Transaction Overview</h3>
    <canvas id="walletChart" height="100"></canvas>
</div>

{{-- HEADER + EXPORT --}}
<div class="flex justify-between items-center mb-4">

    <h3 class="text-lg font-semibold">Transactions</h3>

    <a href="{{ route('owner.wallet.export', request()->query()) }}"
       class="bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">
        ⬇ Export CSV
    </a>
	
	<a href="{{ route('owner.wallet.pdf') }}"
   class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">
    📄 Export PDF
</a>

@if($fraudStats['high_risk'] > 0)
    <div class="bg-red-600 text-white p-4 rounded mb-4">
        🚨 ALERT: {{ $fraudStats['high_risk'] }} high-risk withdrawals detected!
    </div>
@endif
</div>

{{-- TRANSACTION LIST --}}
<div class="bg-white rounded-2xl shadow-sm border overflow-hidden">

    @if($transactions->count())

        <div class="divide-y">

            @foreach($transactions as $tx)
            <div class="flex items-center justify-between p-5 hover:bg-gray-50 transition">

                {{-- LEFT --}}
                <div>
                    <p class="font-medium text-gray-800">
                        {{ $tx->description }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $tx->created_at->format('d M Y, h:i A') }}
                    </p>
                </div>

                {{-- RIGHT --}}
                <div class="text-right">

                    @if($tx->type === 'credit')
                        <p class="text-green-600 font-semibold">
                            +₦{{ number_format($tx->amount, 2) }}
                        </p>
                    @else
                        <p class="text-red-500 font-semibold">
                            -₦{{ number_format($tx->amount, 2) }}
                        </p>
                    @endif

                </div>

            </div>
            @endforeach

        </div>

        <div class="p-4">
            {{ $transactions->links() }}
        </div>

    @else
        <div class="p-6 text-gray-400 text-center">
            No transactions yet.
        </div>
    @endif

</div>

</div>

{{-- 📊 CHART SCRIPT --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const chartEl = document.getElementById('walletChart');

if (chartEl) {
    const ctx = chartEl.getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Credit', 'Debit'],
            datasets: [{
                label: 'Amount (₦)',
                data: [{{ $credits ?? 0 }}, {{ $debits ?? 0 }}],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>

@endsection
