@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto mt-6">

    <h2 class="text-xl font-semibold mb-4">🛠 Support Dashboard</h2>

    <div class="bg-white shadow rounded-xl p-4">

        @foreach($conversations as $conv)
            <div class="border-b py-3 flex justify-between items-center">

                <div>
                    <p class="font-medium">User ID: {{ $conv->user_id }}</p>
                    <p class="text-sm text-gray-500">Status: {{ $conv->status }}</p>
                </div>

                <a href="{{ route('support.show', $conv->id) }}"
                   class="bg-blue-600 text-white px-3 py-1 rounded-lg">
                    Open Chat
                </a>

            </div>
        @endforeach

    </div>

</div>

@endsection