<?php

namespace App\Http\Controllers;

use App\Models\DocumentFinding;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportPdfController
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $shipments = Shipment::query()->visibleTo($user)->with('client')->get();

        $findings = DocumentFinding::query()
            ->whereHas('shipment', fn ($q) => $q->visibleTo($user))
            ->where('status', 'open')
            ->with('shipment')
            ->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'shipments' => $shipments,
            'findings' => $findings,
            'generatedAt' => now(),
            'generatedBy' => $user,
        ])->setPaper('a4');

        return $pdf->download('compliance-report-'.now()->format('Y-m-d').'.pdf');
    }
}
