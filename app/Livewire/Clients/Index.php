<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showForm = false;

    public string $name = '';

    public string $contact_name = '';

    public string $contact_email = '';

    public string $country = '';

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'contact_name' => 'nullable|string|max:150',
            'contact_email' => 'nullable|email|max:150',
            'country' => 'nullable|string|max:100',
        ]);

        Client::create([
            'name' => $this->name,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'country' => $this->country,
        ]);

        $this->reset('name', 'contact_name', 'contact_email', 'country', 'showForm');
    }

    public function render()
    {
        return view('livewire.clients.index', [
            'clients' => Client::withCount('shipments')->orderBy('name')->get(),
        ])->title('Clients');
    }
}
