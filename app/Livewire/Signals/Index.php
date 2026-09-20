<?php

namespace App\Livewire\Signals;

use App\Models\Signal;
use App\Models\SignalEvent;
use App\Services\SignalsEngineService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $filter = 'open';

    public function resolve(int $eventId): void
    {
        $event = SignalEvent::findOrFail($eventId);
        $event->update([
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);
    }

    public function runCheckNow(SignalsEngineService $engine): void
    {
        $fired = $engine->runAll();
        session()->flash('status', "Signals evaluated. {$fired} new event(s) fired.");
    }

    public function render()
    {
        $user = Auth::user();

        $events = SignalEvent::with(['signal', 'shipment'])
            ->when($user->isClientUser(), fn ($q) => $q->whereHas('shipment', fn ($s) => $s->where('client_id', $user->client_id)))
            ->when($this->filter === 'open', fn ($q) => $q->whereNull('resolved_at'))
            ->when($this->filter === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'))
            ->latest('triggered_at')
            ->get();

        return view('livewire.signals.index', [
            'events' => $events,
            'signals' => Signal::orderBy('name')->get(),
        ])->title('Signals');
    }
}
