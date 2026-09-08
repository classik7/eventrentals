@extends('layouts.app')

@section('content')

<div class="container">
    <h2>Request Withdrawal</h2>

    @if(session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="color: red;">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('owner.withdraw.store') }}" method="POST">
        @csrf

        <div>
            <label>Amount</label>
            <input type="number" name="amount" required>
        </div>

        <div>
            <label>Bank Name</label>
            <input type="text" name="bank_name" required>
        </div>

        <div>
            <label>Account Number</label>
            <input type="text" name="account_number" required>
        </div>

        <div>
            <label>Account Name</label>
            <input type="text" name="account_name" required>
        </div>

        <button type="submit">Submit Withdrawal</button>
    </form>
</div>

@endsection