<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\DocumentFinding;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\User;
use App\Services\ShipmentChecklistService;
use App\Services\SimilarShipmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 20 synthetic shipments across five demo states (see CLAUDE.md synthetic
 * data plan): 8 cleared, 4 ready-to-file, 3 partially documented, 3 with AI
 * findings (2 HS mismatches, 1 value mismatch), 2 with near-term deadlines.
 * Document checklists are generated through the same ShipmentChecklistService
 * the UI uses, from the document_requirements seeded by DocumentRequirementsSeeder.
 */
class ShipmentsSeeder extends Seeder
{
    protected string $genericPdfBytes;

    public function run(): void
    {
        $checklistService = app(ShipmentChecklistService::class);
        $coordinator = User::where('email', 'coordinator@tradecustoms.local')->first();
        $this->genericPdfBytes = Pdf::loadHTML('<html><body style="font-family: DejaVu Sans;">Synthetic demo document. Not a real customs record.</body></html>')->output();

        $clients = Client::pluck('id', 'name');
        $routes = [
            ['China', 'India', 'sea'],
            ['Germany', 'India', 'air'],
            ['India', 'UAE', 'sea'],
            ['US', 'India', 'air'],
            ['Taiwan', 'India', 'sea'],
        ];

        $products = [
            ['Cotton fabric, woven, 100% cotton, plain weave', '520852', $clients['Arjun Textiles Pvt Ltd']],
            ['Polyester filament woven fabric for lining', '540761', $clients['Arjun Textiles Pvt Ltd']],
            ['Electronic speed controllers (ESC) for brushless motors, 30A', '854370', $clients['Nexgen Electronics']],
            ['DC motor assembly, 24V, for industrial fans', '850131', $clients['Nexgen Electronics']],
            ['Organic turmeric powder, 25kg bags', '091030', $clients['Pure Harvest Foods']],
            ['Basmati rice, 20kg bags, vacuum packed', '100630', $clients['Pure Harvest Foods']],
            ['Disposable surgical gloves, nitrile, medical grade', '401511', $clients['Medica Devices India']],
            ['Digital blood pressure monitors, class IIa', '901819', $clients['Medica Devices India']],
            ['Industrial cleaning chemicals, non-hazardous', '340220', $clients['Sunbeam Chemicals']],
            ['Woven polypropylene sacks, 50kg capacity', '630533', $clients['Sunbeam Chemicals']],
        ];

        if (Shipment::count() >= 20) {
            return;
        }

        $shipmentIndex = 0;
        $mk = function () use (&$shipmentIndex, $products, $routes) {
            $product = $products[$shipmentIndex % count($products)];
            $route = $routes[$shipmentIndex % count($routes)];
            $shipmentIndex++;

            return [$product, $route];
        };

        // Group A: 8 cleared shipments, complete & verified, no issues.
        for ($i = 0; $i < 8; $i++) {
            [$product, $route] = $mk();
            $shipment = $this->makeShipment($product, $route, 'cleared', now()->subDays(30 + $i), $coordinator);
            $checklistService->generate($shipment);
            $this->uploadAll($shipment, verified: true);
        }

        // Group B: 4 ready-to-file, all docs uploaded, unverified, no issues.
        for ($i = 0; $i < 4; $i++) {
            [$product, $route] = $mk();
            $shipment = $this->makeShipment($product, $route, 'ready', now()->addDays(12 + $i), $coordinator);
            $checklistService->generate($shipment);
            $this->uploadAll($shipment, verified: false);
        }

        // Group C: 3 partially documented — missing 1-2 required docs.
        for ($i = 0; $i < 3; $i++) {
            [$product, $route] = $mk();
            $shipment = $this->makeShipment($product, $route, 'documents_pending', now()->addDays(15 + $i), $coordinator);
            $checklistService->generate($shipment);
            $required = $shipment->documents()->where('required', true)->get();
            $toSkip = $required->random(min(2, max(1, intdiv($required->count(), 3))))->pluck('id');
            foreach ($required as $doc) {
                if (! $toSkip->contains($doc->id)) {
                    $this->uploadDoc($doc, verified: false);
                }
            }
        }

        // Group D: 3 with AI findings — 2 HS mismatches, 1 value mismatch. Fully documented otherwise.
        $hsMismatchProduct = ['Cotton fabric, woven, 100% cotton, plain weave', '540761', $clients['Arjun Textiles Pvt Ltd']]; // wrong code on purpose
        $shipment1 = $this->makeShipment($hsMismatchProduct, $routes[0], 'documents_pending', now()->addDays(9), $coordinator);
        $checklistService->generate($shipment1);
        $this->uploadAll($shipment1, verified: false);
        $this->addFinding($shipment1, 'hs_mismatch', 'high',
            "The product description \"cotton fabric woven\" does not match the HS code 5407 (woven fabrics of synthetic filament yarn). Suggested code: 5208.",
            '5208');

        $hsMismatchProduct2 = ['Woven polypropylene sacks, 50kg capacity', '390110', $clients['Sunbeam Chemicals']]; // wrong: plastics code instead of 6305
        $shipment2 = $this->makeShipment($hsMismatchProduct2, $routes[4], 'documents_pending', now()->addDays(11), $coordinator);
        $checklistService->generate($shipment2);
        $this->uploadAll($shipment2, verified: false);
        $this->addFinding($shipment2, 'hs_mismatch', 'high',
            "The product description \"woven polypropylene sacks, 50kg capacity\" does not match the HS code 3901 (polymers of ethylene). Suggested code: 6305.33.",
            '6305.33');

        $valueMismatchProduct = ['Digital blood pressure monitors, class IIa', '901819', $clients['Medica Devices India']];
        $shipment3 = $this->makeShipment($valueMismatchProduct, $routes[1], 'documents_pending', now()->addDays(13), $coordinator);
        $checklistService->generate($shipment3);
        $this->uploadAll($shipment3, verified: false);
        $shipment3->update(['declared_value' => 12500]);
        $this->addFinding($shipment3, 'value_mismatch', 'high',
            'Declared value: $12,500.00. Invoice total: $15,200.00. Difference: $2,700.00 (21.6%). This may cause a customs hold for under-declaration.',
            '15200');

        // Group E: 2 with near-term filing deadlines.
        [$product, $route] = $mk();
        $shipmentE1 = $this->makeShipment($product, $route, 'documents_pending', now()->addHours(20), $coordinator);
        $checklistService->generate($shipmentE1);
        $required = $shipmentE1->documents()->where('required', true)->get();
        foreach ($required->slice(1) as $doc) {
            $this->uploadDoc($doc, verified: false);
        } // leaves one required doc missing -> also fires Missing Required Document

        [$product, $route] = $mk();
        $shipmentE2 = $this->makeShipment($product, $route, 'documents_pending', now()->addHours(40), $coordinator);
        $checklistService->generate($shipmentE2);
        $this->uploadAll($shipmentE2, verified: false);

        // Status above is a starting point per group's intent, not necessarily final —
        // e.g. a group that gets uploadAll() with no finding ends up 100%/0-open, which
        // the app's own rule (see Shipment::recomputeStatus()) means "ready", not
        // "documents_pending". Recompute for real here rather than trusting the literal
        // just so seeded data can't drift out of sync with what the UI would compute.
        Shipment::all()->each(fn (Shipment $s) => $s->recomputeStatus());

        // Embed every shipment's product description so similar-shipment
        // detection has real vectors to search from the moment seeding finishes.
        $similarService = app(SimilarShipmentService::class);
        Shipment::all()->each(fn (Shipment $s) => $similarService->embedAndStore($s));
    }

    protected function makeShipment(array $product, array $route, string $status, \Illuminate\Support\Carbon $deadline, ?User $creator): Shipment
    {
        [$description, $hsCode, $clientId] = $product;
        [$origin, $destination, $mode] = $route;

        return Shipment::create([
            'direction' => $origin === 'India' ? 'export' : 'import',
            'origin_country' => $origin,
            'destination_country' => $destination,
            'product_description' => $description,
            'hs_code' => $hsCode,
            'declared_value' => fake()->numberBetween(4000, 60000),
            'declared_currency' => 'USD',
            'mode' => $mode,
            'incoterms' => fake()->randomElement(['FOB', 'CIF', 'EXW', 'DDP']),
            'expected_date' => $deadline->copy()->subDays(5)->toDateString(),
            'filing_deadline' => $deadline,
            'client_id' => $clientId,
            'status' => $status,
            'created_by' => $creator?->id,
        ]);
    }

    protected function uploadAll(Shipment $shipment, bool $verified): void
    {
        foreach ($shipment->documents as $doc) {
            $this->uploadDoc($doc, $verified);
        }
    }

    protected function uploadDoc(ShipmentDocument $doc, bool $verified): void
    {
        $filename = 'documents/'.$doc->shipment_id.'/'.Str::uuid().'.pdf';
        Storage::disk('public')->put($filename, $this->genericPdfBytes);

        $doc->update([
            'file_path' => $filename,
            'uploaded_by' => User::where('email', 'coordinator@tradecustoms.local')->value('id'),
            'uploaded_at' => now()->subDays(rand(1, 20)),
            'verified' => $verified,
            'verified_by' => $verified ? User::where('email', 'compliance@tradecustoms.local')->value('id') : null,
            'verified_at' => $verified ? now()->subDays(rand(0, 5)) : null,
        ]);
    }

    protected function addFinding(Shipment $shipment, string $type, string $severity, string $description, ?string $suggested): void
    {
        $invoiceDoc = $shipment->documents()->where('document_type', 'ilike', '%invoice%')->first();

        DocumentFinding::create([
            'shipment_id' => $shipment->id,
            'document_id' => $invoiceDoc?->id,
            'finding_type' => $type,
            'description' => $description,
            'suggested_value' => $suggested,
            'severity' => $severity,
            'status' => 'open',
        ]);
    }
}
