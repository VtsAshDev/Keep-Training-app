<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

test('guests are redirected from today to the entry page', function () {
    $this->get('/hoje')->assertRedirect('/login');
});

test('authenticated users can see the today page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/hoje')
        ->assertOk()
        ->assertSee('Hoje');
});

test('authenticated users are redirected from the entry page to today', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect('/hoje');
});

test('google callback creates the user, logs in and redirects to today', function () {
    Socialite::fake('google', GoogleUser::fake([
        'name' => 'Ana Souza',
        'email' => 'ana@example.com',
    ]));

    $this->get('/auth/google/callback')->assertRedirect('/hoje');

    $user = User::where('email', 'ana@example.com')->firstOrFail();

    expect($user->name)->toBe('Ana Souza');
    $this->assertAuthenticatedAs($user);
});

test('google callback logs in an existing user without duplicating it', function () {
    $user = User::factory()->create(['email' => 'ana@example.com']);

    Socialite::fake('google', GoogleUser::fake([
        'name' => 'Ana Souza',
        'email' => 'ana@example.com',
    ]));

    $this->get('/auth/google/callback')->assertRedirect('/hoje');

    expect(User::count())->toBe(1);
    $this->assertAuthenticatedAs($user);
});
