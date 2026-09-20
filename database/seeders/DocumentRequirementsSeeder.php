<?php

namespace Database\Seeders;

use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

class DocumentRequirementsSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            // China -> India (sea): 7 document types
            ['China', 'India', 'sea', [
                ['Commercial invoice', true, null],
                ['Packing list', true, null],
                ['Bill of lading', true, null],
                ['Certificate of origin (general)', true, null],
                ['Customs declaration (Bill of Entry)', true, null],
                ['Insurance certificate', false, null],
                ['Import license', false, 'regulated goods'],
            ]],
            // Germany -> India (air): 6 document types
            ['Germany', 'India', 'air', [
                ['Commercial invoice', true, null],
                ['Packing list', true, null],
                ['Air waybill', true, null],
                ['Certificate of origin (EUR.1/general)', true, null],
                ['Customs declaration (Bill of Entry)', true, null],
                ['Insurance certificate', false, null],
            ]],
            // India -> UAE (sea): 6 document types
            ['India', 'UAE', 'sea', [
                ['Commercial invoice', true, null],
                ['Packing list', true, null],
                ['Bill of lading', true, null],
                ['Certificate of origin (UAE)', true, null],
                ['Customs declaration (Shipping Bill)', true, null],
                ['Insurance certificate', false, null],
            ]],
            // US -> India (air): 7 document types
            ['US', 'India', 'air', [
                ['Commercial invoice', true, null],
                ['Packing list', true, null],
                ['Air waybill', true, null],
                ['Certificate of origin (USFTA/general)', true, null],
                ['Customs declaration (CBP Form 7501 reference)', true, null],
                ['Insurance certificate', false, null],
                ['Phytosanitary certificate', false, 'food/agri product'],
            ]],
            // Taiwan -> India (sea): 6 document types
            ['Taiwan', 'India', 'sea', [
                ['Commercial invoice', true, null],
                ['Packing list', true, null],
                ['Bill of lading', true, null],
                ['Certificate of origin (general)', true, null],
                ['Customs declaration (Bill of Entry)', true, null],
                ['Insurance certificate', false, null],
            ]],
        ];

        foreach ($routes as [$origin, $destination, $mode, $documents]) {
            foreach ($documents as [$type, $required, $conditionalOn]) {
                DocumentRequirement::firstOrCreate([
                    'origin_country' => $origin,
                    'destination_country' => $destination,
                    'mode' => $mode,
                    'document_type' => $type,
                ], [
                    'required' => $required,
                    'conditional_on' => $conditionalOn,
                ]);
            }
        }
    }
}
