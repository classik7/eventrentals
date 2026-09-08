@extends('layouts.app')

@section('title', 'KYC Verification')

@section('content')

<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-2xl shadow">

    <h2 class="text-2xl font-bold mb-6">KYC Verification 🪪</h2>

    {{-- STATUS --}}
    @if(auth()->user()->kyc_status == 'verified')
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        ✅ Your account is verified
    </div>
@elseif(auth()->user()->kyc_status == 'pending')
    <div class="bg-yellow-100 text-yellow-700 p-3 rounded mb-4">
        ⏳ Your KYC is under review
    </div>
@else
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        ❌ You are not verified
    </div>
@endif

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium">ID Type</label>
            <select name="id_type" class="w-full border p-2 rounded">
                <option value="nin">NIN</option>
                <option value="passport">Passport</option>
                <option value="driver">Driver License</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">ID Number</label>
            <input type="text" name="id_number" class="w-full border p-2 rounded" placeholder="Enter ID number">
        </div>

        <div>
            <label class="block text-sm font-medium">Upload ID Document</label>
            <input type="file" name="id_document" class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block text-sm font-medium">Upload Selfie</label>
            <input type="file" name="selfie" class="w-full border p-2 rounded">
        </div>

        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
            Submit KYC
        </button>
    </form>

</div>

@endsection