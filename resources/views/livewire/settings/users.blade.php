<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-900">Users</h1>
        <button wire:click="$toggle('showForm')" class="btn btn-primary">
            <x-icon name="plus" class="h-4 w-4" /> New user
        </button>
    </div>

    @if ($showForm)
        <form wire:submit="save" class="card p-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="form-label">Name</label>
                <input wire:model="name" type="text" class="form-input">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Email</label>
                <input wire:model="email" type="email" class="form-input">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Password</label>
                <input wire:model="password" type="password" class="form-input">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Role</label>
                <select wire:model="role" class="form-select">
                    <option value="">— Select —</option>
                    @foreach ($roles as $roleName)
                        <option value="{{ $roleName }}">{{ $roleName }}</option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="form-label">Client <span class="text-gray-400 font-normal">(for Client role)</span></label>
                <select wire:model="client_id" class="form-select max-w-xs">
                    <option value="">— None —</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2">
                <button type="button" wire:click="$set('showForm', false)" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save user</button>
            </div>
        </form>
    @endif

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Client</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3"><span class="badge badge-teal">{{ $user->roles->pluck('name')->implode(', ') }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->client->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>
</div>
