<?php

namespace App\Models\Training;

use App\Models\User;
use Database\Factories\Training\SupplementLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $supplement_id
 * @property Carbon $date
 * @property bool $taken
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['supplement_id', 'date', 'taken'])]
class SupplementLog extends Model
{
    /** @use HasFactory<SupplementLogFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'taken' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Supplement, $this> */
    public function supplement(): BelongsTo
    {
        return $this->belongsTo(Supplement::class);
    }
}
