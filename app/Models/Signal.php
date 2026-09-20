<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'condition_key',
        'severity',
        'active',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Signal $signal) {
            $signal->created_at ??= now();
        });
    }

    public function events()
    {
        return $this->hasMany(SignalEvent::class);
    }
}
