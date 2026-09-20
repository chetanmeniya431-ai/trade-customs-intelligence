<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'direction',
        'origin_country',
        'destination_country',
        'product_description',
        'hs_code',
        'declared_value',
        'declared_currency',
        'mode',
        'incoterms',
        'expected_date',
        'filing_deadline',
        'client_id',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'declared_value' => 'decimal:2',
            'expected_date' => 'date',
            'filing_deadline' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            if (empty($shipment->reference)) {
                $shipment->reference = static::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $year = now()->year;
        $last = static::query()
            ->where('reference', 'like', "SHP-{$year}-%")
            ->orderByDesc('reference')
            ->value('reference');

        $next = $last ? ((int) Str::afterLast($last, '-')) + 1 : 1;

        return sprintf('SHP-%d-%04d', $year, $next);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(ShipmentDocument::class);
    }

    public function findings()
    {
        return $this->hasMany(DocumentFinding::class);
    }

    public function openFindings()
    {
        return $this->findings()->where('status', 'open');
    }

    public function embedding()
    {
        return $this->hasOne(ShipmentEmbedding::class);
    }

    public function signalEvents()
    {
        return $this->hasMany(SignalEvent::class);
    }

    public function documentCompletionPercent(): int
    {
        $required = $this->documents()->where('required', true)->count();
        if ($required === 0) {
            return 100;
        }
        $uploaded = $this->documents()->where('required', true)->whereNotNull('file_path')->count();

        return (int) round(($uploaded / $required) * 100);
    }

    /**
     * Derives status from actual document/finding state. Skips shipments
     * already past the point this applies (filed/cleared/held are set by
     * explicit user action, not inferred from checklist completion).
     */
    public function recomputeStatus(): void
    {
        if (in_array($this->status, ['filed', 'cleared', 'held'])) {
            return;
        }

        $ready = $this->documentCompletionPercent() === 100 && $this->openFindings()->count() === 0;

        $this->update(['status' => $ready ? 'ready' : 'documents_pending']);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isClientUser() && $user->client_id) {
            return $query->where('client_id', $user->client_id);
        }

        return $query;
    }
}
