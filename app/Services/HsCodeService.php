<?php

namespace App\Services;

use App\Models\HsLookup;
use Illuminate\Support\Facades\DB;

class HsCodeService
{
    public function __construct(protected OllamaService $ollama)
    {
    }

    /**
     * Search the embedded tariff schedule for the top-N chunks nearest the
     * product description, then ask llama3.2:3b to turn those chunks into a
     * structured HS code suggestion. Every lookup is logged to hs_lookups.
     */
    public function suggest(string $productDescription, int $userId, int $topN = 5): array
    {
        $queryEmbedding = $this->ollama->embed($productDescription);

        $matches = $queryEmbedding
            ? $this->searchTariffChunks($queryEmbedding, $topN)
            : collect();

        $context = $matches->map(fn ($row) => "[Chunk {$row->id} — doc {$row->document_id}] {$row->chunk_text}")
            ->implode("\n\n");

        $system = <<<'SYS'
You are a customs tariff classification assistant. Given a product description and
excerpts from a country's HS tariff schedule, suggest the most likely HS code(s).
Respond ONLY with valid JSON matching this shape, no prose outside the JSON:
{
  "suggestions": [
    {"code": "5208", "heading": "Woven fabrics of cotton...", "confidence": "high"}
  ],
  "basis": "short note on which tariff schedule section/chapter this came from",
  "common_mistakes": "short note on common misclassification pitfalls for this product type"
}
Confidence must be one of: high, medium, low. Return at most 3 suggestions.
SYS;

        $prompt = "Product description: {$productDescription}\n\nTariff schedule excerpts:\n"
            .($context !== '' ? $context : '(no matching tariff schedule content was found)');

        $raw = $this->ollama->generate($prompt, $system, json: true);
        $parsed = $this->parseResponse($raw);

        $confidence = $parsed['suggestions'][0]['confidence'] ?? 'low';
        $codes = collect($parsed['suggestions'] ?? [])->pluck('code')->filter()->values()->all();

        $lookup = HsLookup::create([
            'query_text' => $productDescription,
            'suggested_codes' => $parsed['suggestions'] ?? [],
            'confidence' => in_array($confidence, ['high', 'medium', 'low']) ? $confidence : 'low',
            'source_section' => $matches->first()?->document_id
                ? 'Tariff document #'.$matches->first()->document_id
                : null,
            'result_summary' => $parsed['basis'] ?? null,
            'created_by' => $userId,
        ]);

        return [
            'lookup' => $lookup,
            'suggestions' => $parsed['suggestions'] ?? [],
            'basis' => $parsed['basis'] ?? 'No tariff schedule content was available for this query.',
            'common_mistakes' => $parsed['common_mistakes'] ?? null,
            'matched_chunks' => $matches,
            'codes' => $codes,
            'ollama_available' => $raw !== null,
        ];
    }

    protected function searchTariffChunks(array $embedding, int $topN)
    {
        $vectorLiteral = '['.implode(',', $embedding).']';

        return collect(DB::select(
            'select tc.id, tc.document_id, tc.chunk_text,
                    1 - (tc.embedding <=> ?::vector) as similarity
             from tariff_chunks tc
             where tc.embedding is not null
             order by tc.embedding <=> ?::vector
             limit ?',
            [$vectorLiteral, $vectorLiteral, $topN]
        ));
    }

    protected function parseResponse(?string $raw): array
    {
        if (! $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Ollama occasionally wraps JSON in prose/code fences despite format=json.
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $decoded = json_decode($m[0], true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
