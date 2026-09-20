<?php

namespace App\Console\Commands;

use App\Models\DocumentFinding;
use App\Models\ShipmentDocument;
use Illuminate\Console\Command;

class CheckDocumentExpiry extends Command
{
    protected $signature = 'documents:check-expiry';

    protected $description = 'Flag uploaded documents whose expiry date has passed as a finding.';

    public function handle(): int
    {
        $expired = ShipmentDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->whereNotNull('file_path')
            ->get();

        $flagged = 0;

        foreach ($expired as $doc) {
            $exists = DocumentFinding::where('document_id', $doc->id)
                ->where('finding_type', 'expired_doc')
                ->where('status', 'open')
                ->exists();

            if (! $exists) {
                DocumentFinding::create([
                    'shipment_id' => $doc->shipment_id,
                    'document_id' => $doc->id,
                    'finding_type' => 'expired_doc',
                    'description' => "Document \"{$doc->document_type}\" expired on {$doc->expiry_date->toDateString()}.",
                    'severity' => 'medium',
                    'status' => 'open',
                ]);
                $flagged++;
            }
        }

        $this->info("Checked document expiry. {$flagged} new finding(s) created.");

        return self::SUCCESS;
    }
}
