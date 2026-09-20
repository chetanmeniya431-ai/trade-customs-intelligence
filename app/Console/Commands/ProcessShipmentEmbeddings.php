<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Services\SimilarShipmentService;
use Illuminate\Console\Command;

class ProcessShipmentEmbeddings extends Command
{
    protected $signature = 'embeddings:process-shipments';

    protected $description = 'Embed the product description of any shipment missing a shipment_embeddings row.';

    public function handle(SimilarShipmentService $service): int
    {
        $pending = Shipment::whereDoesntHave('embedding')
            ->whereNotNull('product_description')
            ->get();

        $processed = 0;

        foreach ($pending as $shipment) {
            if ($service->embedAndStore($shipment)) {
                $processed++;
            }
        }

        $this->info("Embedded {$processed} of {$pending->count()} pending shipment(s).");

        return self::SUCCESS;
    }
}
