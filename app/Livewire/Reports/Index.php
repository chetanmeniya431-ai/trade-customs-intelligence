<?php

namespace App\Livewire\Reports;

use App\Models\DocumentFinding;
use App\Models\Shipment;
use App\Models\SignalEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        $user = Auth::user();

        $shipments = Shipment::query()->visibleTo($user)->get();

        return view('livewire.reports.index', [
            'totalShipments' => $shipments->count(),
            'statusCounts' => $shipments->countBy('status'),
            'findingCounts' => DocumentFinding::query()
                ->whereHas('shipment', fn ($q) => $q->visibleTo($user))
                ->selectRaw('finding_type, count(*) as total')
                ->groupBy('finding_type')
                ->pluck('total', 'finding_type'),
            'openSignals' => SignalEvent::whereNull('resolved_at')
                ->when($user->isClientUser(), fn ($q) => $q->whereHas('shipment', fn ($s) => $s->where('client_id', $user->client_id)))
                ->count(),
            'totalDeclaredValue' => $shipments->sum('declared_value'),
        ])->title('Reports');
    }
}
