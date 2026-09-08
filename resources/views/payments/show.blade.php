@extends('layouts.app')

@section('content')
    <h2 style="margin-bottom:20px;">💳 Payment</h2>

    @if(session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <h3>{{ $rental->item->title }}</h3>

        <p>
            <strong>Rental Period:</strong><br>
            {{ $rental->start_date }} → {{ $rental->end_date }}
        </p>

        <p>
            <strong>Price per day:</strong><br>
            ₦{{ number_format($rental->item->price_per_day) }}
        </p>

        <p>
            <strong>Status:</strong><br>
            <span class="status-badge status-{{ $rental->status }}">
                {{ ucfirst($rental->status) }}
            </span>
        </p>

        <form method="POST" action="{{ route('payment.pay', $rental) }}">
            @csrf
            <button class="btn btn-green">
                Pay Now
            </button>
        </form>
    </div>

    <div style="margin-top:20px;">
        <a href="{{ route('rentals.my') }}" class="btn btn-gray">
            ← Back to My Rentals
        </a>
    </div>
@endsection
