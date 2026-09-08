<!DOCTYPE html>
<html>
<head>
    <title>Wallet Statement</title>
    <style>
        body { font-family: sans-serif; }
        h2 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>

<h2>Wallet Statement</h2>
<p>User: {{ $user->name }}</p>

<table>
    <thead>
        <tr>
            <th>Type</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        @foreach($transactions as $tx)
        <tr>
            <td>{{ strtoupper($tx->type) }}</td>
            <td>{{ $tx->description }}</td>
            <td>₦{{ number_format($tx->amount, 2) }}</td>
            <td>{{ $tx->created_at->format('d M Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>