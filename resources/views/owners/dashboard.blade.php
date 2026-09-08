@extends('layouts.app')

@section('title', 'Owner Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">

    <h2 class="text-3xl font-bold mb-10">Owner Dashboard 📊</h2>

    {{-- SUCCESS / ERROR MESSAGES --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ===================== --}}
    {{-- TOP STAT CARDS --}}
    {{-- ===================== --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <p class="text-gray-500 text-sm">Total Bookings</p>
            <h3 class="text-3xl font-bold mt-2">{{ $totalBookings }}</h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <p class="text-gray-500 text-sm">Pending Rentals</p>
            <h3 class="text-3xl font-bold mt-2 text-yellow-500">
                {{ $pendingRentals }}
            </h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <p class="text-gray-500 text-sm">Completed Rentals</p>
            <h3 class="text-3xl font-bold mt-2 text-green-500">
                {{ $completedRentals }}
            </h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
            <p class="text-gray-500 text-sm">Total Earnings</p>
            <h3 class="text-3xl font-bold mt-2 text-blue-600">
                ₦{{ number_format($totalEarnings) }}
            </h3>
        </div>

    </div>


{{-- ===================== --}}
{{-- BALANCE + MONTH --}}
{{-- ===================== --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    {{-- Available Balance Card --}}
    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-2xl shadow-lg relative overflow-hidden">

       @php
    $balance = auth()->user()->role === 'admin'
        ? auth()->user()->admin_wallet
        : auth()->user()->wallet_available;
@endphp

        <p class="text-gray-600 text-sm">Available Balance</p>

        <h3 class="text-3xl font-bold text-green-700 mt-2">
           ₦{{ number_format($balance, 2) }}
        </h3>

        {{-- Withdraw Form --}}
        <form method="POST"
              action="{{ route('owner.withdraw.store') }}"
              class="mt-6 space-y-3">
            @csrf

            {{-- Amount --}}
            <div class="relative">
                <span class="absolute left-3 top-3 text-green-600 font-bold">₦</span>
                <input type="number"
                       step="0.01"
                       min="1"
                       max="{{ $balance }}"
                       name="amount"
                       required
                       class="pl-8 border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none"
                       placeholder="Enter amount"
                       value="{{ old('amount') }}">
            </div>

            {{-- Bank Selection --}}
            <select name="bank_code"
                    class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none"
                    required>
                <option value="">Select Bank</option>

                @foreach($banks as $bank)
                    <option value="{{ $bank['code'] }}"
                        {{ old('bank_code') == $bank['code'] ? 'selected' : '' }}>
                        {{ $bank['name'] }}
                    </option>
                @endforeach
            </select>

            {{-- Account Number --}}
            <input type="text"
                   name="account_number"
                   placeholder="Account Number"
                   required
                   class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none"
                   value="{{ old('account_number') }}">

            {{-- Account Name --}}
            <input type="text"
                   name="account_name"
                   placeholder="Account Name"
                   required
                   class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-green-400 outline-none"
                   value="{{ old('account_name') }}">

            {{-- Info --}}
            <p class="text-xs text-gray-500">
                Maximum withdrawable: ₦{{ number_format($balance, 2) }}
            </p>

            {{-- Warning --}}
            @if($balance <= 0)
                <p class="text-sm text-red-500">
                    No funds available for withdrawal.
                </p>
            @endif

            {{-- Button --}}
            <button type="submit"
    class="w-full py-2 rounded-lg font-semibold transition
    {{ $balance <= 0
        ? 'bg-gray-400 cursor-not-allowed'
        : 'bg-green-600 hover:bg-green-700 text-white' }}"
    {{ $balance <= 0 ? 'disabled' : '' }}>
    
    Request Withdrawal
</button>

        </form>

    </div>

    {{-- Pending Escrow --}}
    <div class="bg-yellow-50 p-6 rounded-2xl shadow">
        <p class="text-gray-600 text-sm">Pending Escrow</p>

        <h3 class="text-2xl font-bold text-yellow-600 mt-2">
            ₦{{ number_format(auth()->user()->wallet_pending ?? 0, 2) }}
        </h3>

        <p class="text-xs text-gray-500 mt-2">
            Funds will be available after escrow period.
        </p>
    </div>

    {{-- This Month Earnings --}}
    <div class="bg-purple-50 p-6 rounded-2xl shadow">
        <p class="text-gray-600 text-sm">This Month Earnings</p>

        <h3 class="text-2xl font-bold text-purple-600 mt-2">
            ₦{{ number_format($thisMonthEarnings ?? 0, 2) }}
        </h3>
    </div>

</div>

    {{-- RECENT RENTALS --}}
    <div class="bg-white p-6 rounded-2xl shadow mb-10">
        <h3 class="text-xl font-semibold mb-4">Recent Rentals</h3>

        @forelse($recentRentals as $rental)
            <div class="border-b py-3 flex justify-between items-center">
                <span class="font-medium">
                    {{ $rental->item->title ?? 'Item' }}
                </span>

                <span class="text-gray-500">
                    ₦{{ number_format($rental->total_price) }}
                </span>
            </div>
        @empty
            <p class="text-gray-500">No rentals yet.</p>
        @endforelse
    </div>

    {{-- WITHDRAWAL HISTORY --}}
<div class="bg-white rounded-xl p-6 mt-6 shadow">
    <h3 class="font-semibold text-lg mb-4">Withdrawal History</h3>

    @forelse($withdrawals as $withdrawal)

        <div class="flex justify-between items-center border-b py-3">
            <div>
                <p class="font-medium">
                    ₦{{ number_format($withdrawal->amount, 2) }}
                </p>
                <p class="text-sm text-gray-500">
                    {{ $withdrawal->created_at->format('d M Y') }}
                </p>
            </div>

            <span class="px-3 py-1 rounded-full text-sm
                @if($withdrawal->status == 'approved') bg-green-100 text-green-700
                @elseif($withdrawal->status == 'processing') bg-yellow-100 text-yellow-700
                @else bg-gray-100 text-gray-600
                @endif">
                {{ ucfirst($withdrawal->status) }}
            </span>
        </div>

    @empty
        <p class="text-gray-500">No withdrawals yet.</p>
    @endforelse
</div>
@endsection