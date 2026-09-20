<?php

namespace App\Models;

use App\Casts\Vector;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffChunk extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'chunk_index',
        'chunk_text',
        'embedding',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TariffChunk $chunk) {
            $chunk->created_at ??= now();
        });
    }

    public function document()
    {
        return $this->belongsTo(TariffDocument::class, 'document_id');
    }
}
