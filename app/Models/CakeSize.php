<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CakeSize extends Model
{
    Use HasFactory;


    protected $fillable = [
        'label',
        'price',
    

    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];


    public function cakeCustomizations(): HasMany
    {
        return $this->hasMany(CakeCustomization::class);
    }
}
