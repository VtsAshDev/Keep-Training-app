<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate(['required', 'email'])]
    public string $email = '';

    #[Validate(['required', 'string', 'min:8'])]
    public string $password = '';

    public bool $remember = true;

    public function login()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email)).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError('email', __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]));

            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::clear($throttleKey);

            session()->regenerate();

            return redirect()->intended(route('today'));
        }

        RateLimiter::hit($throttleKey);

        $this->addError('email', __('These credentials do not match our records.'));
    }

    public function loginGoogle()
    {
        $url = Socialite::driver('google')->redirect()->getTargetUrl();

        $this->redirect($url);
    }
};
?>

<div>
    <form wire:submit="login">
        <label>
            {{ __('Email') }}
            <input type="email" wire:model="email" required>
        </label>
        @error('email') <span>{{ $message }}</span> @enderror

        <label>
            {{ __('Password') }}
            <input type="password" wire:model="password" required>
        </label>
        @error('password') <span>{{ $message }}</span> @enderror

        <label>
            <input type="checkbox" wire:model="remember">
            {{ __('Remember me') }}
        </label>

        <button type="submit">{{ __('Log in') }}</button>
    </form>

    <button wire:click="loginGoogle" type="button">{{ __('Sign in with Google') }}</button>
</div>
