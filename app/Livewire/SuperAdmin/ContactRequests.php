<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ContactRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ContactRequests extends Component
{
    use WithPagination;

    public string $filter = 'new';
    public ?int $markingId = null;
    public string $notes = '';

    public function filterBy(string $status): void
    {
        $this->filter = $status;
        $this->resetPage();
    }

    public function startRespond(int $id): void
    {
        $req = ContactRequest::findOrFail($id);
        $this->markingId = $id;
        $this->notes = $req->notes ?? '';
    }

    public function saveResponse(): void
    {
        $this->validate(['notes' => 'nullable|string|max:2000']);

        ContactRequest::findOrFail($this->markingId)->update([
            'status' => 'responded',
            'notes'  => $this->notes,
        ]);

        $this->markingId = null;
        $this->notes = '';
    }

    public function cancelRespond(): void
    {
        $this->markingId = null;
        $this->notes = '';
    }

    public function render()
    {
        $requests = ContactRequest::query()
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->latest()
            ->paginate(20);

        return view('livewire.super-admin.contact-requests', [
            'requests' => $requests,
            'newCount' => ContactRequest::where('status', 'new')->count(),
        ])->title('Contact Requests');
    }
}
