<?php

namespace App\Livewire;

use App\Models\DocumentFinding;
use App\Models\Shipment;
use App\Models\SignalEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        $deadlineShipments = Shipment::query()
            ->visibleTo($user)
            ->whereNotNull('filing_deadline')
            ->whereBetween('filing_deadline', [now(), now()->addDays(7)])
            ->orderBy('filing_deadline')
            ->get();

        $openSignalsCount = SignalEvent::whereNull('resolved_at')
            ->when($user->isClientUser(), fn ($q) => $q->whereHas('shipment', fn ($s) => $s->where('client_id', $user->client_id)))
            ->count();

        $statusCounts = Shipment::query()
            ->visibleTo($user)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentFindings = DocumentFinding::query()
            ->whereHas('shipment', fn ($q) => $q->visibleTo($user))
            ->where('status', 'open')
            ->with('shipment')
            ->latest('created_at')
            ->limit(8)
            ->get();

        return view('livewire.dashboard', [
            'deadlineShipments' => $deadlineShipments,
            'openSignalsCount' => $openSignalsCount,
            'statusCounts' => $statusCounts,
            'recentFindings' => $recentFindings,
        ])->title('Dashboard');
    }
}
