<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900">Reports</h1>
        <a href="{{ route('reports.pdf') }}" class="btn btn-primary">
            <x-icon name="document-arrow-up" class="h-4 w-4" /> Download PDF report
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="card p-5">
            <p class="text-sm text-gray-500">Total shipments</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $totalShipments }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Open signals</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $openSignals }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Open findings</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $findingCounts->sum() }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Total declared value</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($totalDeclaredValue, 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card p-5">
            <h2 class="font-medium text-gray-900 mb-3">Shipments by status</h2>
            <div class="space-y-2">
                @foreach (['draft','documents_pending','ready','filed','cleared','held'] as $status)
                    <div class="flex items-center justify-between text-sm">
                        <x-status-badge :status="$status" />
                        <span class="font-medium text-gray-900">{{ $statusCounts[$status] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-medium text-gray-900 mb-3">Findings by type</h2>
            <div class="space-y-2">
                @forelse ($findingCounts as $type => $count)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ ucwords(str_replace('_',' ',$type)) }}</span>
                        <span class="font-medium text-gray-900">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No findings recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
