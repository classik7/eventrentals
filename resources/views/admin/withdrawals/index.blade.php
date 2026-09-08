@extends('layouts.app')

@section('title', 'Admin - Withdrawals')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto py-10 px-4">

<h2 class="text-3xl font-bold mb-8">Withdrawal Requests 💳</h2>

{{-- 🔥 GLOBAL FRAUD ALERT (MERGED CLEANLY) --}}
@if($fraudStats['high_risk'] > 0)
    <div class="bg-red-600 text-white p-4 rounded mb-4 animate-pulse flex justify-between items-center">
        🚨 {{ $fraudStats['high_risk'] }} HIGH-RISK withdrawals detected!

        <button onclick="playAlert()" class="bg-white text-red-600 px-3 py-1 rounded text-sm">
            🔊 Alert
        </button>
    </div>
@endif

{{-- 🔥 FRAUD DASHBOARD --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    <div class="bg-red-100 p-4 rounded-xl shadow">
        <p class="text-sm text-gray-600">🚨 Flagged Withdrawals</p>
        <h3 class="text-xl font-bold">{{ $fraudStats['total_flagged'] }}</h3>
    </div>

    <div class="bg-orange-100 p-4 rounded-xl shadow">
        <p class="text-sm text-gray-600">🔥 High Risk</p>
        <h3 class="text-xl font-bold">{{ $fraudStats['high_risk'] }}</h3>
    </div>

    <div class="bg-yellow-100 p-4 rounded-xl shadow">
        <p class="text-sm text-gray-600">💰 Flagged Amount</p>
        <h3 class="text-xl font-bold">₦{{ number_format($fraudStats['total_amount'], 2) }}</h3>
    </div>

</div>

{{-- 🔥 FILTER SECTION --}}
<form method="GET" class="mb-6 flex flex-wrap gap-3 items-center">

    <select name="status" class="border px-3 py-2 rounded">
        <option value="">All Status</option>
        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="processing">Processing</option>
		<option value="paid">Paid</option>
        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
    </select>

    <select name="admin_id" class="border px-3 py-2 rounded">
        <option value="">All Admins</option>
        @foreach($admins as $admin)
            <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                👤 {{ $admin->name }}
            </option>
        @endforeach
    </select>

    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border px-3 py-2 rounded">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border px-3 py-2 rounded">

    <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="Min ₦" class="border px-3 py-2 rounded w-32">
    <input type="number" name="max_amount" value="{{ request('max_amount') }}" placeholder="Max ₦" class="border px-3 py-2 rounded w-32">

    <select name="risk" class="border px-3 py-2 rounded">
        <option value="">All</option>
        <option value="high" {{ request('risk') == 'high' ? 'selected' : '' }}>
            High Risk 🚨
        </option>
    </select>

    <button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>

    <a href="{{ route('admin.withdrawals.index') }}" class="bg-gray-300 px-4 py-2 rounded">
        Reset
    </a>

</form>

{{-- 🔥 EXPORT --}}
<a href="{{ route('admin.withdrawals.export') }}"
   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded mb-4 inline-block">
   ⬇ Export CSV
</a>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <div class="bg-white p-4 rounded-xl shadow">
        <h3 class="font-bold mb-3">📊 Withdrawal Trend</h3>
        <canvas id="withdrawalChart"></canvas>
    </div>

    <div class="bg-white p-4 rounded-xl shadow">
        <h3 class="font-bold mb-3">🚨 Fraud Trend</h3>
        <canvas id="fraudChart"></canvas>
    </div>

</div>
<div class="bg-white rounded-2xl shadow overflow-hidden">

    @if($withdrawals->count())

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">

            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="py-4 px-6">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th class="text-left py-4 px-6">Owner</th>
                    <th class="text-left py-4 px-6">Account</th>
                    <th class="text-left py-4 px-6">Amount</th>
                    <th class="text-left py-4 px-6">Bank Details</th>
                    <th class="text-left py-4 px-6">Date</th>
                    <th class="text-left py-4 px-6">Status</th>
                    <th class="text-left py-4 px-6">Approved By</th>
                    <th class="text-left py-4 px-6">Action</th>
                </tr>
            </thead>

            <tbody>

                @foreach($withdrawals as $withdrawal)

                <tr class="border-b transition 
                    @if($withdrawal->is_flagged) bg-red-50 hover:bg-red-100 
                    @else hover:bg-gray-50 
                    @endif">

                    {{-- CHECKBOX --}}
                    <td class="py-4 px-6">
                        <input type="checkbox" form="bulkForm" name="selected[]" value="{{ $withdrawal->id }}">
                    </td>

                    {{-- OWNER --}}
                    <td class="py-4 px-6 font-medium">
                        {{ $withdrawal->owner->name ?? 'N/A' }}
                        <div class="text-xs text-gray-500">
                            {{ $withdrawal->owner->email ?? '' }}
                        </div>
                    </td>

                    {{-- ACCOUNT STATUS --}}
                    <td class="py-4 px-6 text-xs">
                        @if(optional($withdrawal->user)->account_status === 'blocked')
                            <span class="text-red-600 font-semibold">🚫 Blocked</span>
                        @elseif(optional($withdrawal->user)->account_status === 'restricted')
                            <span class="text-orange-500 font-semibold">⛔ Restricted</span>
                        @elseif(optional($withdrawal->user)->account_status === 'warning')
                            <span class="text-yellow-500 font-semibold">⚠️ Warning</span>
                        @else
                            <span class="text-green-600 font-semibold">✅ Active</span>
                        @endif
                    </td>

                    {{-- AMOUNT --}}
                    <td class="py-4 px-6 font-semibold">
                        ₦{{ number_format($withdrawal->amount, 2) }}
                    </td>

                    {{-- BANK DETAILS --}}
                    <td class="py-4 px-6">
                        @php
                            $banks = app(\App\Services\PaystackService::class)->getBanks();
                            $bankName = collect($banks)->firstWhere('code', $withdrawal->bank_code)['name'] ?? null;
                        @endphp

                        @if($withdrawal->account_name && $withdrawal->account_number)
                            <div>
                                <strong>{{ $withdrawal->account_name }}</strong><br>
                                {{ $withdrawal->account_number }}<br>
                                {{ $bankName ?? 'Unknown Bank' }}
                            </div>
                        @else
                            <span class="text-gray-400">Not Provided</span>
                        @endif
                    </td>

                    {{-- DATE --}}
                    <td class="py-4 px-6">
                        {{ $withdrawal->created_at->format('d M Y') }}
                    </td>

                    {{-- STATUS --}}
                   <td class="py-4 px-6">

    @if($withdrawal->transfer_status === 'otp_pending')
        <span class="px-3 py-1 text-xs rounded-full bg-orange-100 text-orange-600 font-semibold">
            OTP Pending
        </span>

    @elseif($withdrawal->status === 'processing')
        <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-600 font-semibold">
            Processing
        </span>

    @elseif($withdrawal->status === 'paid')
        <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-600 font-semibold">
            Paid
        </span>

    @elseif($withdrawal->status === 'rejected')
        <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-600 font-semibold">
            Rejected
        </span>

    @else
        <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-600 font-semibold">
            Pending
        </span>

    @endif
                        {{-- 🔥 FRAUD BADGE + SCORE --}}
                        @if($withdrawal->is_flagged)
                            <div class="mt-1">
                                <span class="bg-red-600 text-white px-2 py-1 rounded text-xs animate-pulse">
                                    🚨 {{ strtoupper($withdrawal->risk_level) }}
                                </span>

                                @if(!empty($withdrawal->risk_score))
                                    <div class="text-xs text-red-700 font-bold mt-1">
                                        Score: {{ $withdrawal->risk_score }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </td>

                    {{-- APPROVED BY --}}
                    <td class="py-4 px-6">
                        @if($withdrawal->approver)
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-lg">
                                👤 {{ $withdrawal->approver->name }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs italic">Pending</span>
                        @endif
                    </td>

                    {{-- ACTION --}}
                    <td class="py-4 px-6">
                        @if($withdrawal->status == 'pending')
                            <div class="flex gap-2">

                                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}">
                                    @csrf
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal->id) }}">
                                    @csrf
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                                        Reject
                                    </button>
                                </form>

                            </div>
                        @else
                            <span class="text-gray-400 text-xs">Completed</span>
                        @endif
                    </td>

                </tr>

                @endforeach
            </tbody>

        </table>
    </div>

    {{-- BULK ACTION --}}
    <form method="POST" action="{{ route('admin.withdrawals.bulk') }}" id="bulkForm">
        @csrf

        <div class="p-4 flex gap-3">
            <button name="action" value="approve"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Bulk Approve
            </button>

            <button name="action" value="reject"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                Bulk Reject
            </button>
        </div>
    </form>

    @else
        <div class="p-8 text-center text-gray-500">
            No withdrawal requests yet.
        </div>
    @endif

</div>

</div>

{{-- 🔥 SELECT ALL SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="selected[]"]').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
});
</script>

{{-- 🔥 ALERT SOUND --}}
<script>
function playAlert() {
    const audio = new Audio('https://www.soundjay.com/buttons/sounds/beep-01a.mp3');
    audio.play();
}
</script>

<script>
// 📊 Withdrawal Chart
const withdrawalData = {
    labels: {!! json_encode($trend->pluck('date')) !!},
    datasets: [{
        label: 'Withdrawal ₦',
        data: {!! json_encode($trend->pluck('total')) !!},
        borderWidth: 2,
        tension: 0.4
    }]
};

new Chart(document.getElementById('withdrawalChart'), {
    type: 'line',
    data: withdrawalData
});

// 🚨 Fraud Chart
const fraudData = {
    labels: {!! json_encode($fraudTrend->pluck('date')) !!},
    datasets: [{
        label: 'Fraud Cases',
        data: {!! json_encode($fraudTrend->pluck('total')) !!},
        borderWidth: 2,
        tension: 0.4
    }]
};

new Chart(document.getElementById('fraudChart'), {
    type: 'bar',
    data: fraudData
});
</script>

@endsection