<?php

namespace App\Models\Training;

use App\Enums\Training\Equipment;
use App\Enums\Training\MuscleGroup;
use App\Models\User;
use Database\Factories\Training\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property MuscleGroup $muscle_group
 * @property Equipment|null $equipment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'muscle_group', 'equipment'])]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'muscle_group' => MuscleGroup::class,
            'equipment' => Equipment::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<WorkoutSet, $this> */
    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }

    /**
     * Default catalog plus the user's own exercises.
     *
     * @param  Builder<Exercise>  $query
     */
    public function scopeAvailableTo(Builder $query, User $user): void
    {
        $query->where(fn (Builder $query) => $query->whereNull('user_id')->orWhere('user_id', $user->id));
    }
}
