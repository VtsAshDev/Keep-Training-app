<?php

namespace App\Models\Training;

use App\Enums\Training\UsageRule;
use App\Models\User;
use Database\Factories\Training\SupplementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $dose
 * @property string $unit
 * @property string|null $dose_notes
 * @property string|null $scheduled_time
 * @property UsageRule $usage_rule
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'dose', 'unit', 'dose_notes', 'scheduled_time', 'usage_rule', 'notes'])]
class Supplement extends Model
{
    /** @use HasFactory<SupplementFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dose' => 'decimal:2',
            'usage_rule' => UsageRule::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<SupplementLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(SupplementLog::class);
    }
}
