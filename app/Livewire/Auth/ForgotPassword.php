<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class ForgotPassword extends Component
{
    public string $email = '';
    public bool $sent = false;

    public function send(): void
    {
        $this->validate(['email' => ['required', 'email', 'max:255']]);

        $ipKey = 'forgot-ip:' . (request()->ip() ?? 'unknown');
        $emailKey = 'forgot-email:' . sha1(Str::lower($this->email));

        if (RateLimiter::tooManyAttempts($ipKey, 10) || RateLimiter::tooManyAttempts($emailKey, 3)) {
            $this->addError('email', 'Too many requests. Please wait a while and try again.');
            return;
        }

        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($emailKey, 3600);

        // Same response whether or not the address exists (prevents enumeration).
        Password::sendResetLink(['email' => $this->email]);

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')->title('Forgot password');
    }
}
