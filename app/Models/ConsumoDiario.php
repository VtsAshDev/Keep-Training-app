<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumoDiario extends Model
{
    protected $table = 'consumo_diario';

    protected $guarded = [];

    /** @return BelongsTo<Suplemento, $this> */
    public function suplemento(): BelongsTo
    {
        return $this->belongsTo(Suplemento::class);
    }
}
