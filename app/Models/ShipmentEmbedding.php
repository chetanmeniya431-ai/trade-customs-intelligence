<?php

namespace App\Models;

use App\Casts\Vector;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
        ];
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
