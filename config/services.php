<?php

return [
    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://host.docker.internal:11434'),
        'generation_model' => env('OLLAMA_GENERATION_MODEL', 'llama3.2:3b'),
        'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 90),
        'embedding_dimensions' => 768,
    ],

    'compliance' => [
        'high_value_threshold' => (float) env('HIGH_VALUE_THRESHOLD', 50000),
        'value_mismatch_percent' => (float) env('VALUE_MISMATCH_PERCENT', 5),
    ],
];
