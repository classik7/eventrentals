@extends('layouts.app')

@section('title','Payment Successful')

@section('content')
<div class="max-w-xl mx-auto py-20 text-center">

    <div class="text-6xl mb-6">✅</div>

    <h2 class="text-2xl font-semibold mb-4">
        Payment Successful!
    </h2>

    <p class="text-gray-600 mb-8">
        Your rentals have been submitted successfully.
        Owners will review your request shortly.
    </p>

    <div class="flex justify-center gap-4">

        <a href="{{ route('items.index') }}"
           class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700">
            Back to Homepage
        </a>

        <a href="{{ route('rentals.my') }}"
           class="border border-gray-300 px-6 py-3 rounded-xl hover:bg-gray-100">
            View My Rentals
        </a>

    </div>

</div>
@endsection
