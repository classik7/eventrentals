@extends('layouts.app')

@section('title','Finance Overview')

@section('content')
<div class="max-w-6xl mx-auto py-10 px-4">

    <h1 class="text-2xl font-semibold mb-8">📊 Finance Overview</h1>

    {{-- ================= DATE FILTER ================= --}}
    <form method="GET" class="bg-white p-6 rounded-2xl shadow mb-8">
        <div class="flex flex-col md:flex-row gap-4 items-end">

            <div>
                <label class="text-sm text-gray-500">Start Date</label>
                <input type="date"
                       name="start_date"
                       value="{{ request('start_date') }}"
                       class="border rounded-lg px-3 py-2 w-full">
            </div>

            <div>
                <label class="text-sm text-gray-500">End Date</label>
                <input type="date"
                       name="end_date"
                       value="{{ request('end_date') }}"
                       class="border rounded-lg px-3 py-2 w-full">
            </div>

            <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                Filter
            </button>

            <a href="{{ route('admin.finance') }}"
               class="bg-yellow-600 text-white px-6 py-2 border rounded-lg hover:bg-yellow-700 transition">
               Reset
            </a>
			
			<a href="{{ route('admin.finance.export', request()->all()) }}"
				class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
				Export CSV
			</a>
        </div>
    </form>
    {{-- ================= END DATE FILTER ================= --}}

	<div class="mb-10">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8">

        <div class="flex items-center justify-between">
			<div class="mb-6">
    <button onclick="showCommissionPrompt()"
        class="bg-black text-white px-6 py-3 rounded-xl shadow hover:bg-gray-800">

        🔐 View Hidden Commission
    </button>

    <div id="commissionResult" class="mt-3 text-lg font-semibold text-green-600"></div>
</div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-800 font-medium">
                    Platform Wallet Balance
                </p>

                <h2 class="text-3xl font-semibold text-slate-800 mt-2">
                    ₦{{ number_format($platformBalance,2) }}
                </h2>
            </div>

            <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gray-100">
                <span class="text-xl">🏦</span>
            </div>

        </div>

    </div>
</div>


    {{-- ================= SUMMARY CARDS ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-gray-500 text-sm">Total Commission</p>
			
                <h2 id="totalCommissionValue" class="text-2xl font-bold text-green-600">
			🔒 Locked
	</h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-gray-500 text-sm">Filtered Commission</p>
            <h2 id="filteredCommissionValue" class="text-2xl font-bold text-blue-600">
				🔒 Locked
			</h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-gray-500 text-sm">Total Payouts</p>
            <h2 class="text-2xl font-bold text-red-600">
                ₦{{ number_format($totalPayouts,2) }}
            </h2>
        </div>
        
        <div class="bg-white rounded-2xl shadow p-6">
            <p class="text-gray-500 text-sm">Net Platform Profit</p>
            <h2 id="profitValue" class="text-2xl font-bold text-purple-600">
				🔒 Locked
			</h2>
        </div>

    </div>
    {{-- ================= END SUMMARY CARDS ================= --}}


    {{-- ================= CHART ================= --}}
    <div class="bg-white rounded-2xl shadow p-6 mt-10">
        <h2 class="text-lg font-semibold mb-4">
            📈 Monthly Commission (Last 12 Months)
        </h2>

        <canvas id="commissionChart" height="100"></canvas>
    </div>
    {{-- ================= END CHART ================= --}}


    {{-- ================= LEADERBOARD ================= --}}
    <div class="bg-white rounded-2xl shadow p-6 mt-10">
        <h2 class="text-lg font-semibold mb-4">🏆 Top 5 Earning Owners</h2>

        <div class="space-y-3">
            @forelse($topOwnersData as $index => $owner)

                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-500">
                            #{{ $index + 1 }}
                        </span>
                        <span class="font-medium">
                            {{ $owner['name'] }}
                        </span>
                    </div>

                    <span class="font-semibold text-green-600">
                        ₦{{ number_format($owner['earnings'],2) }}
                    </span>
                </div>

            @empty
                <p class="text-gray-500 text-sm">
                    No earnings data yet.
                </p>
            @endforelse
        </div>
    </div>
    {{-- ================= END LEADERBOARD ================= --}}

</div>


{{-- ================= CHART SCRIPT ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('commissionChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Commission (₦)',
                data: @json($monthlyCommission),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

});
</script>
{{-- ================= END CHART SCRIPT ================= --}}

<script>
function showCommissionPrompt(){

    let password = prompt("Enter Finance Password");
    if(!password) return;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch("{{ route('admin.commission.check') }}",{
        method:"POST",
        headers:{
            "X-CSRF-TOKEN": token
        },
        body: new URLSearchParams({
            password: password
        })
    })
    .then(async response => {

        let text = await response.text();
        console.log("SERVER RESPONSE:", text);

        try{
            let data = JSON.parse(text);
			
			console.log(data); // <-- ADD THIS
			
            if(data.success){

               document.getElementById("totalCommissionValue").innerHTML =
    "₦" + (parseFloat(data.totalCommission) || 0).toLocaleString();

document.getElementById("filteredCommissionValue").innerHTML =
    "₦" + (parseFloat(data.filteredCommission) || 0).toLocaleString();

document.getElementById("profitValue").innerHTML =
    "₦" + (parseFloat(data.netProfit) || 0).toLocaleString();

            }else{
                alert("Wrong finance password");
            }

        }catch(e){
            alert("Server returned an error. Open console.");
        }

    })
    .catch(error=>{
        console.error("FETCH ERROR:", error);
        alert("Request failed");
    });

}
</script>
@endsection