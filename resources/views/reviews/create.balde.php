@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12">

    <h2 class="text-2xl font-semibold mb-6">
        Write Review for {{ $item->title }}
    </h2>

    <form method="POST" action="{{ route('reviews.store', $item->id) }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-2">Rating</label>
            <select name="rating"
                class="w-full border rounded-lg px-3 py-2" required>
                <option value="">Select rating</option>
                @for($i=5;$i>=1;$i--)
                    <option value="{{ $i }}">{{ $i }} Star</option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block mb-2">Comment</label>
            <textarea name="comment"
                class="w-full border rounded-lg px-3 py-2" rows="4"
                required></textarea>
        </div>

        <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            Submit Review
        </button>

    </form>
</div>
@endsection
