<?php

use App\Actions\Training\GetNextWorkoutPlan;
use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutSession;
use App\Models\User;

function nextPlan(User $user): ?WorkoutPlan
{
    return app(GetNextWorkoutPlan::class)->handle($user);
}

/**
 * @return array<string, WorkoutPlan>
 */
function rotation(User $user, string $letters = 'ABCD'): array
{
    $plans = [];
    foreach (str_split($letters) as $index => $letter) {
        $plans[$letter] = WorkoutPlan::factory()->for($user)->create(['name' => "Ficha {$letter}", 'position' => $index + 1]);
    }

    return $plans;
}

function finishedWith(User $user, WorkoutPlan $plan, string $finishedAt): WorkoutSession
{
    return WorkoutSession::factory()->for($user)->create([
        'workout_plan_id' => $plan->id,
        'started_at' => now()->parse($finishedAt)->subHour(),
        'finished_at' => $finishedAt,
    ]);
}

beforeEach(fn () => $this->travelTo(now()->parse('2026-10-22 10:00')));

test('there is no suggestion without active plans', function () {
    $user = User::factory()->create();
    WorkoutPlan::factory()->for($user)->archived()->create();

    expect(nextPlan($user))->toBeNull();
});

test('suggests the first active plan when no workout was finished', function () {
    $user = User::factory()->create();
    $plans = rotation($user);

    expect(nextPlan($user)->id)->toBe($plans['A']->id);
});

test('suggests the plan after the last finished one', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    finishedWith($user, $plans['A'], '2026-10-19 08:00');
    finishedWith($user, $plans['B'], '2026-10-20 08:00');

    expect(nextPlan($user)->id)->toBe($plans['C']->id);
});

test('wraps around to the first plan after the last one', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    finishedWith($user, $plans['D'], '2026-10-20 08:00');

    expect(nextPlan($user)->id)->toBe($plans['A']->id);
});

test('missing days does not advance the rotation', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    finishedWith($user, $plans['B'], '2026-10-08 08:00');

    expect(nextPlan($user)->id)->toBe($plans['C']->id);
});

test('follows the plan actually done after a manual swap', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    finishedWith($user, $plans['A'], '2026-10-19 08:00');
    // B was suggested, but the user chose C.
    finishedWith($user, $plans['C'], '2026-10-20 08:00');

    expect(nextPlan($user)->id)->toBe($plans['D']->id);
});

test('a workout in progress does not move the rotation', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    finishedWith($user, $plans['A'], '2026-10-20 08:00');
    WorkoutSession::factory()->for($user)->inProgress()->create(['workout_plan_id' => $plans['C']->id]);

    expect(nextPlan($user)->id)->toBe($plans['B']->id);
});

test('uses the position of an archived last plan to find the next active one', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    finishedWith($user, $plans['B'], '2026-10-20 08:00');
    $plans['B']->update(['archived_at' => now()]);

    expect(nextPlan($user)->id)->toBe($plans['C']->id);
});

test('wraps around when the archived last plan was the last position', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    finishedWith($user, $plans['D'], '2026-10-20 08:00');
    $plans['D']->update(['archived_at' => now()]);

    expect(nextPlan($user)->id)->toBe($plans['A']->id);
});

test('skips archived plans in the rotation', function () {
    $user = User::factory()->create();
    $plans = rotation($user);
    $plans['B']->update(['archived_at' => now()]);
    finishedWith($user, $plans['A'], '2026-10-20 08:00');

    expect(nextPlan($user)->id)->toBe($plans['C']->id);
});

test('ignores plans and workouts of other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $plans = rotation($user);
    $otherPlans = rotation($other);
    finishedWith($other, $otherPlans['A'], '2026-10-21 08:00');

    expect(nextPlan($user)->id)->toBe($plans['A']->id);
});
