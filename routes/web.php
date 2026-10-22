<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::view('/', 'welcome')->middleware('guest')->name('home');

Route::redirect('/login', '/')->name('login');

Route::livewire('/hoje', 'core.today')->middleware('auth')->name('today');

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

    return redirect()->intended(route('today'));
});
