<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One photo of a cake design; with several, they are the views going around the cake. */
class CakeDesignFrame extends Model
{
    protected $fillable = [
        'cake_design_id',
        'image',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /** @return BelongsTo<CakeDesign, $this> */
    public function design(): BelongsTo
    {
        return $this->belongsTo(CakeDesign::class, 'cake_design_id');
    }
}
