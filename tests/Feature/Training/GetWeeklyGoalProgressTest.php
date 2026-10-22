<?php

use App\Actions\Training\GetWeeklyGoalProgress;
use App\Data\Training\WeeklyGoalProgress;
use App\Models\Training\WorkoutSession;
use App\Models\User;

// Thursday. The week runs from Monday 19 to Sunday 25 of October.
beforeEach(fn () => $this->travelTo(now()->parse('2026-10-22 10:00')));

function workoutsOn(User $user, string ...$dates): void
{
    foreach ($dates as $date) {
        WorkoutSession::factory()->for($user)->create([
            'started_at' => $date,
            'finished_at' => now()->parse($date)->addHour(),
        ]);
    }
}

function progress(User $user): WeeklyGoalProgress
{
    return app(GetWeeklyGoalProgress::class)->handle($user);
}

test('counts the finished workouts of the current week', function () {
    $user = User::factory()->create(['weekly_workout_goal' => 4]);
    workoutsOn($user, '2026-10-18 10:00', '2026-10-19 07:00', '2026-10-20 07:00');
    WorkoutSession::factory()->for($user)->inProgress()->create(['started_at' => '2026-10-22 09:00']);

    $progress = progress($user);

    expect($progress->completed)->toBe(2)
        ->and($progress->goal)->toBe(4)
        ->and($progress->met)->toBeFalse();
});

test('the goal is met when the count reaches the user goal', function () {
    $user = User::factory()->create(['weekly_workout_goal' => 3]);
    workoutsOn($user, '2026-10-19 07:00', '2026-10-20 07:00', '2026-10-21 07:00');

    expect(progress($user)->met)->toBeTrue();
});

test('the week starts on Monday at midnight local time', function () {
    $user = User::factory()->create();
    workoutsOn($user, '2026-10-18 23:30', '2026-10-19 00:30');

    expect(progress($user)->completed)->toBe(1);
});

test('the current week only adds to the streak once met', function () {
    $user = User::factory()->create(['weekly_workout_goal' => 2]);
    workoutsOn($user,
        '2026-10-05 07:00', '2026-10-07 07:00',
        '2026-10-12 07:00', '2026-10-14 07:00',
        '2026-10-20 07:00',
    );

    expect(progress($user)->streak)->toBe(2);

    workoutsOn($user, '2026-10-21 07:00');

    expect(progress($user)->streak)->toBe(3);
});

test('a missed week breaks the streak', function () {
    $user = User::factory()->create(['weekly_workout_goal' => 2]);
    workoutsOn($user,
        '2026-09-28 07:00', '2026-09-30 07:00',
        '2026-10-05 07:00',
        '2026-10-12 07:00', '2026-10-14 07:00',
    );

    expect(progress($user)->streak)->toBe(1);
});

test('the streak is zero when last week was missed and this week is not met yet', function () {
    $user = User::factory()->create(['weekly_workout_goal' => 2]);
    workoutsOn($user, '2026-10-05 07:00', '2026-10-07 07:00', '2026-10-20 07:00');

    expect(progress($user)->streak)->toBe(0);
});

test('days show done, today and pending from Monday to Sunday', function () {
    $user = User::factory()->create();
    workoutsOn($user, '2026-10-19 07:00', '2026-10-20 07:00');

    expect(progress($user)->days)->toBe(['done', 'done', 'pending', 'today', 'pending', 'pending', 'pending']);
});

test('today shows as done once a workout is finished', function () {
    $user = User::factory()->create();
    workoutsOn($user, '2026-10-22 07:00');

    expect(progress($user)->days[3])->toBe('done');
});

test('workouts of other users do not count', function () {
    workoutsOn(User::factory()->create(), '2026-10-19 07:00', '2026-10-20 07:00');

    expect(progress(User::factory()->create())->completed)->toBe(0);
});
