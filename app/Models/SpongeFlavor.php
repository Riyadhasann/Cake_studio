<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpongeFlavor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'extra_price',
        'swatch_image',
        'color',
        'layer_image',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function previews(): HasMany
    {
        return $this->hasMany(CakePreview::class);
    }

    public function customizations(): HasMany
    {
        return $this->hasMany(CakeCustomization::class);
    }
}
