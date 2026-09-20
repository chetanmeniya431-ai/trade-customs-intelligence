<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('shipments.index') }}" class="text-gray-400 hover:text-gray-600"><x-icon name="arrow-left" class="h-5 w-5" /></a>
        <h1 class="text-xl font-semibold text-gray-900">Bulk import shipments</h1>
    </div>

    <div class="card p-6 space-y-5">
        <p class="text-sm text-gray-600">
            Upload a CSV or Excel file with columns: <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">reference, direction, origin_country, destination_country, product_description, hs_code, declared_value, declared_currency, mode, incoterms, expected_date, filing_deadline, client_name</code>.
            Each row creates a shipment and its document checklist is generated automatically from the origin/destination/mode.
        </p>
        <a href="/samples/shipments-import-template.csv" class="text-teal-600 hover:text-teal-700 text-sm font-medium">Download CSV template →</a>

        <form wire:submit="import" class="space-y-3">
            <input type="file" wire:model="file" accept=".csv,.xlsx,.xls" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-teal-700 hover:file:bg-teal-100">
            @error('file') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            <div wire:loading wire:target="file" class="text-xs text-gray-500">Uploading…</div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="import">
                <span wire:loading.remove wire:target="import">Import shipments</span>
                <span wire:loading wire:target="import">Importing…</span>
            </button>
        </form>

        @if (! is_null($createdCount))
            <div class="rounded-md bg-teal-50 border border-teal-200 p-3 text-sm text-teal-800">
                Imported {{ $createdCount }} shipment(s).
            </div>
        @endif

        @if (count($rowErrors) > 0)
            <div class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800 space-y-1">
                <p class="font-medium">{{ count($rowErrors) }} row(s) failed:</p>
                @foreach ($rowErrors as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </div>
</div>
