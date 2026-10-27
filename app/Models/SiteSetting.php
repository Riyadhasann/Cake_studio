<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One of the shop's words or pictures, set on the admin "Shop & pictures" page (see App\Services\SiteSettings). */
class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];
}
