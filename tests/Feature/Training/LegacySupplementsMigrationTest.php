<?php

use App\Actions\Training\GetTodaySupplements;
use App\Actions\Training\ToggleSupplementLog;
use App\Enums\Training\UsageRule;
use App\Models\Training\Supplement;
use App\Models\Training\SupplementLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function runLegacyCopy(): void
{
    (require database_path('migrations/2026_10_22_000007_copy_legacy_supplements.php'))->up();
}

/**
 * @param  array<string, mixed>  $overrides
 */
function legacySupplement(User $user, array $overrides = []): int
{
    return DB::table('suplementos')->insertGetId([
        'user_id' => $user->id,
        'nome' => 'Creatina',
        'quantidade_dose' => 5,
        'unidade_medida' => 'g',
        'informacao_adicional_dose' => 'com água',
        'horario_indicado' => 'Manhã',
        'regra_uso' => 'diario',
        'observacao' => null,
        'created_at' => '2026-08-06 10:00:00',
        'updated_at' => '2026-08-07 10:00:00',
        ...$overrides,
    ]);
}

function legacyLog(User $user, int $supplementId, string $date, bool $taken = true): void
{
    DB::table('consumo_diario')->insert([
        'user_id' => $user->id,
        'suplemento_id' => $supplementId,
        'data_consumo' => $date,
        'consumido' => $taken,
        'created_at' => "{$date} 08:00:00",
        'updated_at' => "{$date} 08:00:00",
    ]);
}

test('copies each supplement with its fields renamed', function () {
    $user = User::factory()->create();
    legacySupplement($user, ['informacao_adicional_dose' => '', 'observacao' => 'Não tomar com café']);

    runLegacyCopy();

    $supplement = Supplement::sole();
    expect($supplement->user_id)->toBe($user->id)
        ->and($supplement->name)->toBe('Creatina')
        ->and((float) $supplement->dose)->toBe(5.0)
        ->and($supplement->unit)->toBe('g')
        ->and($supplement->dose_notes)->toBeNull()
        ->and($supplement->scheduled_time)->toBe('Manhã')
        ->and($supplement->usage_rule)->toBe(UsageRule::Daily)
        ->and($supplement->notes)->toBe('Não tomar com café')
        ->and($supplement->created_at?->toDateTimeString())->toBe('2026-08-06 10:00:00');
});

test('converts the usage rules', function () {
    $user = User::factory()->create();
    legacySupplement($user, ['nome' => 'Whey', 'regra_uso' => 'apenas_treino']);
    legacySupplement($user, ['nome' => 'Estranho', 'regra_uso' => 'outro']);

    runLegacyCopy();

    expect(Supplement::where('name', 'Whey')->sole()->usage_rule)->toBe(UsageRule::TrainingDays)
        ->and(Supplement::where('name', 'Estranho')->sole()->usage_rule)->toBe(UsageRule::Daily);
});

test('copies the daily logs pointing to the new supplements', function () {
    $user = User::factory()->create();
    $legacy = legacySupplement($user);
    legacyLog($user, $legacy, '2026-08-06');
    legacyLog($user, $legacy, '2026-08-07', taken: false);

    runLegacyCopy();

    $supplement = Supplement::sole();
    $logs = SupplementLog::orderBy('date')->get();
    expect($logs)->toHaveCount(2)
        ->and($logs->pluck('supplement_id')->unique()->all())->toBe([$supplement->id])
        ->and($logs->map(fn ($log) => [$log->date->toDateString(), $log->taken])->all())
        ->toBe([['2026-08-06', true], ['2026-08-07', false]]);
});

test('skips logs made by another user on someone else\'s supplement', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $legacy = legacySupplement($owner);
    legacyLog($intruder, $legacy, '2026-08-06');

    runLegacyCopy();

    expect(Supplement::count())->toBe(1)
        ->and(SupplementLog::count())->toBe(0);
});

test('keeps each user with their own supplements', function () {
    [$ana, $bia] = User::factory()->count(2)->create();
    legacySupplement($ana, ['nome' => 'Creatina']);
    legacySupplement($bia, ['nome' => 'Vitamina D']);

    runLegacyCopy();

    expect($ana->supplements()->pluck('name')->all())->toBe(['Creatina'])
        ->and($bia->supplements()->pluck('name')->all())->toBe(['Vitamina D']);
});

test('copied logs work with the new rules and the legacy tables stay untouched', function () {
    $this->travelTo(now()->parse('2026-10-22 10:00'));
    $user = User::factory()->create();
    $legacy = legacySupplement($user);
    legacyLog($user, $legacy, '2026-10-22');

    runLegacyCopy();

    $supplement = Supplement::sole();
    expect(app(GetTodaySupplements::class)->handle($user)->first()->taken)->toBeTrue();

    // Toggling finds the copied log instead of hitting the unique constraint.
    expect(app(ToggleSupplementLog::class)->handle($user, $supplement->id)->taken)->toBeFalse()
        ->and(SupplementLog::count())->toBe(1)
        ->and(DB::table('suplementos')->count())->toBe(1)
        ->and(DB::table('consumo_diario')->count())->toBe(1);
});

test('does nothing without legacy data', function () {
    runLegacyCopy();

    expect(Supplement::count())->toBe(0);
});
