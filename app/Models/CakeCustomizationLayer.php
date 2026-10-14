<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CakeCustomizationLayer extends Model
{
    protected $fillable = [
        'cake_customization_id',
        'position',
        'sponge_flavor_id',
        'cream_flavor_id',
    ];

    /** @return BelongsTo<CakeCustomization, $this> */
    public function customization(): BelongsTo
    {
        return $this->belongsTo(CakeCustomization::class, 'cake_customization_id');
    }

    /** @return BelongsTo<SpongeFlavor, $this> */
    public function spongeFlavor(): BelongsTo
    {
        return $this->belongsTo(SpongeFlavor::class);
    }

    /** @return BelongsTo<CreamFlavor, $this> */
    public function creamFlavor(): BelongsTo
    {
        return $this->belongsTo(CreamFlavor::class);
    }
}
