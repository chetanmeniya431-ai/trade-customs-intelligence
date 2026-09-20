<?php

namespace App\Services;

use App\Models\TariffDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * The single ingestion pipeline for tariff schedules, regulations, and trade
 * agreements. Used both by the seeder (synthetic demo documents) and by the
 * manual "Tariff Documents" upload screen — there is no separate stub path
 * for user-uploaded files. Extract text -> chunk -> embed each chunk via
 * Ollama -> store as vector(768) rows in tariff_chunks.
 */
class TariffIngestService
{
    protected const CHUNK_SIZE = 900;

    protected const CHUNK_OVERLAP = 150;

    public function __construct(protected OllamaService $ollama)
    {
    }

    /**
     * Ingest an already-created TariffDocument record whose file_path points
     * at a stored PDF (or plain text) on the 'public' disk under tariffs/.
     */
    public function ingest(TariffDocument $document): void
    {
        $absolutePath = storage_path('app/public/'.$document->file_path);

        $text = $this->extractText($absolutePath);

        if (trim($text) === '') {
            Log::warning("TariffIngestService: no extractable text for document {$document->id}");

            return;
        }

        $chunks = $this->chunkText($text);

        $document->chunks()->delete();

        $embeddings = $this->ollama->embedBatch($chunks) ?? [];

        foreach ($chunks as $index => $chunkText) {
            $document->chunks()->create([
                'chunk_index' => $index,
                'chunk_text' => $chunkText,
                'embedding' => $embeddings[$index] ?? $this->ollama->embed($chunkText),
            ]);
        }

        $document->update([
            'chunk_count' => count($chunks),
            'embedded_at' => now(),
        ]);
    }

    public function extractText(string $absolutePath): string
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        if ($extension === 'txt') {
            return (string) file_get_contents($absolutePath);
        }

        try {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($absolutePath);

            return $pdf->getText();
        } catch (\Throwable $e) {
            Log::error('PDF parse failed: '.$e->getMessage());

            return '';
        }
    }

    /**
     * Sliding-window word chunking with overlap, so a heading/table row
     * split across a chunk boundary still has context on both sides.
     *
     * @return string[]
     */
    public function chunkText(string $text): array
    {
        $normalized = preg_replace('/\s+/', ' ', $text) ?? $text;
        $words = preg_split('/\s+/', trim($normalized));
        $words = array_values(array_filter($words, fn ($w) => $w !== ''));

        if (empty($words)) {
            return [];
        }

        $chunks = [];
        $step = self::CHUNK_SIZE - self::CHUNK_OVERLAP;
        $totalWords = count($words);

        for ($start = 0; $start < $totalWords; $start += $step) {
            $slice = array_slice($words, $start, self::CHUNK_SIZE);
            $chunks[] = implode(' ', $slice);

            if ($start + self::CHUNK_SIZE >= $totalWords) {
                break;
            }
        }

        return $chunks;
    }

    public function storeUploadedFile(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('tariffs', $filename, 'public');

        return 'tariffs/'.$filename;
    }
}
