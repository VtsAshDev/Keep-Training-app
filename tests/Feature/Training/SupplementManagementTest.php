<?php

use App\Actions\Training\DeleteSupplement;
use App\Actions\Training\GetSupplements;
use App\Actions\Training\SaveSupplement;
use App\Enums\Training\UsageRule;
use App\Models\Training\Supplement;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('creates and updates a supplement', function () {
    $user = User::factory()->create();
    $data = ['name' => 'Creatina', 'dose' => 5, 'unit' => 'g', 'scheduled_time' => '08:00', 'usage_rule' => UsageRule::Daily];

    $supplement = app(SaveSupplement::class)->handle($user, null, $data);
    app(SaveSupplement::class)->handle($user, $supplement->id, [...$data, 'dose' => 3]);

    expect($supplement->fresh()->user_id)->toBe($user->id)
        ->and((float) $supplement->fresh()->dose)->toBe(3.0);
});

test('groups supplements by usage rule, daily first', function () {
    $user = User::factory()->create();
    Supplement::factory()->for($user)->trainingDays()->create(['name' => 'Whey']);
    Supplement::factory()->for($user)->create(['name' => 'Creatina']);
    Supplement::factory()->create();

    $groups = app(GetSupplements::class)->handle($user);

    expect($groups->keys()->all())->toBe(['daily', 'training_days'])
        ->and($groups->flatten()->count())->toBe(2);
});

test('refuses to change or delete a supplement of another user', function () {
    $supplement = Supplement::factory()->create(['name' => 'Creatina']);
    $intruder = User::factory()->create();

    expect(fn () => app(SaveSupplement::class)->handle($intruder, $supplement->id, ['name' => 'X', 'dose' => 1, 'unit' => 'g', 'usage_rule' => UsageRule::Daily]))
        ->toThrow(ModelNotFoundException::class);
    expect(fn () => app(DeleteSupplement::class)->handle($intruder, $supplement->id))
        ->toThrow(ModelNotFoundException::class);
    expect($supplement->fresh()?->name)->toBe('Creatina');
});

test('deletes a supplement', function () {
    $supplement = Supplement::factory()->create();

    app(DeleteSupplement::class)->handle($supplement->user, $supplement->id);

    expect(Supplement::count())->toBe(0);
});
