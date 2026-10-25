<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A finished cake design customers can pick: its name, extra price and photos (one, or a full turn for 360°). */
class CakeDesign extends Model
{
    /**
     * Where the cake sits in the cover photo when nobody fitted it (the bundled photos' average), so the 3D cake can
     * wear the photo: the middle (x) and half width (r) of the cake as shares of the photo's width; the side's top and
     * bottom edge straight in front, and how far those edges curve up towards the sides, as shares of its height.
     */
    public const DEFAULT_FIT = ['x' => 0.5, 'r' => 0.4, 'top' => 0.35, 'bottom' => 0.65, 'top_curve' => 0.065, 'bottom_curve' => 0.085];

    protected $fillable = [
        'name',
        'extra_price',
        'is_active',
        'sort_order',
        'fit',
        'toppings',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'fit' => 'array',
        'toppings' => 'array',
    ];

    /**
     * The toppings stood on the 3D cake's top: [image (path inside public/), count, ring, size, angle], see
     * SaveCakeDesignToppingRequest.
     *
     * @return list<array{image: string, count: int, ring: float, size: float, angle: float}>
     */
    public function toppingList(): array
    {
        $list = [];
        foreach (is_array($this->toppings) ? $this->toppings : [] as $topping) {
            if (is_array($topping) && is_string($topping['image'] ?? null)) {
                $list[] = [
                    'image' => $topping['image'],
                    'count' => (int) ($topping['count'] ?? 1),
                    'ring' => (float) ($topping['ring'] ?? 0),
                    'size' => (float) ($topping['size'] ?? 0.3),
                    'angle' => (float) ($topping['angle'] ?? 0),
                ];
            }
        }

        return $list;
    }

    /**
     * The saved fit, with the usual place for anything not saved.
     *
     * @return array<string, float>
     */
    public function fitOrDefault(): array
    {
        $fit = array_intersect_key(is_array($this->fit) ? $this->fit : [], self::DEFAULT_FIT);

        return array_map(floatval(...), [...self::DEFAULT_FIT, ...$fit]);
    }

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

    /**
     * The photos in spin order; the first is the cover.
     *
     * @return HasMany<CakeDesignFrame, $this>
     */
    public function frames(): HasMany
    {
        return $this->hasMany(CakeDesignFrame::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<CakeCustomization, $this> */
    public function customizations(): HasMany
    {
        return $this->hasMany(CakeCustomization::class);
    }
}
