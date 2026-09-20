<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900">Shipments</h1>
        <div class="flex gap-2">
            @role('Customs Broker|Import/Export Coordinator')
            <a href="{{ route('shipments.import') }}" class="btn btn-secondary">
                <x-icon name="arrow-up-tray" class="h-4 w-4" /> Bulk import
            </a>
            <a href="{{ route('shipments.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> New shipment
            </a>
            @endrole
        </div>
    </div>

    <div class="card p-4 flex flex-wrap gap-3">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Search reference, product, HS code…" class="form-input max-w-xs">
        <select wire:model.live="status" class="form-select max-w-[160px]">
            <option value="">All statuses</option>
            @foreach (['draft','documents_pending','ready','filed','cleared','held'] as $s)
                <option value="{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select wire:model.live="direction" class="form-select max-w-[140px]">
            <option value="">Import & export</option>
            <option value="import">Import</option>
            <option value="export">Export</option>
        </select>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Route</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Documents</th>
                    <th class="px-4 py-3">Findings</th>
                    <th class="px-4 py-3">Deadline</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($shipments as $shipment)
                    <tr wire:key="shipment-{{ $shipment->id }}" class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('shipments.show', $shipment) }}'">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $shipment->reference }}</p>
                            <p class="text-xs text-gray-500">{{ $shipment->client->name ?? 'No client' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $shipment->origin_country }} → {{ $shipment->destination_country }}
                            <p class="text-xs text-gray-400 uppercase">{{ $shipment->mode }} · {{ ucfirst($shipment->direction) }}</p>
                        </td>
                        <td class="px-4 py-3"><x-status-badge :status="$shipment->status" /></td>
                        <td class="px-4 py-3 w-40">
                            @php $pct = $shipment->documentCompletionPercent(); @endphp
                            <div class="flex items-center gap-2">
                                <div class="h-2 flex-1 rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full {{ $pct == 100 ? 'bg-green-500' : 'bg-teal-500' }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-9 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($shipment->open_findings_count > 0)
                                <span class="badge badge-red">{{ $shipment->open_findings_count }} open</span>
                            @else
                                <span class="badge badge-green">Clear</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($shipment->filing_deadline)
                                @php $hrs = now()->diffInHours($shipment->filing_deadline, false); @endphp
                                <span class="{{ $hrs <= 24 ? 'text-red-600 font-medium' : ($hrs <= 48 ? 'text-yellow-600' : 'text-gray-600') }}">
                                    {{ $shipment->filing_deadline->format('d M, H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No shipments found.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    {{ $shipments->links() }}
</div>
