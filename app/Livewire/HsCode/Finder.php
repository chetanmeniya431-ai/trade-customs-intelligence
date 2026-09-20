<?php

namespace App\Livewire\HsCode;

use App\Models\HsLookup;
use App\Services\HsCodeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Finder extends Component
{
    public string $description = '';

    public ?array $result = null;

    public bool $searching = false;

    public function search(HsCodeService $service): void
    {
        $this->validate(['description' => 'required|string|min:8|max:1000']);

        $this->result = $service->suggest($this->description, Auth::id());
    }

    public function useExample(string $example): void
    {
        $this->description = $example;
    }

    public function render()
    {
        return view('livewire.hs-code.finder', [
            'recentLookups' => HsLookup::latest('created_at')->limit(8)->get(),
            'examples' => [
                '6-ply polypropylene woven sacks, 50kg capacity, plain weave',
                'Electronic speed controller (ESC) for brushless drones, 30A, no battery',
                'Unroasted Arabica green coffee beans, not decaffeinated',
            ],
        ])->title('HS Code Finder');
    }
}
