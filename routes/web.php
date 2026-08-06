<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::redirect('/login', '/')->name('login');

Route::view('/intern', 'intern')->middleware('auth')->name('intern');

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

    Auth::login($user);

    return redirect('/intern');
});
