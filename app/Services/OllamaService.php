<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected string $baseUrl;

    protected string $generationModel;

    protected string $embeddingModel;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ollama.base_url'), '/');
        $this->generationModel = config('services.ollama.generation_model');
        $this->embeddingModel = config('services.ollama.embedding_model');
        $this->timeout = config('services.ollama.timeout', 90);
    }

    /**
     * Generate a text completion. Returns null on failure so callers can
     * degrade gracefully (Ollama is a local dependency and may be offline).
     */
    public function generate(string $prompt, ?string $system = null, bool $json = false): ?string
    {
        try {
            $payload = [
                'model' => $this->generationModel,
                'prompt' => $prompt,
                'stream' => false,
            ];

            if ($system) {
                $payload['system'] = $system;
            }

            if ($json) {
                $payload['format'] = 'json';
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/generate", $payload);

            if (! $response->successful()) {
                Log::warning('Ollama generate failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            return $response->json('response');
        } catch (\Throwable $e) {
            Log::error('Ollama generate exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Embed a single string. Returns a 768-dim float array (nomic-embed-text), or null on failure.
     */
    public function embed(string $text): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/embed", [
                    'model' => $this->embeddingModel,
                    'input' => $text,
                ]);

            if (! $response->successful()) {
                Log::warning('Ollama embed failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            $embeddings = $response->json('embeddings');

            return $embeddings[0] ?? null;
        } catch (\Throwable $e) {
            Log::error('Ollama embed exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Embed many strings in one request (chunk ingestion). Returns an array
     * of embeddings aligned with $texts, or null on failure.
     */
    public function embedBatch(array $texts): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/embed", [
                    'model' => $this->embeddingModel,
                    'input' => $texts,
                ]);

            if (! $response->successful()) {
                Log::warning('Ollama embedBatch failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            return $response->json('embeddings');
        } catch (\Throwable $e) {
            Log::error('Ollama embedBatch exception: '.$e->getMessage());

            return null;
        }
    }

    public function isAvailable(): bool
    {
        try {
            return Http::timeout(5)->get("{$this->baseUrl}/api/tags")->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
