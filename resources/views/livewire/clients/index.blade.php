<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900">Clients</h1>
        <button wire:click="$toggle('showForm')" class="btn btn-primary">
            <x-icon name="plus" class="h-4 w-4" /> New client
        </button>
    </div>

    @if ($showForm)
        <form wire:submit="save" class="card p-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="form-label">Company name</label>
                <input wire:model="name" type="text" class="form-input">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Country</label>
                <input wire:model="country" type="text" class="form-input">
            </div>
            <div>
                <label class="form-label">Contact name</label>
                <input wire:model="contact_name" type="text" class="form-input">
            </div>
            <div>
                <label class="form-label">Contact email</label>
                <input wire:model="contact_email" type="email" class="form-input">
                @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2">
                <button type="button" wire:click="$set('showForm', false)" wire:loading.attr="disabled" wire:target="save" class="btn btn-secondary">Cancel</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary">
                    <span wire:loading.remove wire:target="save">Save client</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </form>
    @endif

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Company</th>
                    <th class="px-4 py-3">Country</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Shipments</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($clients as $client)
                    <tr wire:key="client-{{ $client->id }}">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $client->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->country ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->contact_name }} @if($client->contact_email)<br><span class="text-xs">{{ $client->contact_email }}</span>@endif</td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->shipments_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">No clients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</div>
