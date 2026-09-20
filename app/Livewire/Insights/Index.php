<?php

namespace App\Livewire\Insights;

use App\Models\DocumentFinding;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * "Compliance Insights" trend information — not signals, just patterns.
 * Computed live from document_findings/shipments rather than a materialized
 * weekly snapshot, since the demo dataset is small; the query shapes match
 * what a scheduled weekly job would produce.
 */
#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        $hsWarningsByProduct = DocumentFinding::query()
            ->join('shipments', 'shipments.id', '=', 'document_findings.shipment_id')
            ->where('document_findings.finding_type', 'hs_mismatch')
            ->selectRaw('shipments.product_description, count(*) as total')
            ->groupBy('shipments.product_description')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $missingDocsByRoute = DocumentFinding::query()
            ->join('shipments', 'shipments.id', '=', 'document_findings.shipment_id')
            ->where('document_findings.finding_type', 'missing_doc')
            ->selectRaw("shipments.origin_country || ' → ' || shipments.destination_country as route, count(*) as total")
            ->groupBy('route')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $valueMismatchByClient = DocumentFinding::query()
            ->join('shipments', 'shipments.id', '=', 'document_findings.shipment_id')
            ->join('clients', 'clients.id', '=', 'shipments.client_id')
            ->where('document_findings.finding_type', 'value_mismatch')
            ->selectRaw('clients.name, count(*) as total')
            ->groupBy('clients.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('livewire.insights.index', [
            'hsWarningsByProduct' => $hsWarningsByProduct,
            'missingDocsByRoute' => $missingDocsByRoute,
            'valueMismatchByClient' => $valueMismatchByClient,
        ])->title('Compliance Insights');
    }
}
