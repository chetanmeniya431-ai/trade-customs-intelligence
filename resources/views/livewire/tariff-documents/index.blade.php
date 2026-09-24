<div class="space-y-6 max-w-4xl">
    <h1 class="text-xl font-semibold text-gray-900">Tariff Documents</h1>
    <p class="text-sm text-gray-500 -mt-4">
        Upload tariff schedules, regulations, and trade agreements as PDFs. Each upload is parsed, chunked, and
        embedded with <code>nomic-embed-text</code> immediately — the same pipeline that powers the HS Code Finder
        and the HS-mismatch check on document upload. This is a live import, not just a seeding step.
    </p>

    @if (session('status'))
        <div class="rounded-md bg-teal-50 border border-teal-200 p-3 text-sm text-teal-800">{{ session('status') }}</div>
    @endif

    <div class="card p-6 space-y-4">
        <h2 class="font-medium text-gray-900">Upload a document</h2>
        <form wire:submit="upload" class="space-y-4">
            <div>
                <label class="form-label">PDF file</label>
                <input type="file" wire:model="file" accept="application/pdf,.txt" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-teal-700 hover:file:bg-teal-100">
                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div wire:loading wire:target="file" class="mt-1 text-xs text-gray-500">Uploading file…</div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="form-label">Document name</label>
                    <input wire:model="name" type="text" class="form-input" placeholder="India Customs Tariff — Chapters 50-63">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Country code</label>
                    <input wire:model="country_code" type="text" class="form-input" placeholder="IN">
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <select wire:model="document_type" class="form-select">
                        <option value="tariff_schedule">Tariff schedule</option>
                        <option value="regulation">Regulation</option>
                        <option value="trade_agreement">Trade agreement</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="upload">
                <span wire:loading.remove wire:target="upload">Upload &amp; process</span>
                <span wire:loading wire:target="upload">Parsing, chunking &amp; embedding…</span>
            </button>
        </form>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Country</th>
                    <th class="px-4 py-3">Chunks</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($documents as $doc)
                    <tr wire:key="tariff-doc-{{ $doc->id }}">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $doc->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ ucwords(str_replace('_',' ',$doc->document_type)) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $doc->country_code ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $doc->chunks_count }}</td>
                        <td class="px-4 py-3">
                            @if ($doc->isEmbedded())
                                <span class="badge badge-green">Embedded</span>
                            @else
                                <span class="badge badge-yellow">Not processed</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('tariff-documents.download', $doc) }}" class="text-teal-600 hover:text-teal-700 text-xs font-medium">Download</a>
                            <button wire:click="reprocess({{ $doc->id }})" wire:loading.attr="disabled" class="text-gray-500 hover:text-gray-700 text-xs font-medium">Re-process</button>
                            <button wire:click="delete({{ $doc->id }})" wire:loading.attr="disabled" wire:target="delete({{ $doc->id }})" wire:confirm="Delete this document and its embedded chunks?" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No tariff documents uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</div>
