<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Signal;
use App\Models\SignalEvent;
use Illuminate\Support\Carbon;

/**
 * Evaluates the 10 seeded signal conditions against current shipment state.
 * Each condition is keyed by `condition_key` on the signals table so this
 * class stays the single source of truth for "what does each signal mean".
 */
class SignalsEngineService
{
    public function runAll(): int
    {
        $fired = 0;
        $signals = Signal::where('active', true)->get()->keyBy('condition_key');

        foreach ($signals as $key => $signal) {
            $method = 'evaluate'.str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $fired += $this->{$method}($signal);
            }
        }

        return $fired;
    }

    protected function fire(Signal $signal, ?Shipment $shipment, string $note): bool
    {
        $exists = SignalEvent::where('signal_id', $signal->id)
            ->where('shipment_id', $shipment?->id)
            ->whereNull('resolved_at')
            ->exists();

        if ($exists) {
            return false;
        }

        SignalEvent::create([
            'signal_id' => $signal->id,
            'shipment_id' => $shipment?->id,
            'triggered_at' => now(),
            'note' => $note,
        ]);

        return true;
    }

    protected function evaluateMissingRequiredDocument(Signal $signal): int
    {
        $fired = 0;
        $cutoff = now()->addHours(48);

        Shipment::whereNotNull('filing_deadline')
            ->where('filing_deadline', '<=', $cutoff)
            ->whereNotIn('status', ['filed', 'cleared'])
            ->get()
            ->each(function (Shipment $shipment) use ($signal, &$fired) {
                $missing = $shipment->documents()->where('required', true)->whereNull('file_path')->exists();
                if ($missing && $this->fire($signal, $shipment, 'A required document is missing within 48h of the filing deadline.')) {
                    $fired++;
                }
            });

        return $fired;
    }

    protected function evaluateCustomsFilingDeadline48h(Signal $signal): int
    {
        // Non-overlapping urgency bands rather than a narrow 1-hour window:
        // a shipment sits in the "48h" band once it's past the 24h band and
        // until its deadline is inside it. Wide bands mean a shipment can't
        // slip through undetected if a scheduled run is ever missed.
        return $this->deadlineWindow($signal, 24, 48, '48 hours');
    }

    protected function evaluateCustomsFilingDeadline24h(Signal $signal): int
    {
        return $this->deadlineWindow($signal, 0, 24, '24 hours');
    }

    protected function deadlineWindow(Signal $signal, int $fromHours, int $toHours, string $label): int
    {
        $fired = 0;
        $from = now()->addHours($fromHours);
        $to = now()->addHours($toHours);

        Shipment::whereNotNull('filing_deadline')
            ->whereBetween('filing_deadline', [$from, $to])
            ->whereNotIn('status', ['filed', 'cleared'])
            ->get()
            ->each(function (Shipment $shipment) use ($signal, $label, &$fired) {
                if ($this->fire($signal, $shipment, "Filing deadline is approximately {$label} away.")) {
                    $fired++;
                }
            });

        return $fired;
    }

    protected function evaluateHsCodeMismatchDetected(Signal $signal): int
    {
        return $this->fromOpenFindings($signal, 'hs_mismatch');
    }

    protected function evaluateValueMismatchDetected(Signal $signal): int
    {
        return $this->fromOpenFindings($signal, 'value_mismatch');
    }

    protected function fromOpenFindings(Signal $signal, string $findingType): int
    {
        $fired = 0;

        Shipment::whereHas('findings', function ($q) use ($findingType) {
            $q->where('finding_type', $findingType)->where('status', 'open');
        })->get()->each(function (Shipment $shipment) use ($signal, $findingType, &$fired) {
            $label = $findingType === 'hs_mismatch' ? 'HS code mismatch' : 'value mismatch';
            if ($this->fire($signal, $shipment, "AI check found a {$label}.")) {
                $fired++;
            }
        });

        return $fired;
    }

    protected function evaluateFilingDeadlinePassedNotFiled(Signal $signal): int
    {
        $fired = 0;

        Shipment::whereNotNull('filing_deadline')
            ->where('filing_deadline', '<', now())
            ->whereNotIn('status', ['filed', 'cleared'])
            ->get()
            ->each(function (Shipment $shipment) use ($signal, &$fired) {
                if ($this->fire($signal, $shipment, 'Filing deadline has passed and the shipment is not filed.')) {
                    $fired++;
                }
            });

        return $fired;
    }

    protected function evaluateExpiredDocumentUploaded(Signal $signal): int
    {
        $fired = 0;

        Shipment::whereHas('documents', function ($q) {
            $q->whereNotNull('expiry_date')->where('expiry_date', '<', now());
        })->get()->each(function (Shipment $shipment) use ($signal, &$fired) {
            if ($this->fire($signal, $shipment, 'A document with a past expiry date was uploaded.')) {
                $fired++;
            }
        });

        return $fired;
    }

    protected function evaluateRepeatHsCodeWarningSameClient(Signal $signal): int
    {
        $fired = 0;
        $since = now()->subDays(90);

        $clientCounts = \App\Models\DocumentFinding::query()
            ->join('shipments', 'shipments.id', '=', 'document_findings.shipment_id')
            ->whereNotNull('shipments.client_id')
            ->where('document_findings.finding_type', 'hs_mismatch')
            ->where('document_findings.created_at', '>=', $since)
            ->selectRaw('shipments.client_id, count(distinct document_findings.shipment_id) as shipment_count')
            ->groupBy('shipments.client_id')
            ->havingRaw('count(distinct document_findings.shipment_id) >= ?', [3])
            ->get();

        foreach ($clientCounts as $row) {
            $latestShipment = Shipment::where('client_id', $row->client_id)
                ->whereHas('findings', fn ($q) => $q->where('finding_type', 'hs_mismatch'))
                ->latest()
                ->first();

            if ($latestShipment && $this->fire($signal, $latestShipment, 'This client has HS mismatches on 3+ shipments in the last 90 days.')) {
                $fired++;
            }
        }

        return $fired;
    }

    protected function evaluateHighValueShipmentManualReview(Signal $signal): int
    {
        $fired = 0;
        $threshold = (float) config('services.compliance.high_value_threshold', 50000);

        // "Reviewed" is proxied by at least one document being verified —
        // there's no separate compliance-review timestamp on the shipment.
        Shipment::where('declared_value', '>', $threshold)
            ->get()
            ->each(function (Shipment $shipment) use ($signal, &$fired) {
                $reviewed = $shipment->documents()->where('verified', true)->exists();
                if (! $reviewed && $this->fire($signal, $shipment, "Shipment value exceeds the high-value threshold and has had no compliance review.")) {
                    $fired++;
                }
            });

        return $fired;
    }

    protected function evaluateMissingCertificateOfOrigin(Signal $signal): int
    {
        $fired = 0;
        $preferentialDestinations = ['UAE', 'US', 'United States', 'UK', 'United Kingdom'];

        Shipment::whereIn('destination_country', $preferentialDestinations)
            ->get()
            ->each(function (Shipment $shipment) use ($signal, &$fired) {
                $hasCoO = $shipment->documents()
                    ->where('document_type', 'ilike', '%certificate of origin%')
                    ->whereNotNull('file_path')
                    ->exists();

                if (! $hasCoO && $this->fire($signal, $shipment, 'Shipment to a preferential-duty destination has no Certificate of Origin.')) {
                    $fired++;
                }
            });

        return $fired;
    }
}
