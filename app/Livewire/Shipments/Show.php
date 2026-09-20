<?php

namespace App\Livewire\Shipments;

use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Services\DocumentCheckService;
use App\Services\SimilarShipmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithFileUploads;

    public Shipment $shipment;

    public ?int $activeUploadDocId = null;

    public $uploadFile;

    public array $resolutionNotes = [];

    public function mount(Shipment $shipment): void
    {
        $user = Auth::user();

        if ($user->isClientUser() && $shipment->client_id !== $user->client_id) {
            abort(403);
        }

        $this->shipment = $shipment;
    }

    public function updatedUploadFile(): void
    {
        if (! $this->activeUploadDocId) {
            return;
        }

        $this->validate([
            'uploadFile' => 'required|file|mimes:pdf|max:10240',
        ]);

        $doc = ShipmentDocument::where('shipment_id', $this->shipment->id)->findOrFail($this->activeUploadDocId);

        $filename = Str::uuid().'.pdf';
        $path = $this->uploadFile->storeAs('documents/'.$this->shipment->id, $filename, 'public');

        $doc->update([
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
            'uploaded_at' => now(),
            'verified' => false,
        ]);

        $absolutePath = Storage::disk('public')->path($path);

        app(DocumentCheckService::class)->runAllChecks($this->shipment, $doc, $absolutePath);

        $this->shipment->recomputeStatus();

        $this->reset('uploadFile', 'activeUploadDocId');
        session()->flash('status', "\"{$doc->document_type}\" uploaded and checked.");
    }

    public function selectUploadTarget(int $docId): void
    {
        $this->activeUploadDocId = $docId;
    }

    public function verifyDocument(int $docId): void
    {
        $doc = ShipmentDocument::where('shipment_id', $this->shipment->id)->findOrFail($docId);
        $doc->update([
            'verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);
    }

    public function setExpiryDate(int $docId, string $date): void
    {
        $doc = ShipmentDocument::where('shipment_id', $this->shipment->id)->findOrFail($docId);
        $doc->update(['expiry_date' => $date ?: null]);
    }

    public function resolveFinding(int $findingId): void
    {
        $finding = $this->shipment->findings()->findOrFail($findingId);
        $finding->update([
            'status' => 'resolved',
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
            'resolution_note' => $this->resolutionNotes[$findingId] ?? null,
        ]);
        $this->shipment->recomputeStatus();
    }

    public function confirmFinding(int $findingId): void
    {
        $finding = $this->shipment->findings()->findOrFail($findingId);
        $finding->update(['status' => 'confirmed']);
        $this->shipment->recomputeStatus();
    }

    public function updateStatus(string $status): void
    {
        $this->shipment->update(['status' => $status]);
    }

    public function render()
    {
        $this->shipment->load(['documents.findings', 'documents.uploader', 'client', 'findings']);

        $similar = app(SimilarShipmentService::class)->findSimilar($this->shipment);

        return view('livewire.shipments.show', [
            'similar' => $similar,
        ])->title($this->shipment->reference);
    }
}
