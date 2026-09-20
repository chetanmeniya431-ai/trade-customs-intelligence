<?php

namespace App\Livewire\Shipments;

use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $direction = '';

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'direction'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $user = Auth::user();

        $shipments = Shipment::query()
            ->visibleTo($user)
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('reference', 'ilike', "%{$this->search}%")
                        ->orWhere('product_description', 'ilike', "%{$this->search}%")
                        ->orWhere('hs_code', 'ilike', "%{$this->search}%");
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->direction, fn ($q) => $q->where('direction', $this->direction))
            ->with(['client'])
            ->withCount(['documents', 'openFindings'])
            ->latest()
            ->paginate(15);

        return view('livewire.shipments.index', ['shipments' => $shipments])->title('Shipments');
    }
}
