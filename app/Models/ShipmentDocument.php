<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'document_type',
        'file_path',
        'uploaded_by',
        'uploaded_at',
        'verified',
        'verified_by',
        'verified_at',
        'required',
        'notes',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'verified' => 'boolean',
            'verified_at' => 'datetime',
            'required' => 'boolean',
            'expiry_date' => 'date',
        ];
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function findings()
    {
        return $this->hasMany(DocumentFinding::class, 'document_id');
    }

    public function isUploaded(): bool
    {
        return ! empty($this->file_path);
    }
}
