<div class="space-y-6" x-data="{ uploadingFor: null }">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('shipments.index') }}" class="text-gray-400 hover:text-gray-600"><x-icon name="arrow-left" class="h-5 w-5" /></a>
        <h1 class="text-xl font-semibold text-gray-900">{{ $shipment->reference }}</h1>
        <x-status-badge :status="$shipment->status" />
    </div>

    @if (session('status'))
        <div class="rounded-md bg-teal-50 border border-teal-200 p-3 text-sm text-teal-800">{{ session('status') }}</div>
    @endif

    @if ($similar)
        <div class="rounded-md border border-teal-200 bg-teal-50 p-4 text-sm">
            <p class="text-teal-800">
                <x-icon name="sparkles" class="h-4 w-4 inline -mt-0.5" />
                This shipment looks similar to <a href="{{ route('shipments.show', $similar['shipment']) }}" class="font-semibold underline">{{ $similar['shipment']->reference }}</a>
                ({{ $similar['shipment']->product_description }}, {{ $similar['shipment']->origin_country }} → {{ $similar['shipment']->destination_country }},
                {{ round($similar['similarity'] * 100) }}% similar) — completed
                {{ $similar['shipment']->status === 'cleared' ? $similar['shipment']->updated_at->diffForHumans() : 'recently' }}.
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card p-5 lg:col-span-2">
            <h2 class="font-medium text-gray-900 mb-4">Shipment details</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm sm:grid-cols-3">
                @unlessrole('Finance')
                <div><dt class="text-gray-500">Direction</dt><dd class="text-gray-900">{{ ucfirst($shipment->direction) }}</dd></div>
                <div><dt class="text-gray-500">Route</dt><dd class="text-gray-900">{{ $shipment->origin_country }} → {{ $shipment->destination_country }}</dd></div>
                <div><dt class="text-gray-500">Mode</dt><dd class="text-gray-900 uppercase">{{ $shipment->mode }}</dd></div>
                <div><dt class="text-gray-500">Incoterms</dt><dd class="text-gray-900">{{ $shipment->incoterms ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">HS code</dt><dd class="text-gray-900">{{ $shipment->hs_code ?? '—' }}</dd></div>
                @endunlessrole
                <div><dt class="text-gray-500">Declared value</dt><dd class="text-gray-900 font-medium">{{ $shipment->declared_currency }} {{ number_format($shipment->declared_value, 2) }}</dd></div>
                @unlessrole('Finance')
                <div><dt class="text-gray-500">Client</dt><dd class="text-gray-900">{{ $shipment->client->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Expected date</dt><dd class="text-gray-900">{{ $shipment->expected_date?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Filing deadline</dt><dd class="text-gray-900">{{ $shipment->filing_deadline?->format('d M Y, H:i') ?? '—' }}</dd></div>
                @endunlessrole
            </dl>
            @unlessrole('Client|Finance')
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
                <label class="text-sm text-gray-500">Update status:</label>
                <select wire:change="updateStatus($event.target.value)" class="form-select max-w-[180px]">
                    @foreach (['draft','documents_pending','ready','filed','cleared','held'] as $s)
                        <option value="{{ $s }}" @selected($shipment->status === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            @endunlessrole
        </div>

        <div class="card p-5">
            <h2 class="font-medium text-gray-900 mb-3">Product description</h2>
            <p class="text-sm text-gray-700">{{ $shipment->product_description }}</p>
        </div>
    </div>

    @unlessrole('Finance')
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-medium text-gray-900">Document checklist</h2>
            <span class="text-sm text-gray-500">{{ $shipment->documentCompletionPercent() }}% complete</span>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach ($shipment->documents as $doc)
                <div wire:key="doc-{{ $doc->id }}" class="py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                {{ $doc->document_type }}
                                @if ($doc->required)
                                    <span class="badge badge-gray">Required</span>
                                @else
                                    <span class="badge badge-gray">Optional</span>
                                @endif
                                @if ($doc->isUploaded())
                                    @if ($doc->verified)
                                        <span class="badge badge-green">Verified</span>
                                    @else
                                        <span class="badge badge-blue">Uploaded</span>
                                    @endif
                                @else
                                    <span class="badge badge-yellow">Missing</span>
                                @endif
                            </p>
                            @if ($doc->isUploaded())
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Uploaded by {{ $doc->uploader->name ?? 'unknown' }} · {{ $doc->uploaded_at?->diffForHumans() }}
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($doc->isUploaded())
                                <a href="{{ route('shipments.documents.download', $doc) }}" class="btn btn-secondary !py-1.5 !px-2.5 text-xs">
                                    <x-icon name="eye" class="h-4 w-4" /> View
                                </a>
                                @unlessrole('Client')
                                    @if (!$doc->verified)
                                        <button wire:click="verifyDocument({{ $doc->id }})" class="btn btn-secondary !py-1.5 !px-2.5 text-xs">
                                            <x-icon name="check" class="h-4 w-4" /> Verify
                                        </button>
                                    @endif
                                @endunlessrole
                            @endif

                            @unlessrole('Client|Finance')
                                @if ($activeUploadDocId === $doc->id)
                                    <input type="file" wire:model="uploadFile" accept="application/pdf" class="text-xs">
                                @else
                                    <button wire:click="selectUploadTarget({{ $doc->id }})" class="btn btn-secondary !py-1.5 !px-2.5 text-xs">
                                        <x-icon name="arrow-up-tray" class="h-4 w-4" /> {{ $doc->isUploaded() ? 'Replace' : 'Upload' }}
                                    </button>
                                @endif
                            @endunlessrole
                        </div>
                    </div>

                    @if ($doc->findings->isNotEmpty())
                        <div class="mt-3 space-y-2 pl-1">
                            @foreach ($doc->findings as $finding)
                                <div class="rounded-md border border-red-100 bg-red-50 p-3 text-sm">
                                    <div class="flex items-start gap-2">
                                        <x-severity-badge :severity="$finding->severity" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-gray-800">{{ $finding->description }}</p>
                                            @if ($finding->suggested_value)
                                                <p class="text-xs text-gray-600 mt-0.5">Suggested value: <strong>{{ $finding->suggested_value }}</strong></p>
                                            @endif
                                            <p class="text-xs text-gray-500 mt-0.5">Status: {{ ucfirst($finding->status) }}</p>
                                        </div>
                                        @unlessrole('Client')
                                        @if ($finding->status === 'open')
                                            <div class="flex gap-1 shrink-0">
                                                <button wire:click="confirmFinding({{ $finding->id }})" class="btn btn-secondary !py-1 !px-2 text-xs">Confirm</button>
                                                <button wire:click="resolveFinding({{ $finding->id }})" class="btn btn-secondary !py-1 !px-2 text-xs">Resolve</button>
                                            </div>
                                        @endif
                                        @endunlessrole
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endunlessrole

    @role('Finance')
    <div class="card p-5">
        <h2 class="font-medium text-gray-900 mb-3">Payment documents</h2>
        <div class="divide-y divide-gray-100">
            @forelse ($shipment->documents->filter(fn($d) => str_contains(strtolower($d->document_type), 'invoice') || str_contains(strtolower($d->document_type), 'payment')) as $doc)
                <div class="py-3 flex items-center justify-between">
                    <span class="text-sm text-gray-900">{{ $doc->document_type }}</span>
                    @if ($doc->isUploaded())
                        <a href="{{ route('shipments.documents.download', $doc) }}" class="btn btn-secondary !py-1.5 !px-2.5 text-xs">View</a>
                    @else
                        <span class="badge badge-yellow">Missing</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 py-3">No invoice or payment documents on this shipment.</p>
            @endforelse
        </div>
    </div>
    @endrole
</div>
