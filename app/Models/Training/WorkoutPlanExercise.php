<?php

namespace App\Models\Training;

use Database\Factories\Training\WorkoutPlanExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $workout_plan_id
 * @property int $exercise_id
 * @property int $position
 * @property int $sets
 * @property int $reps_min
 * @property int $reps_max
 * @property int $rest_seconds
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['exercise_id', 'position', 'sets', 'reps_min', 'reps_max', 'rest_seconds', 'notes'])]
class WorkoutPlanExercise extends Model
{
    /** @use HasFactory<WorkoutPlanExerciseFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'sets' => 'integer',
            'reps_min' => 'integer',
            'reps_max' => 'integer',
            'rest_seconds' => 'integer',
        ];
    }

    /** @return BelongsTo<WorkoutPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class, 'workout_plan_id');
    }

    /** @return BelongsTo<Exercise, $this> */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
