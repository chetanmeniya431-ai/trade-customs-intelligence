<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'origin_country',
        'destination_country',
        'mode',
        'document_type',
        'required',
        'conditional_on',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
        ];
    }

    public static function forRoute(string $origin, string $destination, string $mode)
    {
        return static::query()
            ->where('origin_country', $origin)
            ->where('destination_country', $destination)
            ->where('mode', $mode)
            ->get();
    }
}
