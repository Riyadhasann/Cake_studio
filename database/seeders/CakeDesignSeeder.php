<?php

namespace Database\Seeders;

use App\Models\CakeDesign;
use App\Services\PublicUploads;
use Illuminate\Database\Seeder;

/**
 * The four bundled cake designs (one front photo each, in database/seeders/images/designs/). Runs only while there are no designs,
 * so designs the admin added, renamed or priced are never touched. More photos per design (a full turn for 360°)
 * are added on /admin/designs.
 */
class CakeDesignSeeder extends Seeder
{
    /** @var array<string, string> name => photo in images/designs */
    public const DESIGNS = [
        'Caramel Chocolate Drip' => 'design-1.webp',
        'Black Forest Cherry' => 'design-2.webp',
        'Red Velvet Kisses' => 'design-3.webp',
        'Vanilla Chocolate Drip' => 'design-4.webp',
    ];

    /** @var array<string, array<string, float>> name => where the cake sits in its photo (see CakeDesign::DEFAULT_FIT), measured */
    public const FITS = [
        'Caramel Chocolate Drip' => ['x' => 0.492, 'r' => 0.42, 'top' => 0.344, 'bottom' => 0.652, 'top_curve' => 0.052, 'bottom_curve' => 0.076],
        'Black Forest Cherry' => ['x' => 0.506, 'r' => 0.386, 'top' => 0.356, 'bottom' => 0.644, 'top_curve' => 0.072, 'bottom_curve' => 0.088],
        'Red Velvet Kisses' => ['x' => 0.506, 'r' => 0.41, 'top' => 0.41, 'bottom' => 0.656, 'top_curve' => 0.084, 'bottom_curve' => 0.096],
        'Vanilla Chocolate Drip' => ['x' => 0.498, 'r' => 0.392, 'top' => 0.3, 'bottom' => 0.65, 'top_curve' => 0.06, 'bottom_curve' => 0.088],
    ];

    /**
     * name => toppings cut out of its photo (in images/designs/toppings), stood on the 3D cake's top: how many,
     * how far in (share of the radius), how wide (share of the radius) and the first one's place (degrees from the front).
     *
     * @var array<string, list<array{0: string, 1: int, 2: float, 3: float, 4: float}>>
     */
    public const TOPPINGS = [
        'Caramel Chocolate Drip' => [['design-1-meringue.webp', 9, 0.58, 0.34, 0.0], ['design-1-meringue.webp', 1, 0.0, 0.36, 0.0], ['design-1-caramel.webp', 9, 0.86, 0.19, 20.0]],
        'Black Forest Cherry' => [['design-2-cherries.webp', 1, 0.0, 0.44, 0.0]],
        'Red Velvet Kisses' => [['design-3-kiss.webp', 12, 0.8, 0.32, 0.0]],
        'Vanilla Chocolate Drip' => [['design-4-rosette.webp', 18, 0.87, 0.27, 0.0], ['design-4-rose.webp', 1, 0.45, 0.42, 138.0]],
    ];

    public function run(PublicUploads $uploads): void
    {
        if (CakeDesign::exists()) {
            return;
        }

        $order = 0;
        foreach (self::DESIGNS as $name => $file) {
            $photo = __DIR__.'/images/designs/'.$file;

            if (! is_file($photo)) {
                continue;
            }

            $design = CakeDesign::create([
                'name' => $name, 'extra_price' => 0, 'is_active' => true, 'sort_order' => $order++,
                'fit' => self::FITS[$name], 'toppings' => $this->toppings($name, $uploads),
            ]);
            $design->frames()->create([
                'image' => $uploads->storeContents((string) file_get_contents($photo), 'cake-designs', 'webp'),
                'position' => 0,
            ]);
        }
    }

    /**
     * Stores a design's topping cut-outs and returns them as CakeDesign::$toppings.
     *
     * @return list<array{image: string, count: int, ring: float, size: float, angle: float}>
     */
    public function toppings(string $name, PublicUploads $uploads): array
    {
        $toppings = [];
        foreach (self::TOPPINGS[$name] ?? [] as [$file, $count, $ring, $size, $angle]) {
            $picture = __DIR__.'/images/designs/toppings/'.$file;
            if (is_file($picture)) {
                $image = $uploads->storeContents((string) file_get_contents($picture), 'cake-designs', 'webp');
                $toppings[] = ['image' => $image, 'count' => $count, 'ring' => $ring, 'size' => $size, 'angle' => $angle];
            }
        }

        return $toppings;
    }
}
