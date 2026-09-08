@extends('layouts.app')

@section('content')
<div class="container">

    <h2>💰 Wallet</h2>

    <p><strong>Available Balance:</strong> ₦{{ number_format($user->wallet_available, 2) }}</p>
    <p><strong>Pending Balance:</strong> ₦{{ number_format($user->wallet_pending, 2) }}</p>

    <hr>

    <h4>Transactions</h4>

    <table class="table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            @foreach($transactions as $tx)
                <tr>
                    <td>{{ ucfirst($tx->type) }}</td>
                    <td>₦{{ number_format($tx->amount, 2) }}</td>
                    <td>{{ $tx->description }}</td>
                    <td>{{ $tx->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $transactions->links() }}

</div>
@endsection