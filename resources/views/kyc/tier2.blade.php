@extends('layouts.app')

@section('title', 'Tier 2 Verification')

@section('content')

<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-2xl shadow">

    <h2 class="text-xl font-bold mb-4 text-green-600">
        🟢 Tier 2 Verification
    </h2>

    <p class="text-sm text-gray-600 mb-6">
        Upgrade your account to receive payments faster and increase customer trust.
    </p>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST" action="{{ route('kyc.tier2.submit') }}" enctype="multipart/form-data">
        @csrf

        {{-- ID DOCUMENT --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
                Government ID (NIN Slip, Passport, Driver License)
            </label>
            <input type="file" name="id_document"
                   class="w-full border p-2 rounded">

            @error('id_document')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- SELFIE --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
                Upload Selfie
            </label>
            <input type="file" name="selfie"
                   class="w-full border p-2 rounded">

            @error('selfie')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">
            Submit for Verification
        </button>

    </form>

</div>

@endsection