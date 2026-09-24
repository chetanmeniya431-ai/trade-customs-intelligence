<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Contact Requests</h1>
            <p class="mt-1 text-sm text-gray-500">People who want their own system after viewing the demo.</p>
        </div>
        @if($newCount > 0)
            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">
                {{ $newCount }} new
            </span>
        @endif
    </div>

    {{-- Filter tabs --}}
    <div class="mb-4 flex gap-2 border-b border-gray-200">
        @foreach(['new' => 'New', 'responded' => 'Responded', 'all' => 'All'] as $value => $label)
            <button wire:click="filterBy('{{ $value }}')" wire:loading.attr="disabled" wire:target="filterBy('{{ $value }}')"
                    class="pb-3 px-1 text-sm font-medium border-b-2 -mb-px transition-colors disabled:opacity-60
                           {{ $filter === $value ? 'border-teal-600 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    @if($requests->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 py-16 text-center">
            <p class="text-sm text-gray-500">No contact requests yet.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name / Company</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Message</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Received</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $req->name }}</p>
                                @if($req->company)
                                    <p class="text-xs text-gray-500">{{ $req->company }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-700">{{ $req->email }}</p>
                                @if($req->phone)
                                    <p class="text-xs text-gray-500">{{ $req->phone }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $req->message }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $req->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3">
                                @if($req->status === 'new')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">New</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Responded</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($markingId === $req->id)
                                    <div class="flex items-center gap-2">
                                        <input wire:model="notes" type="text" placeholder="Add a note..."
                                               wire:loading.attr="disabled" wire:target="saveResponse"
                                               class="rounded-md border border-gray-300 px-2 py-1 text-xs focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                                        <button wire:click="saveResponse" wire:loading.attr="disabled" wire:target="saveResponse"
                                                class="rounded-md bg-teal-600 px-2 py-1 text-xs font-medium text-white hover:bg-teal-700 disabled:opacity-60">
                                            <span wire:loading.remove wire:target="saveResponse">Save</span>
                                            <span wire:loading wire:target="saveResponse">Saving…</span>
                                        </button>
                                        <button wire:click="cancelRespond" wire:loading.attr="disabled" wire:target="saveResponse"
                                                class="rounded-md border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-60">Cancel</button>
                                    </div>
                                @else
                                    <button wire:click="startRespond({{ $req->id }})"
                                            wire:loading.attr="disabled" wire:target="startRespond({{ $req->id }})"
                                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-60">
                                        <span wire:loading.remove wire:target="startRespond({{ $req->id }})">Mark responded</span>
                                        <span wire:loading wire:target="startRespond({{ $req->id }})">Loading…</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    @endif
</div>
