<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreamFlavor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'extra_price',
        'swatch_image',
        'color',
        'layer_image',
        'is_active',
        'is_default',
        'texture_image',
        'sort_order',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Menu order set by the admin, oldest first when two share a position.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function previews(): HasMany
    {
        return $this->hasMany(CakePreview::class);
    }

    public function customizations(): HasMany
    {
        return $this->hasMany(CakeCustomization::class);
    }
}
