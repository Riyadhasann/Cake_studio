<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CakeVariant extends Model
{
    public const LAYER_COUNTS = [2, 3, 4];

    /** View box units per texture tile: about 4 tiles across a 700-wide cake. */
    public const TEXTURE_TILE = 160;

    protected $fillable = [
        'layers',
        'view_box_width',
        'view_box_height',
        'base_svg',
        'shading_image',
        'is_published',
    ];

    protected $casts = [
        'layers' => 'integer',
        'view_box_width' => 'integer',
        'view_box_height' => 'integer',
        'is_published' => 'boolean',
    ];

    /** @return HasMany<CakeVariantSlot, $this> */
    public function slots(): HasMany
    {
        return $this->hasMany(CakeVariantSlot::class)->orderBy('layer_no')->orderBy('type');
    }

    /** @return HasMany<FlavorVariantImage, $this> */
    public function flavorImages(): HasMany
    {
        return $this->hasMany(FlavorVariantImage::class)->orderBy('layer_no')->orderBy('flavor_type');
    }

    /**
     * Only published variants ever reach the design page.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function canPublish(): bool
    {
        return $this->publishProblems() === [];
    }

    /**
     * Why this variant can't be shown to customers yet, in words for the admin. Empty when it can.
     *
     * @return list<string>
     */
    public function publishProblems(): array
    {
        $problems = [];

        if (! in_array($this->layers, self::LAYER_COUNTS, true)) {
            $problems[] = 'The layer count must be 2, 3 or 4.';
        }

        if ($this->view_box_width < 1 || $this->view_box_height < 1) {
            $problems[] = 'The view box width and height must be set.';
        }

        $slots = $this->slots;
        $expected = 2 * $this->layers;

        if ($slots->count() !== $expected) {
            $problems[] = "A {$this->layers}-layer cake needs exactly {$expected} slots (a sponge and a cream per layer); it has {$slots->count()}.";
        }

        foreach ($slots as $slot) {
            if ($slot->layer_no < 1 || $slot->layer_no > $this->layers) {
                $problems[] = "Slot \"{$slot->label()}\" is outside layers 1 to {$this->layers}.";
            }

            if (! in_array($slot->type, CakeVariantSlot::TYPES, true)) {
                $problems[] = "Slot #{$slot->id} has an unknown type \"{$slot->type}\".";
            }

            if (trim((string) $slot->svg_path) === '') {
                $problems[] = "Slot \"{$slot->label()}\" has no shape.";
            }
        }

        foreach (range(1, max(1, $this->layers)) as $layerNo) {
            foreach (CakeVariantSlot::TYPES as $type) {
                $count = $slots->where('layer_no', $layerNo)->where('type', $type)->count();

                if ($count === 0 && $layerNo <= $this->layers) {
                    $problems[] = "Layer {$layerNo} {$type} is missing.";
                } elseif ($count > 1) {
                    $problems[] = "Layer {$layerNo} {$type} is defined {$count} times.";
                }
            }
        }

        return array_values(array_unique($problems));
    }
}
