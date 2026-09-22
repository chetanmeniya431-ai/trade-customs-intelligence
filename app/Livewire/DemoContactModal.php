<?php

namespace App\Livewire;

use App\Models\ContactRequest;
use Livewire\Attributes\On;
use Livewire\Component;

class DemoContactModal extends Component
{
    public bool $open = false;
    public bool $submitted = false;

    public string $name = '';
    public string $email = '';
    public string $company = '';
    public string $phone = '';
    public string $message = '';

    protected array $rules = [
        'name'    => 'required|string|max:100',
        'email'   => 'required|email|max:150',
        'company' => 'nullable|string|max:150',
        'phone'   => 'nullable|string|max:30',
        'message' => 'required|string|max:1000',
    ];

    protected array $messages = [
        'name.required'    => 'Please enter your name.',
        'email.required'   => 'Please enter your email.',
        'email.email'      => 'Please enter a valid email address.',
        'message.required' => 'Please describe what you need.',
    ];

    #[On('show-demo-modal')]
    public function show(): void
    {
        $this->open = true;
        $this->submitted = false;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function submit(): void
    {
        $this->validate();

        ContactRequest::create([
            'name'    => $this->name,
            'email'   => $this->email,
            'company' => $this->company ?: null,
            'phone'   => $this->phone ?: null,
            'message' => $this->message,
            'project' => 'trade-customs-intelligence',
        ]);

        $this->submitted = true;
        $this->reset(['name', 'email', 'company', 'phone', 'message']);
    }

    public function render()
    {
        return view('livewire.demo-contact-modal');
    }
}
