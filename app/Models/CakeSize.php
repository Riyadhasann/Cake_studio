<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CakeSize extends Model
{
    protected $fillable = [
        'label',
        'weight_kg',
        'price',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    /**
     * Lightest first, as customers see them on the design page.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('weight_kg')->orderBy('id');
    }

    /** @return HasMany<CakeCustomization, $this> */
    public function cakeCustomizations(): HasMany
    {
        return $this->hasMany(CakeCustomization::class);
    }
}
