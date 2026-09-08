@extends('layouts.app')

@section('title', 'Tier 3 Verification')

@section('content')

<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-2xl shadow">

    <h2 class="text-xl font-bold mb-4 text-blue-600">
        🔵 Tier 3 — Premium Verification
    </h2>

    <p class="text-sm text-gray-600 mb-6">
        Unlock premium features, faster payments, and the blue verified badge.
    </p>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST" action="{{ route('kyc.tier3.submit') }}" enctype="multipart/form-data">
        @csrf

        {{-- BUSINESS NAME --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Business Name</label>
            <input type="text" name="business_name"
                   class="w-full border p-2 rounded"
                   placeholder="Enter your business name">

            @error('business_name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- CAC NUMBER --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">CAC Number</label>
            <input type="text" name="cac_number"
                   class="w-full border p-2 rounded"
                   placeholder="Enter CAC registration number">

            @error('cac_number')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- BUSINESS ADDRESS --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Business Address</label>
            <textarea name="business_address"
                      class="w-full border p-2 rounded"
                      placeholder="Enter full business address"></textarea>

            @error('business_address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- PROOF OF ADDRESS --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
                Proof of Address (Utility Bill)
            </label>
            <input type="file" name="proof_of_address"
                   class="w-full border p-2 rounded">

            @error('proof_of_address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">
            Submit for Premium Verification
        </button>

    </form>

</div>

@endsection