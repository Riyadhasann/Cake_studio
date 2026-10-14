<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_address',
        'status',
        'total',
        'delivery_date',
        'notes',
        'cancel_reason',
        'cancelled_at',
        'delivered_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'delivery_date' => 'date',
        'cancelled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];


    public function cakeCustomizations(): HasMany
    {
        return $this->hasMany(CakeCustomization::class);
    }
}
