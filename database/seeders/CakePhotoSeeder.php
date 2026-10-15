<?php

namespace Database\Seeders;

use App\Models\CakeVariant;
use App\Services\PublicUploads;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Puts the bundled cake photo (a chocolate cake with an empty wedge cut, from docs/base_cake_image.png) under
 * every drawing that is still bare: no photo, no base SVG, no shading and no flavor photos, as CakeVariantSeeder
 * leaves them. Its slots are fitted to the cut, so the design page opens on the photo with the default
 * sponge and cream in every layer. A drawing the admin has worked on is never touched.
 */
class CakePhotoSeeder extends Seeder
{
    public const PHOTO = __DIR__.'/images/base-cake.webp';

    /** The cut face in the photo's pixels, a little outside the empty area so no gap shows. */
    public const CUT_FACE = [
        'top' => [[218, 371], [555, 219], [901, 374]],
        'bottom' => [[221, 801], [555, 647], [904, 789]],
        'sponge_share' => 0.72,
    ];

    public function run(PublicUploads $uploads): void
    {
        $size = @getimagesize(self::PHOTO);

        if (! $size) {
            return;
        }

        $bare = CakeVariant::whereNull('base_image')->whereNull('base_svg')->whereNull('shading_image')
            ->whereDoesntHave('flavorImages')
            ->get();

        foreach ($bare as $variant) {
            DB::transaction(function () use ($variant, $uploads, $size) {
                $variant->update([
                    'base_image' => $uploads->storeContents((string) file_get_contents(self::PHOTO), 'cake-bases', 'webp'),
                    'view_box_width' => $size[0],
                    'view_box_height' => $size[1],
                    'cut_face' => self::CUT_FACE,
                ]);
                $variant->fitSlotsToCutFace();
                $variant->update(['is_published' => $variant->canPublish()]);
            });
        }
    }
}
