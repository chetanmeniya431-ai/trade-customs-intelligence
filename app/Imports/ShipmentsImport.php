<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Shipment;
use App\Services\ShipmentChecklistService;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Bulk shipment import from CSV/XLSX. Expected columns (header row required):
 * reference, direction, origin_country, destination_country, product_description,
 * hs_code, declared_value, declared_currency, mode, incoterms, expected_date,
 * filing_deadline, client_name
 */
class ShipmentsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    public int $created = 0;

    public array $errors = [];

    public function __construct(protected int $userId)
    {
    }

    public function collection(\Illuminate\Support\Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            try {
                $clientId = null;
                if (! empty($row['client_name'])) {
                    $client = Client::firstOrCreate(['name' => trim($row['client_name'])]);
                    $clientId = $client->id;
                }

                $shipment = Shipment::create([
                    'reference' => ! empty($row['reference']) ? trim($row['reference']) : null,
                    'direction' => strtolower(trim($row['direction'])),
                    'origin_country' => trim($row['origin_country']),
                    'destination_country' => trim($row['destination_country']),
                    'product_description' => trim($row['product_description']),
                    'hs_code' => $row['hs_code'] ?: null,
                    'declared_value' => (float) $row['declared_value'],
                    'declared_currency' => strtoupper($row['declared_currency'] ?: 'USD'),
                    'mode' => strtolower(trim($row['mode'])),
                    'incoterms' => $row['incoterms'] ?: null,
                    'expected_date' => $row['expected_date'] ?: null,
                    'filing_deadline' => $row['filing_deadline'] ?: null,
                    'client_id' => $clientId,
                    'status' => 'draft',
                    'created_by' => $this->userId,
                ]);

                app(ShipmentChecklistService::class)->generate($shipment);
                $this->created++;
            } catch (\Throwable $e) {
                $this->errors[] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }
    }

    public function rules(): array
    {
        return [
            'direction' => 'required|in:import,export,Import,Export',
            'origin_country' => 'required|string',
            'destination_country' => 'required|string',
            'product_description' => 'required|string',
            'declared_value' => 'required|numeric',
            'mode' => 'required|in:air,sea,road,rail,Air,Sea,Road,Rail',
        ];
    }
}
