<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\relations\BelongsTo;


class CakePreview extends Model
{
    protected $fillable = [
        'sponge_flavor_id',
        'cream_flavor_id',
        'image',
    ];


    public function spongeFlavor(): BelongsTo
    {
        return $this->belongsTo(SpongeFlavor::class);
    }

    public function creamFlavor(): BelongsTo
    {
        return $this->belongsTo(CreamFlavor::class);
    }
}
