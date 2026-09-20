<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
        @role('Customs Broker|Import/Export Coordinator')
        <a href="{{ route('shipments.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="h-4 w-4" /> New shipment
        </a>
        @endrole
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card p-5">
            <p class="text-sm text-gray-500">Open signals</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $openSignalsCount }}</p>
            <a href="{{ route('signals.index') }}" class="mt-2 inline-block text-sm text-teal-600 hover:text-teal-700">View signals →</a>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Deadlines in next 7 days</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $deadlineShipments->count() }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Open findings</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $recentFindings->count() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-5 lg:col-span-2">
            <h2 class="font-medium text-gray-900 mb-4">Deadline timeline — next 7 days</h2>
            @if ($deadlineShipments->isEmpty())
                <p class="text-sm text-gray-500">No filing deadlines in the next 7 days.</p>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($deadlineShipments as $shipment)
                        @php
                            $hoursLeft = now()->diffInHours($shipment->filing_deadline, false);
                            $urgency = $hoursLeft <= 24 ? 'text-red-600' : ($hoursLeft <= 48 ? 'text-yellow-600' : 'text-gray-600');
                        @endphp
                        <a href="{{ route('shipments.show', $shipment) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $shipment->reference }}</p>
                                <p class="text-xs text-gray-500">{{ $shipment->origin_country }} → {{ $shipment->destination_country }} · {{ $shipment->documentCompletionPercent() }}% documents · {{ $shipment->openFindings()->count() }} open findings</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium {{ $urgency }}">{{ $shipment->filing_deadline->diffForHumans() }}</p>
                                <x-status-badge :status="$shipment->status" />
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card p-5">
            <h2 class="font-medium text-gray-900 mb-4">Shipments by status</h2>
            <canvas id="statusChart" height="220"></canvas>
        </div>
    </div>

    <div class="card p-5">
        <h2 class="font-medium text-gray-900 mb-4">Recent findings</h2>
        @if ($recentFindings->isEmpty())
            <p class="text-sm text-gray-500">No open findings.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($recentFindings as $finding)
                    <a href="{{ route('shipments.show', $finding->shipment) }}" class="flex items-start gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded">
                        <x-severity-badge :severity="$finding->severity" class="mt-0.5" />
                        <div class="min-w-0">
                            <p class="text-sm text-gray-900">{{ $finding->description }}</p>
                            <p class="text-xs text-gray-500">{{ $finding->shipment->reference }} · {{ $finding->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:navigated', initStatusChart);
        document.addEventListener('DOMContentLoaded', initStatusChart);
        function initStatusChart() {
            const el = document.getElementById('statusChart');
            if (!el || el.dataset.rendered) return;
            el.dataset.rendered = '1';
            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(collect($statusCounts)->keys()->map(fn($s) => ucwords(str_replace('_',' ',$s)))->values()) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($statusCounts->toArray())) !!},
                        backgroundColor: ['#9ca3af', '#facc15', '#3b82f6', '#6366f1', '#22c55e', '#ef4444'],
                    }]
                },
                options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
            });
        }
    </script>
</div>
