<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlavorVariantImage extends Model
{
    protected $fillable = [
        'cake_variant_id',
        'layer_no',
        'flavor_type',
        'flavor_id',
        'image',
    ];

    protected $casts = [
        'layer_no' => 'integer',
        'flavor_id' => 'integer',
    ];

    /** @return BelongsTo<CakeVariant, $this> */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(CakeVariant::class, 'cake_variant_id');
    }

    /** The sponge or cream this photo shows, or null when it no longer exists. */
    public function flavor(): SpongeFlavor|CreamFlavor|null
    {
        return match ($this->flavor_type) {
            'sponge' => SpongeFlavor::find($this->flavor_id),
            'cream' => CreamFlavor::find($this->flavor_id),
            default => null,
        };
    }
}
