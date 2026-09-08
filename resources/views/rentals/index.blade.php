<!DOCTYPE html>
<html>
<head>
    <title>My Rental Requests</title>
</head>
<body>

<h2>📥 My Rental Requests (Owner)</h2>

@if($rentals->count() === 0)
    <p><strong>No rental requests yet.</strong></p>
@endif

@foreach($rentals as $rental)
    <div style="border:1px solid #ccc;padding:15px;margin-bottom:12px;">
        <p><strong>Item ID:</strong> {{ $rental->item_id }}</p>
        <p><strong>Renter ID:</strong> {{ $rental->renter_id }}</p>
        <p><strong>Status:</strong> {{ ucfirst($rental->status) }}</p>

        @if($rental->status === 'pending')
            <form method="POST" action="{{ route('rentals.accept', $rental->id) }}">
                @csrf
                <button>Accept</button>
            </form>

            <form method="POST" action="{{ route('rentals.reject', $rental->id) }}">
                @csrf
                <button>Reject</button>
            </form>
        @endif
    </div>
@endforeach

<a href="{{ route('items.index') }}">← Back Home</a>

</body>
</html>
