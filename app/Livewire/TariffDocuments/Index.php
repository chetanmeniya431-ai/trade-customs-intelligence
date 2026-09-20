<?php

namespace App\Livewire\TariffDocuments;

use App\Models\TariffDocument;
use App\Services\TariffIngestService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public $file;

    public string $name = '';

    public string $country_code = 'IN';

    public string $document_type = 'tariff_schedule';

    public bool $processing = false;

    public function updatedFile(): void
    {
        if ($this->file && ! $this->name) {
            $this->name = pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME);
        }
    }

    public function upload(TariffIngestService $ingestService): void
    {
        $this->validate([
            'file' => 'required|file|mimes:pdf,txt|max:20480',
            'name' => 'required|string|max:150',
            'country_code' => 'nullable|string|max:10',
            'document_type' => 'required|in:tariff_schedule,regulation,trade_agreement',
        ]);

        $path = $ingestService->storeUploadedFile($this->file);

        $document = TariffDocument::create([
            'name' => $this->name,
            'country_code' => $this->country_code ?: null,
            'document_type' => $this->document_type,
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
        ]);

        $this->processing = true;
        $this->reset('file', 'name');

        // Run synchronously: this is a local Ollama call over a modest PDF,
        // and the user needs to see it land in the list as "embedded" now,
        // not poll a queue. See TariffIngestService for the pipeline.
        $ingestService->ingest($document->fresh());

        $this->processing = false;
        session()->flash('status', "\"{$document->name}\" uploaded and processed into {$document->fresh()->chunk_count} searchable chunks.");
    }

    public function reprocess(int $documentId, TariffIngestService $ingestService): void
    {
        $document = TariffDocument::findOrFail($documentId);
        $this->processing = true;
        $ingestService->ingest($document);
        $this->processing = false;
        session()->flash('status', "\"{$document->name}\" re-processed.");
    }

    public function delete(int $documentId): void
    {
        $document = TariffDocument::findOrFail($documentId);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }

    public function render()
    {
        return view('livewire.tariff-documents.index', [
            'documents' => TariffDocument::withCount('chunks')->latest('created_at')->get(),
        ])->title('Tariff Documents');
    }
}
