<?php

use App\Models\CakeVariant;
use App\Models\CakeVariantSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers shared by the feature tests
|--------------------------------------------------------------------------
*/

/** A variant with a sponge and a cream slot for every layer. */
function completeVariant(int $layers = 2): CakeVariant
{
    $variant = CakeVariant::create(['layers' => $layers, 'view_box_width' => 700, 'view_box_height' => 196 * $layers]);

    foreach (range(1, $layers) as $layerNo) {
        foreach (CakeVariantSlot::TYPES as $type) {
            $variant->slots()->create(['layer_no' => $layerNo, 'type' => $type, 'svg_path' => 'M0 0H10V10H0Z']);
        }
    }

    return $variant->fresh();
}
