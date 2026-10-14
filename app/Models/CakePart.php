<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CakePart extends Model
{
    protected $fillable = [
        'key',
        'name',
        'layer_image',
    ];
}
