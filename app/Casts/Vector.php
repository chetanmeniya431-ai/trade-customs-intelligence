<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Casts a pgvector `vector` column to/from a plain PHP array of floats.
 * Postgres returns vectors as the literal text "[0.1,0.2,...]".
 */
class Vector implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $trimmed = trim($value, "[]");

        if ($trimmed === '') {
            return [];
        }

        return array_map('floatval', explode(',', $trimmed));
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return '['.implode(',', array_map(static fn ($v) => (float) $v, $value)).']';
    }
}
