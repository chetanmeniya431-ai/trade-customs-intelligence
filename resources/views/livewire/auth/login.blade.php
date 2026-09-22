<div class="card p-6">
    <form wire:submit="login" class="space-y-5">
        <div>
            <label class="form-label" for="email">Email</label>
            <input wire:model="email" type="email" id="email" class="form-input" placeholder="you@company.com" autofocus>
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="password">Password</label>
            <input wire:model="password" type="password" id="password" class="form-input">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-teal-600 focus:ring-teal-600">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-xs text-teal-600 hover:text-teal-700">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Log in</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </button>
    </form>

    <div class="mt-6 rounded-md bg-gray-50 p-3 text-xs text-gray-500 space-y-0.5">
        <p class="font-medium text-gray-600">Demo logins (password: <code>password</code>)</p>
        <p>admin@tradecustoms.local — Customs Broker</p>
        <p>coordinator@tradecustoms.local — Coordinator</p>
        <p>compliance@tradecustoms.local — Compliance Manager</p>
        <p>finance@tradecustoms.local — Finance</p>
        <p>client@tradecustoms.local — Client</p>
        <p class="mt-2 pt-2 border-t border-gray-200 font-medium text-gray-600">Admin login</p>
        <p>superadmin@tradecustoms.local — Super Admin</p>
    </div>
</div>
