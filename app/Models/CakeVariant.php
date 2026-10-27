<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array{top: list<array{0: float|int, 1: float|int}>, bottom: list<array{0: float|int, 1: float|int}>, sponge_share: float|int}|null $cut_face
 */
class CakeVariant extends Model
{
    /** The layer counts every shop offers; the admin can add more, up to MAX_LAYERS, on the Cake shapes page. */
    public const LAYER_COUNTS = [2, 3, 4];

    public const MIN_LAYERS = 2;

    public const MAX_LAYERS = 8;

    protected $fillable = [
        'layers',
        'view_box_width',
        'view_box_height',
        'base_svg',
        'base_image',
        'cut_face',
        'shading_image',
        'is_published',
    ];

    protected $casts = [
        'cut_face' => 'array',
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

    /**
     * The layer counts customers can pick: the built-in ones plus every count the admin added a drawing for.
     *
     * @return non-empty-list<int>
     */
    public static function offeredLayerCounts(): array
    {
        $added = self::query()->whereBetween('layers', [self::MIN_LAYERS, self::MAX_LAYERS])->pluck('layers')->map(fn ($layers) => (int) $layers)->all();
        $counts = array_unique([...self::LAYER_COUNTS, ...$added]);
        sort($counts);

        return $counts;
    }

    /** A layer count the admin added (not one of the built-in ones), so it can be removed again. */
    public function isExtra(): bool
    {
        return ! in_array($this->layers, self::LAYER_COUNTS, true);
    }

    /**
     * Replaces every slot with the bands of the marked cut face. Does nothing without one.
     */
    public function fitSlotsToCutFace(): void
    {
        if (! $this->cut_face) {
            return;
        }

        $this->slots()->delete();

        foreach (CakeVariantSlot::cutFacePaths($this->cut_face, $this->layers) as $layerNo => $paths) {
            foreach ($paths as $type => $path) {
                $this->slots()->create(['layer_no' => $layerNo, 'type' => $type, 'svg_path' => $path]);
            }
        }

        $this->unsetRelation('slots');
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

        if ($this->layers < self::MIN_LAYERS || $this->layers > self::MAX_LAYERS) {
            $problems[] = 'The layer count must be from '.self::MIN_LAYERS.' to '.self::MAX_LAYERS.'.';
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
