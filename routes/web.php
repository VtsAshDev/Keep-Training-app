<?php

use App\Actions\Core\RecordLogin;
use App\Enums\Core\LoginMethod;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::view('/', 'welcome')->middleware('guest')->name('home');

Route::redirect('/login', '/')->name('login');

Route::middleware('auth')->group(function () {
    Route::livewire('/hoje', 'core.today')->name('today');
    Route::livewire('/configuracoes', 'core.settings')->name('settings');

    Route::post('/sair', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');
});

Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google');

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user();

    $user = User::updateOrCreate([
        'email' => $googleUser->getEmail(),
    ], [
        'name' => $googleUser->getName(),
        'password' => bcrypt(str()->random(24)),
    ]);

    Auth::login($user, remember: true);
    session()->regenerate();
    app(RecordLogin::class)->handle($user, LoginMethod::Google);

    return redirect()->intended(route('today'));
});

require __DIR__.'/training.php';

if (app()->isLocal()) {
    Route::view('/dev/ui', 'dev.ui')->name('dev.ui');
}
