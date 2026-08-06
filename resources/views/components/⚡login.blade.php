<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate(['required', 'email'])]
    public string $email = '';

    #[Validate(['required', 'string', 'min:8'])]
    public string $password = '';

    public function login()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email)).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError('email', "Muitas tentativas de login. Tente novamente em {$seconds} segundos.");

            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::clear($throttleKey);

            session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        RateLimiter::hit($throttleKey);

        $this->addError('email', 'As credenciais fornecidas estão incorretas.');
    }

    public function loginGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
};
?>

<div class="bg-surface w-full max-w-[360px] border-[3px] border-primary block-shadow-primary p-8">

    <div class="flex justify-center mb-6 cursor-pointer" onclick="toggleView('welcome')" title="Voltar">
        <img src="/icon-kt.png" alt="Keep Training Logo" class="w-24 h-24 object-contain block-shadow-primary">
    </div>


    <h2 class="text-center font-display-lg text-headline-md uppercase text-primary font-black mb-4">ENTRAR</h2>

    <div class="border-t-2 border-primary mb-6"></div>


    <form wire:submit="login" class="space-y-4">

        <div class="space-y-1">
            <label class="block font-label-bold text-label-bold uppercase text-primary font-bold">E-MAIL</label>
            <div class="flex items-center border border-outline px-3 py-2 bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-on-surface-variant mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <input type="email" wire:model="email" placeholder="ATLETA@KEEPTRAINING.COM" class="w-full bg-transparent outline-none font-mono-data text-sm placeholder:text-outline-variant text-primary uppercase" required>
            </div>
            @error('email') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
        </div>


        <div class="space-y-1">
            <div class="flex justify-between items-center">
                <label class="block font-label-bold text-label-bold uppercase text-primary font-bold">SENHA</label>
                <a href="#" class="font-label-bold text-[10px] uppercase text-primary font-bold hover:underline">ESQUECI MINHA SENHA</a>
            </div>
            <div class="flex items-center border border-outline px-3 py-2 bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-on-surface-variant mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <input type="password" wire:model="password" placeholder="••••••••" class="w-full bg-transparent outline-none font-mono-data text-sm placeholder:text-outline-variant text-primary" required>
            </div>
            @error('password') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
        </div>


        <button type="submit" class="w-full mt-6 bg-secondary-container hover:bg-secondary text-primary font-headline-md flex items-center justify-center gap-2 p-3 border-2 border-primary block-shadow-primary transition-all press-effect">
            <span class="font-black uppercase tracking-tight text-lg">ENTRAR</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>
    </form>


    <div class="flex items-center my-6">
        <div class="flex-grow border-t border-primary"></div>
        <span class="mx-4 font-label-bold text-xs uppercase text-primary font-black">OU</span>
        <div class="flex-grow border-t border-primary"></div>
    </div>


    <button  wire:click="loginGoogle"  type="button" class="w-full bg-white hover:bg-surface-variant text-primary font-label-bold flex items-center justify-center gap-3 p-3 border-2 border-primary block-shadow-primary transition-all press-effect">
        <svg viewBox="0 0 24 24" class="size-5" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        <span class="uppercase font-bold tracking-wide">ENTRAR COM GOOGLE</span>
    </button>


    <div class="mt-8 text-center border-t border-primary pt-4">
        <span class="font-label-bold text-xs text-on-surface-variant">Não tem uma conta?</span>
        <a href="#" class="font-label-bold text-xs text-primary font-black underline uppercase ml-1">CADASTRE-SE</a>
    </div>
</div>
