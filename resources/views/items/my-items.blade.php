@extends('layouts.app')

@php
    $breadcrumbs = '<a href="'.route('items.index').'">Home</a> / My Items';
@endphp

@section('content')
    <h2 style="margin-bottom:20px;">📦 My Listed Items</h2>

    @forelse($items as $item)
        <div class="card">
            @if($item->image)
                <img
                    src="{{ asset('storage/' . $item->image) }}"
                    class="thumb"
                    alt="{{ $item->title }}"
                >
            @endif

            <div style="flex:1;">
                <h3 style="margin-bottom:6px;">{{ $item->title }}</h3>

                <p class="muted">₦{{ number_format($item->price_per_day) }} / day</p>
                <p class="muted">📍 {{ $item->location }}</p>
                <p class="muted">Rentals: {{ $item->rentals_count }}</p>

                <span class="status-badge status-{{ $item->status }}">
                    {{ ucfirst($item->status) }}
                </span>
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
                <a
                    href="{{ route('items.edit', $item) }}"
                    class="btn btn-blue"
                >
                    Edit
                </a>

               <form method="POST"
      action="{{ route('items.destroy', $item) }}"
      onsubmit="return confirm('Are you sure you want to delete this item?')">
    @csrf
    @method('DELETE')
    <button class="btn btn-red">
        Delete
    </button>
</form>


                </form>
            </div>
        </div>
    @empty
        <div class="card" style="justify-content:center;">
            <p class="muted">
                You haven’t listed any items yet.
            </p>
        </div>
    @endforelse
@endsection
