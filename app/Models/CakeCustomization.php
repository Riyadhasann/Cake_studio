<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CakeCustomization extends Model
{
    protected $fillable = [
        'order_id',
        'cake_size_id',
        'sponge_flavor_id', // mirrors layer 1, kept so the original columns stay valid
        'cream_flavor_id',  // mirrors layer 1, kept so the original columns stay valid
        'message',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cakeSize(): BelongsTo
    {
        return $this->belongsTo(CakeSize::class);
    }

    public function spongeFlavor(): BelongsTo
    {
        return $this->belongsTo(SpongeFlavor::class);
    }

    public function creamFlavor(): BelongsTo
    {
        return $this->belongsTo(CreamFlavor::class);
    }

    /** The layers are the source of truth, bottom layer first. */
    public function layers(): HasMany
    {
        return $this->hasMany(CakeCustomizationLayer::class)->orderBy('position');
    }

    /**
     * Plain arrays for views: [['position' => 1, 'sponge_name' => ..., 'cream_name' => ...], ...]
     * Same shape the cart uses, so one Blade component can show both.
     * Eager load layers.spongeFlavor and layers.creamFlavor to avoid extra queries.
     */
    protected function layerSummary(): Attribute
    {
        return Attribute::get(fn () => $this->layers->map(fn ($layer) => [
            'position' => $layer->position,
            'sponge_name' => $layer->spongeFlavor?->name ?? 'Unknown',
            'cream_name' => $layer->creamFlavor?->name ?? 'Unknown',
        ])->all());
    }
}
