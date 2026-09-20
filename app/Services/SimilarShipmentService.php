<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentEmbedding;
use Illuminate\Support\Facades\DB;

class SimilarShipmentService
{
    public const SIMILARITY_THRESHOLD = 0.85;

    public function __construct(protected OllamaService $ollama)
    {
    }

    public function embedAndStore(Shipment $shipment): ?ShipmentEmbedding
    {
        $vector = $this->ollama->embed($shipment->product_description);

        if (! $vector) {
            return null;
        }

        return ShipmentEmbedding::updateOrCreate(
            ['shipment_id' => $shipment->id],
            ['embedding' => $vector]
        );
    }

    /**
     * Returns the most similar past shipment (excluding itself), or null if
     * none clear the 0.85 cosine-similarity bar.
     */
    public function findSimilar(Shipment $shipment): ?array
    {
        $embedding = $shipment->embedding ?? $this->embedAndStore($shipment);

        if (! $embedding || ! $embedding->embedding) {
            return null;
        }

        $vectorLiteral = '['.implode(',', $embedding->embedding).']';

        $rows = DB::select(
            'select s.*, 1 - (se.embedding <=> ?::vector) as similarity
             from shipment_embeddings se
             join shipments s on s.id = se.shipment_id
             where se.shipment_id != ?
             order by se.embedding <=> ?::vector
             limit 1',
            [$vectorLiteral, $shipment->id, $vectorLiteral]
        );

        if (empty($rows) || $rows[0]->similarity < self::SIMILARITY_THRESHOLD) {
            return null;
        }

        $row = $rows[0];
        $match = Shipment::find($row->id);

        return [
            'shipment' => $match,
            'similarity' => round((float) $row->similarity, 4),
        ];
    }
}
