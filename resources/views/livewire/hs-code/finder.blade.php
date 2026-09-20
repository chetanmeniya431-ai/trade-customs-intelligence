<div class="space-y-6 max-w-4xl">
    <h1 class="text-xl font-semibold text-gray-900">HS Code Finder</h1>
    <p class="text-sm text-gray-500 -mt-4">
        Describe the product. The system searches the embedded tariff schedule for the closest matches and asks the
        AI assistant for a suggested HS code. This is a suggestion tool — verify before using in a declaration.
    </p>

    <div class="card p-6 space-y-4">
        <form wire:submit="search" class="space-y-3">
            <textarea wire:model="description" rows="3" class="form-input" placeholder="Describe the product in detail: material, use, key specs…"></textarea>
            @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

            <div class="flex flex-wrap gap-2">
                @foreach ($examples as $example)
                    <button type="button" wire:click="useExample(@js($example))" class="text-xs rounded-full bg-gray-100 px-3 py-1 text-gray-600 hover:bg-gray-200">
                        {{ Str::limit($example, 40) }}
                    </button>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="search">
                <span wire:loading.remove wire:target="search"><x-icon name="magnifying-glass" class="h-4 w-4" /> Suggest HS code</span>
                <span wire:loading wire:target="search">Searching tariff schedule…</span>
            </button>
        </form>
    </div>

    @if ($result)
        <div class="card p-6 space-y-4" wire:key="result">
            @if (! $result['ollama_available'])
                <div class="rounded-md bg-yellow-50 border border-yellow-200 p-3 text-sm text-yellow-800">
                    The Ollama AI service did not respond — showing tariff schedule matches only, no AI-suggested code.
                </div>
            @endif

            @if (empty($result['suggestions']))
                <p class="text-sm text-gray-600">No confident suggestion could be generated. Try a more detailed description, or upload the relevant tariff schedule under Tariff Documents.</p>
            @else
                <div class="space-y-3">
                    @foreach ($result['suggestions'] as $suggestion)
                        <div class="flex flex-wrap items-start justify-between gap-2 rounded-md border border-gray-200 p-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900">{{ $suggestion['code'] ?? '—' }}</p>
                                <p class="text-sm text-gray-600">{{ $suggestion['heading'] ?? '' }}</p>
                            </div>
                            @php $conf = $suggestion['confidence'] ?? 'low'; @endphp
                            <span class="badge shrink-0 {{ $conf === 'high' ? 'badge-green' : ($conf === 'medium' ? 'badge-yellow' : 'badge-gray') }}">
                                {{ ucfirst($conf) }} confidence
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($result['basis'])
                <div>
                    <p class="text-xs font-medium uppercase text-gray-500">Basis</p>
                    <p class="text-sm text-gray-700">{{ $result['basis'] }}</p>
                </div>
            @endif

            @if ($result['common_mistakes'])
                <div>
                    <p class="text-xs font-medium uppercase text-gray-500">Common mistakes for this category</p>
                    <p class="text-sm text-gray-700">{{ $result['common_mistakes'] }}</p>
                </div>
            @endif

            @if ($result['matched_chunks']->isNotEmpty())
                <details class="text-sm">
                    <summary class="cursor-pointer text-gray-500">Matched tariff schedule excerpts ({{ $result['matched_chunks']->count() }})</summary>
                    <div class="mt-2 space-y-2">
                        @foreach ($result['matched_chunks'] as $chunk)
                            <p class="rounded bg-gray-50 p-2 text-xs text-gray-600">{{ Str::limit($chunk->chunk_text, 240) }}</p>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @endif

    <div class="card p-5">
        <h2 class="font-medium text-gray-900 mb-3">Recent lookups</h2>
        <div class="divide-y divide-gray-100">
            @forelse ($recentLookups as $lookup)
                <div class="py-2.5 text-sm">
                    <p class="text-gray-900">{{ Str::limit($lookup->query_text, 90) }}</p>
                    <p class="text-xs text-gray-500">
                        {{ collect($lookup->suggested_codes)->pluck('code')->filter()->implode(', ') ?: 'No suggestion' }}
                        · {{ ucfirst($lookup->confidence ?? 'n/a') }} confidence · {{ $lookup->created_at->diffForHumans() }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-gray-500 py-2.5">No lookups yet.</p>
            @endforelse
        </div>
    </div>
</div>
