<?php

namespace App\Services;

use App\Models\DocumentRequirement;
use App\Models\Shipment;

class ShipmentChecklistService
{
    /**
     * Generate the dynamic document checklist for a shipment based on its
     * origin/destination/mode, from the seeded (or manually maintained)
     * document_requirements table. Idempotent: skips document types the
     * shipment already has a row for.
     */
    public function generate(Shipment $shipment): int
    {
        $requirements = DocumentRequirement::forRoute(
            $shipment->origin_country,
            $shipment->destination_country,
            $shipment->mode
        );

        $existingTypes = $shipment->documents()->pluck('document_type')->all();
        $created = 0;

        foreach ($requirements as $requirement) {
            if (in_array($requirement->document_type, $existingTypes, true)) {
                continue;
            }

            $shipment->documents()->create([
                'document_type' => $requirement->document_type,
                'required' => $requirement->required,
            ]);
            $created++;
        }

        return $created;
    }

    public function copyFrom(Shipment $source, Shipment $target): int
    {
        $existingTypes = $target->documents()->pluck('document_type')->all();
        $created = 0;

        foreach ($source->documents as $doc) {
            if (in_array($doc->document_type, $existingTypes, true)) {
                continue;
            }

            $target->documents()->create([
                'document_type' => $doc->document_type,
                'required' => $doc->required,
            ]);
            $created++;
        }

        return $created;
    }
}
