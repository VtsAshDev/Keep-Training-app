<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suplemento extends Model
{
    protected $guarded = [];

    /** @return HasMany<ConsumoDiario, $this> */
    public function consumos(): HasMany
    {
        return $this->hasMany(ConsumoDiario::class);
    }
}
