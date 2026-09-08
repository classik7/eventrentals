@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto p-6">

<h2 class="text-2xl font-bold mb-6">KYC Verification Panel 🪪</h2>

@if(session('success'))
    <div class="bg-green-200 p-3 mb-4 rounded">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="bg-red-200 p-3 mb-4 rounded">{{ session('error') }}</div>
@endif

<table class="w-full border rounded-lg overflow-hidden">

<thead class="bg-gray-100">
<tr>
    <th class="p-3">User</th>
    <th>ID Type</th>
    <th>ID</th>
    <th>Document</th>
    <th>Selfie</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

@foreach($users as $user)
<tr class="border-t">

<td class="p-3">{{ $user->name }}</td>
<td>{{ $user->id_type }}</td>
<td>{{ $user->id_number }}</td>

<td>
    <a href="{{ asset('storage/'.$user->id_document) }}" target="_blank">
        View ID
    </a>
</td>

<td>
    <a href="{{ asset('storage/'.$user->selfie) }}" target="_blank">
        View Selfie
    </a>
</td>

<td class="flex gap-2 p-3">

<form method="POST" action="{{ route('admin.kyc.approve', $user->id) }}">
    @csrf
    <button class="bg-green-600 text-white px-3 py-1 rounded">
        Approve
    </button>
</form>

<form method="POST" action="{{ route('admin.kyc.reject', $user->id) }}">
    @csrf
    <input type="text" name="reason" placeholder="Reason" class="border p-1 text-xs">
    <button class="bg-red-600 text-white px-3 py-1 rounded">
        Reject
    </button>
</form>

</td>

</tr>
@endforeach

</tbody>

</table>

</div>

@endsection