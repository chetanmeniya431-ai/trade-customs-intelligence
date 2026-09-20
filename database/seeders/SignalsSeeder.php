<?php

namespace Database\Seeders;

use App\Models\Signal;
use Illuminate\Database\Seeder;

class SignalsSeeder extends Seeder
{
    public function run(): void
    {
        $signals = [
            ['name' => 'Missing Required Document', 'condition_key' => 'missing_required_document', 'severity' => 'critical'],
            ['name' => 'Customs Filing Deadline — 48h', 'condition_key' => 'customs_filing_deadline_48h', 'severity' => 'high'],
            ['name' => 'Customs Filing Deadline — 24h', 'condition_key' => 'customs_filing_deadline_24h', 'severity' => 'critical'],
            ['name' => 'HS Code Mismatch Detected', 'condition_key' => 'hs_code_mismatch_detected', 'severity' => 'high'],
            ['name' => 'Value Mismatch Detected', 'condition_key' => 'value_mismatch_detected', 'severity' => 'high'],
            ['name' => 'Filing Deadline Passed — Not Filed', 'condition_key' => 'filing_deadline_passed_not_filed', 'severity' => 'critical'],
            ['name' => 'Expired Document Uploaded', 'condition_key' => 'expired_document_uploaded', 'severity' => 'medium'],
            ['name' => 'Repeat HS Code Warning — Same Client', 'condition_key' => 'repeat_hs_code_warning_same_client', 'severity' => 'medium'],
            ['name' => 'High Value Shipment — Manual Review', 'condition_key' => 'high_value_shipment_manual_review', 'severity' => 'high'],
            ['name' => 'Missing Certificate of Origin', 'condition_key' => 'missing_certificate_of_origin', 'severity' => 'high'],
        ];

        foreach ($signals as $signal) {
            Signal::firstOrCreate(
                ['condition_key' => $signal['condition_key']],
                ['name' => $signal['name'], 'severity' => $signal['severity'], 'active' => true]
            );
        }
    }
}
