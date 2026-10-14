<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CakePreview extends Model
{
    protected $fillable = [
        'sponge_flavor_id',
        'cream_flavor_id',
        'image',
    ];

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
