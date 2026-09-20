<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsLookup extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'query_text',
        'suggested_codes',
        'confidence',
        'source_section',
        'result_summary',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'suggested_codes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HsLookup $lookup) {
            $lookup->created_at ??= now();
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
