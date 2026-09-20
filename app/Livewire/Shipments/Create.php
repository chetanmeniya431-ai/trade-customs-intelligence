<?php

namespace App\Livewire\Shipments;

use App\Models\Client;
use App\Models\Shipment;
use App\Services\ShipmentChecklistService;
use App\Services\SimilarShipmentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public string $reference = '';

    public string $direction = 'import';

    public string $origin_country = '';

    public string $destination_country = '';

    public string $product_description = '';

    public string $hs_code = '';

    public string $declared_value = '';

    public string $declared_currency = 'USD';

    public string $mode = 'sea';

    public string $incoterms = 'FOB';

    public string $expected_date = '';

    public string $filing_deadline = '';

    public ?int $client_id = null;

    public ?array $similarMatch = null;

    public function updatedProductDescription(): void
    {
        $this->similarMatch = null;

        if (strlen($this->product_description) < 12) {
            return;
        }

        $probe = new Shipment([
            'product_description' => $this->product_description,
            'origin_country' => $this->origin_country ?: 'N/A',
            'destination_country' => $this->destination_country ?: 'N/A',
        ]);

        $embedding = app(\App\Services\OllamaService::class)->embed($this->product_description);
        if (! $embedding) {
            return;
        }

        $vectorLiteral = '['.implode(',', $embedding).']';
        $rows = \Illuminate\Support\Facades\DB::select(
            'select s.id, s.reference, s.product_description, s.origin_country, s.destination_country,
                    s.declared_value, s.declared_currency, s.status, s.updated_at,
                    1 - (se.embedding <=> ?::vector) as similarity
             from shipment_embeddings se
             join shipments s on s.id = se.shipment_id
             order by se.embedding <=> ?::vector
             limit 1',
            [$vectorLiteral, $vectorLiteral]
        );

        if (! empty($rows) && $rows[0]->similarity >= SimilarShipmentService::SIMILARITY_THRESHOLD) {
            $this->similarMatch = (array) $rows[0];
        }
    }

    public function copyFromSimilar(int $shipmentId): void
    {
        $source = Shipment::find($shipmentId);
        if (! $source) {
            return;
        }

        $this->hs_code = $source->hs_code ?? '';
        $this->origin_country = $this->origin_country ?: $source->origin_country;
        $this->destination_country = $this->destination_country ?: $source->destination_country;
        session()->flash('copy_source_id', $source->id);
    }

    public function save(ShipmentChecklistService $checklistService)
    {
        $validated = $this->validate([
            'reference' => 'nullable|string|max:50|unique:shipments,reference',
            'direction' => 'required|in:import,export',
            'origin_country' => 'required|string|max:100',
            'destination_country' => 'required|string|max:100',
            'product_description' => 'required|string|max:2000',
            'hs_code' => 'nullable|string|max:20',
            'declared_value' => 'required|numeric|min:0',
            'declared_currency' => 'required|string|size:3',
            'mode' => 'required|in:air,sea,road,rail',
            'incoterms' => 'nullable|string|max:10',
            'expected_date' => 'nullable|date',
            'filing_deadline' => 'nullable|date',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $shipment = Shipment::create([
            ...$validated,
            'reference' => $validated['reference'] ?: null,
            'expected_date' => $validated['expected_date'] ?: null,
            'filing_deadline' => $validated['filing_deadline'] ?: null,
            'incoterms' => $validated['incoterms'] ?: null,
            'hs_code' => $validated['hs_code'] ?: null,
            'client_id' => $validated['client_id'] ?: null,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        $created = $checklistService->generate($shipment);

        if ($created === 0) {
            $shipment->documents()->create([
                'document_type' => 'Commercial invoice',
                'required' => true,
            ]);
        }

        session()->flash('status', "Shipment {$shipment->reference} created with a {$shipment->documents()->count()}-item document checklist.");

        return redirect()->route('shipments.show', $shipment);
    }

    public function render()
    {
        return view('livewire.shipments.create', [
            'clients' => Client::orderBy('name')->get(),
        ])->title('New shipment');
    }
}
