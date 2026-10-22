<?php

namespace App\Models\Training;

use Database\Factories\Training\WorkoutSetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $workout_session_id
 * @property int $exercise_id
 * @property int $set_number
 * @property string|null $weight_kg
 * @property int|null $reps
 * @property bool $completed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['exercise_id', 'set_number', 'weight_kg', 'reps', 'completed'])]
class WorkoutSet extends Model
{
    /** @use HasFactory<WorkoutSetFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'set_number' => 'integer',
            'weight_kg' => 'decimal:2',
            'reps' => 'integer',
            'completed' => 'boolean',
        ];
    }

    /** @return BelongsTo<WorkoutSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }

    /** @return BelongsTo<Exercise, $this> */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
