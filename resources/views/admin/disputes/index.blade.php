@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-10">

    <h2 class="text-2xl font-semibold mb-6">
        🛡 Disputes
    </h2>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-4">Rental</th>
                    <th class="p-4">Renter</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>

                @forelse($disputes as $dispute)
                <tr class="border-t hover:bg-gray-50">

                    <td class="p-4">
                        {{ $dispute->rental->item->title }}
                    </td>

                    <td class="p-4">
                        {{ $dispute->rental->renter->name }}
                    </td>

                    <td class="p-4">
                        <span class="px-3 py-1 text-xs rounded-full
                            @if($dispute->status === 'open') bg-yellow-100 text-yellow-700
                            @elseif($dispute->status === 'under_review') bg-blue-100 text-blue-700
                            @elseif($dispute->status === 'resolved') bg-green-100 text-green-700
                            @elseif($dispute->status === 'rejected') bg-red-100 text-red-700
                            @endif">
                            {{ ucfirst($dispute->status) }}
                        </span>
                    </td>

                    <td class="p-4">
                        {{ $dispute->created_at->format('M d, Y') }}
                    </td>

                    <td class="p-4 text-right">
                        <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                           class="text-blue-600 hover:underline">
                            View
                        </a>
                    </td>

                </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-400">
                            No disputes yet.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

    </div>

</div>
@endsection