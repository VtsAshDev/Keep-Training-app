<?php

use App\Actions\Core\RecordLogin;
use App\Enums\Core\LoginMethod;
use App\Models\User;
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

            /** @var User $user */
            $user = Auth::user();
            app(RecordLogin::class)->handle($user, LoginMethod::Email);

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

<div x-data="{ withEmail: @js($errors->any()) }" class="flex flex-col gap-3">
    <x-ui.button wire:click="loginGoogle" class="w-full">{{ __('Sign in with Google') }}</x-ui.button>

    <x-ui.button variant="ghost" x-show="! withEmail" x-on:click="withEmail = true" class="w-full">{{ __('Sign in with email') }}</x-ui.button>

    <form x-show="withEmail" x-cloak wire:submit="login" class="flex flex-col gap-4">
        <x-ui.input :label="__('Email')" type="email" wire:model="email" autocomplete="email" required />
        <x-ui.input :label="__('Password')" type="password" wire:model="password" autocomplete="current-password" required />
        <x-ui.checkbox :label="__('Remember me')" wire:model="remember" />
        <x-ui.button type="submit" variant="secondary" class="w-full">{{ __('Log in') }}</x-ui.button>
    </form>

    <p class="text-center text-[13px] text-ink-muted">{{ __('Install it on your phone from the browser menu') }}</p>
</div>
