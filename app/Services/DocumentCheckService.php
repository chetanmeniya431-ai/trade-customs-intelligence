<?php

namespace App\Services;

use App\Models\DocumentFinding;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Runs the three automated checks required whenever a document is uploaded
 * for a shipment: completeness, HS code consistency, and value consistency.
 * Findings are persisted to document_findings; callers don't need to know
 * which checks fired.
 */
class DocumentCheckService
{
    public function __construct(protected OllamaService $ollama)
    {
    }

    public function runAllChecks(Shipment $shipment, ?ShipmentDocument $justUploaded = null, ?string $uploadedAbsolutePath = null): void
    {
        $this->checkCompleteness($shipment);

        if ($justUploaded && $this->isInvoiceDocument($justUploaded)) {
            $this->checkHsCodeConsistency($shipment, $justUploaded);

            $invoiceTotal = null;
            if ($uploadedAbsolutePath) {
                $invoiceTotal = $this->extractInvoiceData($uploadedAbsolutePath)['total'];
            }

            $this->checkValueConsistency($shipment, $justUploaded, $invoiceTotal);
        }
    }

    public function checkCompleteness(Shipment $shipment): void
    {
        $missing = $shipment->documents()
            ->where('required', true)
            ->whereNull('file_path')
            ->get();

        // Clear stale "missing" findings for docs that are now uploaded.
        $shipment->findings()
            ->where('finding_type', 'missing_doc')
            ->where('status', 'open')
            ->get()
            ->each(function (DocumentFinding $finding) use ($missing) {
                $stillMissing = $missing->contains('id', $finding->document_id);
                if (! $stillMissing) {
                    $finding->update(['status' => 'resolved', 'resolved_at' => now()]);
                }
            });

        foreach ($missing as $doc) {
            $exists = $shipment->findings()
                ->where('finding_type', 'missing_doc')
                ->where('document_id', $doc->id)
                ->where('status', 'open')
                ->exists();

            if (! $exists) {
                DocumentFinding::create([
                    'shipment_id' => $shipment->id,
                    'document_id' => $doc->id,
                    'finding_type' => 'missing_doc',
                    'description' => "Required document \"{$doc->document_type}\" has not been uploaded.",
                    'severity' => 'high',
                    'status' => 'open',
                ]);
            }
        }
    }

    public function checkHsCodeConsistency(Shipment $shipment, ShipmentDocument $invoiceDoc): void
    {
        if (empty($shipment->hs_code) || empty($shipment->product_description)) {
            return;
        }

        $queryEmbedding = $this->ollama->embed($shipment->product_description);
        $context = '';

        if ($queryEmbedding) {
            $vectorLiteral = '['.implode(',', $queryEmbedding).']';
            $rows = DB::select(
                'select chunk_text from tariff_chunks
                 where embedding is not null
                 order by embedding <=> ?::vector
                 limit 5',
                [$vectorLiteral]
            );
            $context = collect($rows)->pluck('chunk_text')->implode("\n\n");
        }

        $system = <<<'SYS'
You are a customs compliance assistant checking whether a shipment's declared HS code
matches its product description, using excerpts from the tariff schedule as reference.
Respond ONLY with JSON: {"mismatch": true|false, "explanation": "...", "suggested_code": "..." or null}
SYS;

        $prompt = "Declared HS code: {$shipment->hs_code}\n"
            ."Product description on invoice: {$shipment->product_description}\n\n"
            ."Tariff schedule excerpts:\n".($context !== '' ? $context : '(none available)');

        $raw = $this->ollama->generate($prompt, $system, json: true);
        $result = $this->decodeJson($raw);

        if (($result['mismatch'] ?? false) === true) {
            $exists = $shipment->findings()
                ->where('finding_type', 'hs_mismatch')
                ->where('document_id', $invoiceDoc->id)
                ->where('status', 'open')
                ->exists();

            if (! $exists) {
                DocumentFinding::create([
                    'shipment_id' => $shipment->id,
                    'document_id' => $invoiceDoc->id,
                    'finding_type' => 'hs_mismatch',
                    'description' => $result['explanation']
                        ?? "The product description does not clearly match HS code {$shipment->hs_code}.",
                    'suggested_value' => $result['suggested_code'] ?? null,
                    'severity' => 'high',
                    'status' => 'open',
                ]);
            }
        }
    }

    public function checkValueConsistency(Shipment $shipment, ShipmentDocument $invoiceDoc, ?float $invoiceTotal = null): void
    {
        if ($invoiceTotal === null) {
            return;
        }

        $declared = (float) $shipment->declared_value;
        if ($declared <= 0) {
            return;
        }

        $diff = abs($invoiceTotal - $declared);
        $percent = ($diff / $declared) * 100;
        $threshold = (float) config('services.compliance.value_mismatch_percent', 5);

        if ($percent >= $threshold) {
            $exists = $shipment->findings()
                ->where('finding_type', 'value_mismatch')
                ->where('document_id', $invoiceDoc->id)
                ->where('status', 'open')
                ->exists();

            if (! $exists) {
                $declaredFmt = number_format($declared, 2);
                $invoiceFmt = number_format($invoiceTotal, 2);
                $diffFmt = number_format($diff, 2);
                $percentFmt = number_format($percent, 1);

                DocumentFinding::create([
                    'shipment_id' => $shipment->id,
                    'document_id' => $invoiceDoc->id,
                    'finding_type' => 'value_mismatch',
                    'description' => "Declared value: \${$declaredFmt}. Invoice total: \${$invoiceFmt}. "
                        ."Difference: \${$diffFmt} ({$percentFmt}%). This may cause a customs hold for under-declaration.",
                    'suggested_value' => (string) $invoiceTotal,
                    'severity' => 'high',
                    'status' => 'open',
                ]);
            }
        }
    }

    protected function isInvoiceDocument(ShipmentDocument $doc): bool
    {
        return str_contains(strtolower($doc->document_type), 'invoice');
    }

    /**
     * Extracts the invoice's product description and total value from an
     * uploaded PDF so checkHsCodeConsistency / checkValueConsistency have
     * real numbers to compare against the shipment record, instead of
     * trusting the record alone.
     */
    public function extractInvoiceData(string $absolutePath): array
    {
        $text = $this->extractPdfText($absolutePath);

        if (trim($text) === '') {
            return ['total' => null, 'description' => null];
        }

        $system = <<<'SYS'
Extract data from this commercial invoice text. Respond ONLY with JSON:
{"total": <number or null>, "currency": "<3-letter code or null>", "description": "<short product description or null>"}
SYS;

        $raw = $this->ollama->generate(substr($text, 0, 6000), $system, json: true);
        $decoded = $this->decodeJson($raw);

        if (isset($decoded['total']) && is_numeric($decoded['total'])) {
            return [
                'total' => (float) $decoded['total'],
                'description' => $decoded['description'] ?? null,
            ];
        }

        // Fallback: regex for a currency-looking total when Ollama is unavailable.
        if (preg_match('/total[^0-9]{0,20}([\d,]+\.\d{2})/i', $text, $m)) {
            return ['total' => (float) str_replace(',', '', $m[1]), 'description' => null];
        }

        return ['total' => null, 'description' => null];
    }

    protected function extractPdfText(string $absolutePath): string
    {
        try {
            $parser = new PdfParser;

            return $parser->parseFile($absolutePath)->getText();
        } catch (\Throwable $e) {
            return '';
        }
    }

    protected function decodeJson(?string $raw): array
    {
        if (! $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
