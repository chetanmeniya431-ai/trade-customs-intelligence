<div class="space-y-6">
    <h1 class="text-xl font-semibold text-gray-900">Compliance Insights</h1>
    <p class="text-sm text-gray-500 -mt-4">Patterns across all shipments — informational trends, not signals.</p>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-5">
            <h2 class="font-medium text-gray-900 mb-3">Products most often triggering HS code warnings</h2>
            <div class="space-y-2">
                @forelse ($hsWarningsByProduct as $row)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700 truncate pr-2">{{ Str::limit($row->product_description, 45) }}</span>
                        <span class="badge badge-red shrink-0">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No HS code warnings yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-medium text-gray-900 mb-3">Country pairs with missing documents</h2>
            <div class="space-y-2">
                @forelse ($missingDocsByRoute as $row)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $row->route }}</span>
                        <span class="badge badge-yellow shrink-0">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No missing-document findings yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="font-medium text-gray-900 mb-3">Clients with value mismatches</h2>
            <div class="space-y-2">
                @forelse ($valueMismatchByClient as $row)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $row->name }}</span>
                        <span class="badge badge-red shrink-0">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No value mismatches yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
