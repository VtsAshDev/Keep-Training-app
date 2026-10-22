<?php

namespace App\Models\Training;

use App\Models\User;
use Database\Factories\Training\WorkoutSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $workout_plan_id
 * @property Carbon $started_at
 * @property Carbon|null $finished_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['workout_plan_id', 'started_at', 'finished_at', 'notes'])]
class WorkoutSession extends Model
{
    /** @use HasFactory<WorkoutSessionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<WorkoutPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class, 'workout_plan_id');
    }

    /** @return HasMany<WorkoutSet, $this> */
    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }

    /** @param  Builder<WorkoutSession>  $query */
    public function scopeFinished(Builder $query): void
    {
        $query->whereNotNull('finished_at');
    }

    /** @param  Builder<WorkoutSession>  $query */
    public function scopeInProgress(Builder $query): void
    {
        $query->whereNull('finished_at');
    }

    public function isInProgress(): bool
    {
        return $this->finished_at === null;
    }
}
