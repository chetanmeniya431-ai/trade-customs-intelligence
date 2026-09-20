<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffDocument extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'country_code',
        'document_type',
        'file_path',
        'chunk_count',
        'embedded_at',
        'uploaded_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'embedded_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TariffDocument $doc) {
            $doc->created_at ??= now();
        });
    }

    public function chunks()
    {
        return $this->hasMany(TariffChunk::class, 'document_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isEmbedded(): bool
    {
        return ! is_null($this->embedded_at);
    }
}
