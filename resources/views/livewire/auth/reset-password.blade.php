<div class="card p-6">
    <h2 class="text-base font-semibold text-gray-900 mb-1">Set a new password</h2>
    <p class="text-sm text-gray-500 mb-5">Choose a strong password for your account.</p>

    <form wire:submit="resetPassword" class="space-y-5">
        <input type="hidden" wire:model="token">
        <div>
            <label class="form-label" for="email">Email</label>
            <input wire:model="email" type="email" id="email" class="form-input" autocomplete="username">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="password">New password</label>
            <input wire:model="password" type="password" id="password" class="form-input" autocomplete="new-password">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="password_confirmation">Confirm new password</label>
            <input wire:model="password_confirmation" type="password" id="password_confirmation" class="form-input" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="resetPassword">Reset password</span>
            <span wire:loading wire:target="resetPassword">Resetting…</span>
        </button>
    </form>
</div>
