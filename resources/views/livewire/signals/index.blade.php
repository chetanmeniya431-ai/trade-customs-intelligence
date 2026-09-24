<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900">Signals</h1>
        @role('Customs Broker|Compliance Manager')
        <button wire:click="runCheckNow" class="btn btn-secondary" wire:loading.attr="disabled">
            <x-icon name="bell-alert" class="h-4 w-4" /> Run check now
        </button>
        @endrole
    </div>

    @if (session('status'))
        <div class="rounded-md bg-teal-50 border border-teal-200 p-3 text-sm text-teal-800">{{ session('status') }}</div>
    @endif

    <div class="flex gap-2">
        @foreach (['open' => 'Open', 'resolved' => 'Resolved', 'all' => 'All'] as $key => $label)
            <button wire:click="$set('filter', '{{ $key }}')" wire:loading.attr="disabled" wire:target="$set('filter', '{{ $key }}')"
                class="rounded-full px-3 py-1 text-xs font-medium disabled:opacity-60 {{ $filter === $key ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Signal</th>
                    <th class="px-4 py-3">Shipment</th>
                    <th class="px-4 py-3">Severity</th>
                    <th class="px-4 py-3">Triggered</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($events as $event)
                    <tr wire:key="event-{{ $event->id }}">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $event->signal->name }}</p>
                            <p class="text-xs text-gray-500">{{ $event->note }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if ($event->shipment)
                                <a href="{{ route('shipments.show', $event->shipment) }}" class="text-teal-600 hover:text-teal-700">{{ $event->shipment->reference }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"><x-severity-badge :severity="$event->signal->severity" /></td>
                        <td class="px-4 py-3 text-gray-600">{{ $event->triggered_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            @if ($event->isOpen())
                                <span class="badge badge-red">Open</span>
                            @else
                                <span class="badge badge-green">Resolved</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @unlessrole('Client|Finance')
                                @if ($event->isOpen())
                                    <button wire:click="resolve({{ $event->id }})" wire:loading.attr="disabled" wire:target="resolve({{ $event->id }})" class="text-teal-600 hover:text-teal-700 text-xs font-medium disabled:opacity-60">
                                        <span wire:loading.remove wire:target="resolve({{ $event->id }})">Mark resolved</span>
                                        <span wire:loading wire:target="resolve({{ $event->id }})">Resolving…</span>
                                    </button>
                                @endif
                            @endunlessrole
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No signal events.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</div>
