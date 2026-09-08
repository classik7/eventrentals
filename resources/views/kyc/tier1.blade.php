@extends('layouts.app')

@section('title', 'Tier 1 Verification')

@section('content')

<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-2xl shadow">

    <h2 class="text-xl font-bold mb-4 text-yellow-600">
        🟡 Tier 1 — Basic Verification
    </h2>

    <p class="text-sm text-gray-600 mb-6">
        Complete your basic verification to start receiving payments after job completion.
    </p>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST" action="{{ route('kyc.tier1.submit') }}">
        @csrf

        {{-- FULL NAME --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Full Name</label>
            <input type="text"
                   value="{{ auth()->user()->name }}"
                   disabled
                   class="w-full border p-2 rounded bg-gray-100">
        </div>

        {{-- NIN --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">NIN</label>
            <input type="text" name="nin"
                   class="w-full border p-2 rounded"
                   placeholder="Enter your NIN">

            @error('nin')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- DOB --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Date of Birth</label>
            <input type="date" name="dob"
                   class="w-full border p-2 rounded">

            @error('dob')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded">
            Submit Verification
        </button>

    </form>

</div>

@endsection