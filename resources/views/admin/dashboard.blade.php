@extends('layouts.app')

@section('title','Platform Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-12 px-6">

    <h2 class="text-3xl font-bold mb-10">
        📊 Platform Control Center
    </h2>

    {{-- QUICK ACTIONS --}}
    <div class="grid md:grid-cols-4 gap-6 mb-12">

        <a href="{{ route('admin.disputes.index') }}"
           class="bg-white p-6 rounded-2xl shadow-sm border hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Open Disputes</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ $openDisputes }}
                    </p>
                </div>
                <div class="text-red-500 text-2xl">🛡</div>
            </div>
        </a>

        <a href="{{ route('admin.withdrawals.index') }}"
           class="bg-white p-6 rounded-2xl shadow-sm border hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Pending Withdrawals</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ $pendingWithdrawals }}
                    </p>
                </div>
                <div class="text-blue-500 text-2xl">💰</div>
            </div>
        </a>

        <a href="{{ route('admin.finance') }}"
           class="bg-white p-6 rounded-2xl shadow-sm border hover:shadow-md transition">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Finance Overview</p>
                    <p class="text-lg font-semibold text-green-600">
                        View Reports
                    </p>
                </div>
                <div class="text-green-500 text-2xl">📈</div>
            </div>
        </a>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-sm text-gray-500">System Status</p>
            <p class="text-lg font-semibold text-green-600">
                Operational
            </p>
        </div>

    </div>

    {{-- FINANCIAL METRICS --}}
    <div class="grid md:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-gray-500 text-sm">Total GMV</p>
            <p class="text-2xl font-bold text-blue-600">
                ₦{{ number_format($totalGMV, 2) }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-gray-500 text-sm">Platform Revenue</p>
            <p class="text-2xl font-bold text-green-600">
                ₦{{ number_format($platformRevenue, 2) }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-gray-500 text-sm">Total Platform Earnings</p>
            <p class="text-2xl font-bold text-green-700">
                ₦{{ number_format($totalPlatformEarnings, 2) }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-gray-500 text-sm">Owner Earnings</p>
            <p class="text-2xl font-bold text-purple-600">
                ₦{{ number_format($ownerEarnings, 2) }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-gray-500 text-sm">Transactions</p>
            <p class="text-2xl font-bold">
                {{ $totalTransactions }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-gray-500 text-sm">Average Order Value</p>
            <p class="text-2xl font-bold">
                ₦{{ number_format($averageOrderValue, 2) }}
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-gray-500 text-sm">Refunded Transactions</p>
            <p class="text-2xl font-bold text-red-600">
                {{ $refundedTransactions }}
            </p>
        </div>

    </div>

    {{-- MARKETPLACE GROWTH --}}
    <div class="grid md:grid-cols-2 gap-6 mt-10">

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <h3 class="text-lg font-semibold mb-4">📈 Marketplace Growth</h3>

            <div class="flex justify-between">

                <div>
                    <p class="text-gray-500 text-sm">Bookings This Month</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $currentMonthBookings }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500 text-sm">Bookings Last Month</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ $lastMonthBookings }}
                    </p>
                </div>

            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <h3 class="text-lg font-semibold mb-4">🔥 Trending Categories</h3>

            <table class="w-full text-sm">
                <thead class="border-b text-gray-500">
                    <tr>
                        <th class="text-left py-2">Category</th>
                        <th class="text-right py-2">Rentals</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($trendingCategories as $category)
                    <tr class="border-b">
                        <td class="py-2">{{ $category->name }}</td>
                        <td class="py-2 text-right font-semibold">
                            {{ $category->total_rentals }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection