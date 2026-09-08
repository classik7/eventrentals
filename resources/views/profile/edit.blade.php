@extends('layouts.app')

@section('title', auth()->user()->name)

@section('content')

<div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded-2xl shadow">

    <h2 class="text-2xl font-bold mb-6">Edit Profile 👤</h2>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- BASIC INFO --}}
        <div>
            <h3 class="font-semibold mb-2">Basic Info</h3>

            <input type="text" name="phone"
                value="{{ auth()->user()->phone }}"
                placeholder="Phone Number"
                class="w-full border p-2 rounded mb-3">

            <textarea name="bio"
                placeholder="Tell us about yourself"
                class="w-full border p-2 rounded">{{ auth()->user()->bio }}</textarea>
        </div>

        {{-- PROFILE IMAGES --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-2">Profile Images</h3>

            <label class="block text-sm">Profile Photo</label>
            <input type="file" name="profile_photo" class="w-full border p-2 rounded mb-2">

            @if(auth()->user()->profile_photo)
                <img src="{{ asset('storage/'.auth()->user()->profile_photo) }}"
                     class="w-16 h-16 rounded-full mt-2 mb-4 border object-cover">
            @endif

            <label class="block text-sm">Cover Photo</label>
            <input type="file" name="cover_photo" class="w-full border p-2 rounded mb-2">

            @if(auth()->user()->cover_photo)
                <img src="{{ asset('storage/'.auth()->user()->cover_photo) }}"
                     class="w-full h-28 object-cover rounded mt-2 border">
            @endif
        </div>

        {{-- KYC --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-2">KYC Verification Tier 1</h3>

            <label class="block text-sm">Upload ID Document</label>
            <input type="file" name="id_document" class="w-full border p-2 rounded mb-3">

            <label class="block text-sm">Upload Selfie</label>
            <input type="file" name="selfie" class="w-full border p-2 rounded">
        </div>

        <button class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
            Save Changes
        </button>

    </form>

</div>

@endsection