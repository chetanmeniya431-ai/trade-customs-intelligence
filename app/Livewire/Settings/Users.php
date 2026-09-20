<?php

namespace App\Livewire\Settings;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class Users extends Component
{
    public bool $showForm = false;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    public ?int $client_id = null;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'client_id' => $validated['client_id'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        $this->reset('name', 'email', 'password', 'role', 'client_id', 'showForm');
    }

    public function render()
    {
        return view('livewire.settings.users', [
            'users' => User::with('roles', 'client')->orderBy('name')->get(),
            'roles' => Role::orderBy('name')->pluck('name'),
            'clients' => Client::orderBy('name')->get(),
        ])->title('Users');
    }
}
