<?php

use App\Actions\Core\RecordLogin;
use App\Enums\Core\LoginMethod;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Livewire\Livewire;

test('google sign in is recorded and shown in settings', function () {
    Socialite::fake('google', GoogleUser::fake(['name' => 'Ana Souza', 'email' => 'ana@example.com']));

    $this->get('/auth/google/callback');

    $user = User::where('email', 'ana@example.com')->firstOrFail();
    expect($user->last_login_method)->toBe(LoginMethod::Google)
        ->and(session(RecordLogin::SESSION_KEY))->toBe('google');

    $this->get('/configuracoes')->assertSee('Conectado com Google');
});

test('email sign in is recorded', function () {
    $user = User::factory()->create(['email' => 'ana@example.com', 'password' => 'password123']);

    Livewire::test('core.login')
        ->set('email', 'ana@example.com')
        ->set('password', 'password123')
        ->call('login')
        ->assertRedirect(route('today'));

    expect($user->fresh()->last_login_method)->toBe(LoginMethod::Email)
        ->and(session(RecordLogin::SESSION_KEY))->toBe('email');
});

test('settings fall back to the last method when the session does not have it', function () {
    $user = User::factory()->create(['last_login_method' => LoginMethod::Email]);

    $this->actingAs($user)->get('/configuracoes')->assertSee('Conectado com e-mail');
});

test('the current session wins over the last method of another device', function () {
    $user = User::factory()->create(['last_login_method' => LoginMethod::Email]);

    $this->actingAs($user)
        ->withSession([RecordLogin::SESSION_KEY => 'google'])
        ->get('/configuracoes')
        ->assertSee('Conectado com Google')
        ->assertDontSee('Conectado com e-mail');
});

test('settings show nothing when the method is unknown', function () {
    $this->actingAs(User::factory()->create())->get('/configuracoes')->assertDontSee('Conectado com');
});
