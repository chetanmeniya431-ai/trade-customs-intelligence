<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignalEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'signal_id',
        'shipment_id',
        'triggered_at',
        'resolved_at',
        'resolved_by',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
            'resolved_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SignalEvent $event) {
            $event->created_at ??= now();
            $event->triggered_at ??= now();
        });
    }

    public function signal()
    {
        return $this->belongsTo(Signal::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return is_null($this->resolved_at);
    }
}
