@extends('layouts.app')

@section('content')
    <h2 style="margin-bottom:20px;">📥 Rental Requests</h2>

    @forelse($rentals as $rental)
        <div class="card">
            <div style="flex:1;">
                <h3>{{ $rental->item->title }}</h3>

                <p class="muted">
                    Renter: {{ $rental->renter->name ?? 'Unknown user' }}
                </p>

                <p class="muted">
                    📅 {{ $rental->start_date }} → {{ $rental->end_date }}
                </p>

                <span class="status-badge status-{{ $rental->status }}">
                    {{ ucfirst($rental->status) }}
                </span>
            </div>

            @if($rental->status === 'pending')
                <div style="display:flex; gap:10px;">
                    <form method="POST" action="{{ route('rentals.approve', $rental) }}">
                        @csrf
                        <button class="btn btn-green">Accept</button>
                    </form>

                    <form method="POST" action="{{ route('rentals.reject', $rental) }}">
                        @csrf
                        <button class="btn btn-red">Reject</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <p class="muted">No rental requests yet.</p>
    @endforelse
@endsection
