<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Copies the Keep Training supplements (suplementos, consumo_diario) into
 * supplements and supplement_logs. The legacy tables stay untouched; they are
 * dropped in a later migration, once this copy is validated with production data.
 */
return new class extends Migration
{
    private const USAGE_RULES = [
        'diario' => 'daily',
        'apenas_treino' => 'training_days',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('suplementos') || ! Schema::hasTable('consumo_diario')) {
            return;
        }

        DB::transaction(function () {
            /** @var array<int, array{id: int, user_id: int}> $copied legacy id => new supplement */
            $copied = [];
            $unknownRules = 0;

            foreach (DB::table('suplementos')->orderBy('id')->get() as $legacy) {
                $rule = self::USAGE_RULES[$legacy->regra_uso] ?? null;
                $unknownRules += $rule === null ? 1 : 0;

                $id = DB::table('supplements')->insertGetId([
                    'user_id' => $legacy->user_id,
                    'name' => $legacy->nome,
                    'dose' => $legacy->quantidade_dose,
                    'unit' => $legacy->unidade_medida,
                    'dose_notes' => self::blankToNull($legacy->informacao_adicional_dose),
                    'scheduled_time' => self::blankToNull($legacy->horario_indicado),
                    'usage_rule' => $rule ?? 'daily',
                    'notes' => self::blankToNull($legacy->observacao),
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);

                $copied[(int) $legacy->id] = ['id' => (int) $id, 'user_id' => (int) $legacy->user_id];
            }

            $logs = 0;
            $foreign = 0;

            foreach (DB::table('consumo_diario')->orderBy('id')->get() as $legacy) {
                $supplement = $copied[(int) $legacy->suplemento_id] ?? null;

                // The old toggle did not check the owner, so a log may belong to someone
                // else's supplement. Those logs are invalid and are not copied.
                if ($supplement === null || $supplement['user_id'] !== (int) $legacy->user_id) {
                    $foreign++;

                    continue;
                }

                DB::table('supplement_logs')->insert([
                    'user_id' => $supplement['user_id'],
                    'supplement_id' => $supplement['id'],
                    // Same format the model's date cast writes.
                    'date' => Carbon::parse($legacy->data_consumo)->startOfDay()->format('Y-m-d H:i:s'),
                    'taken' => (bool) $legacy->consumido,
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);
                $logs++;
            }

            Log::info('Legacy supplements copied', [
                'supplements' => count($copied),
                'logs' => $logs,
                'skipped_foreign_logs' => $foreign,
                'unknown_usage_rules_as_daily' => $unknownRules,
            ]);
        });
    }

    /**
     * Irreversible on purpose: rolling back would delete supplements the user may have
     * changed since. Restore the backup taken before the migration instead.
     */
    public function down(): void
    {
        //
    }

    private static function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
};
