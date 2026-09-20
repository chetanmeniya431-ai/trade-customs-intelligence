<div class="space-y-6 max-w-3xl">
    <div class="flex items-center gap-3">
        <a href="{{ route('shipments.index') }}" class="text-gray-400 hover:text-gray-600"><x-icon name="arrow-left" class="h-5 w-5" /></a>
        <h1 class="text-xl font-semibold text-gray-900">New shipment</h1>
    </div>

    <form wire:submit="save" class="card p-6 space-y-5">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="form-label">Shipment reference <span class="text-gray-400 font-normal">(optional — auto-generated)</span></label>
                <input wire:model="reference" type="text" class="form-input" placeholder="SHP-2026-0001">
                @error('reference') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Direction</label>
                <select wire:model="direction" class="form-select">
                    <option value="import">Import</option>
                    <option value="export">Export</option>
                </select>
            </div>
            <div>
                <label class="form-label">Origin country</label>
                <input wire:model="origin_country" type="text" class="form-input" placeholder="China">
                @error('origin_country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Destination country</label>
                <input wire:model="destination_country" type="text" class="form-input" placeholder="India">
                @error('destination_country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="relative">
            <label class="form-label">Product description</label>
            <textarea wire:model.live.debounce.600ms="product_description" rows="3" class="form-input" placeholder="e.g. cotton fabric, woven, 100% cotton"></textarea>
            @error('product_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

            @if ($similarMatch)
                <div class="mt-2 rounded-md border border-teal-200 bg-teal-50 p-3 text-sm">
                    <p class="text-teal-800">
                        <x-icon name="sparkles" class="h-4 w-4 inline -mt-0.5" />
                        This shipment looks similar to <strong>{{ $similarMatch['reference'] }}</strong>
                        ({{ $similarMatch['origin_country'] }} → {{ $similarMatch['destination_country'] }},
                        {{ $similarMatch['declared_currency'] }} {{ number_format($similarMatch['declared_value'], 0) }},
                        {{ round($similarMatch['similarity'] * 100) }}% similar).
                    </p>
                    <button type="button" wire:click="copyFromSimilar({{ $similarMatch['id'] }})" class="mt-2 text-teal-700 font-medium hover:underline">
                        Use its HS code &amp; route as a starting point →
                    </button>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div>
                <label class="form-label">HS code</label>
                <input wire:model="hs_code" type="text" class="form-input" placeholder="5208">
            </div>
            <div>
                <label class="form-label">Declared value</label>
                <input wire:model="declared_value" type="number" step="0.01" class="form-input">
                @error('declared_value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Currency</label>
                <input wire:model="declared_currency" type="text" maxlength="3" class="form-input uppercase">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div>
                <label class="form-label">Mode</label>
                <select wire:model="mode" class="form-select">
                    <option value="air">Air</option>
                    <option value="sea">Sea</option>
                    <option value="road">Road</option>
                    <option value="rail">Rail</option>
                </select>
            </div>
            <div>
                <label class="form-label">Incoterms</label>
                <select wire:model="incoterms" class="form-select">
                    @foreach (['EXW','FOB','CIF','DDP','FCA','CPT'] as $term)
                        <option value="{{ $term }}">{{ $term }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Client</label>
                <select wire:model="client_id" class="form-select">
                    <option value="">— Optional —</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="form-label">Expected arrival/departure date</label>
                <input wire:model="expected_date" type="date" class="form-input">
            </div>
            <div>
                <label class="form-label">Customs filing deadline</label>
                <input wire:model="filing_deadline" type="datetime-local" class="form-input">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <a href="{{ route('shipments.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Create shipment</span>
                <span wire:loading wire:target="save">Creating…</span>
            </button>
        </div>
    </form>
</div>
