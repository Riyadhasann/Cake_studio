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

    public function customization(): BelongsTo
    {
        return $this->belongsTo(CakeCustomization::class, 'cake_customization_id');
    }

    public function spongeFlavor(): BelongsTo
    {
        return $this->belongsTo(SpongeFlavor::class);
    }

    public function creamFlavor(): BelongsTo
    {
        return $this->belongsTo(CreamFlavor::class);
    }
}
