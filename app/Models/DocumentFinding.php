<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFinding extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'shipment_id',
        'document_id',
        'finding_type',
        'description',
        'suggested_value',
        'severity',
        'status',
        'resolved_by',
        'resolution_note',
        'resolved_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DocumentFinding $finding) {
            $finding->created_at ??= now();
        });
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function document()
    {
        return $this->belongsTo(ShipmentDocument::class, 'document_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
