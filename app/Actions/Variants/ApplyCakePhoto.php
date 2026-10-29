<?php

namespace App\Actions\Variants;

use App\Models\CakeVariant;
use App\Services\Images\CutFaceFinder;
use App\Services\PublicUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Puts a cake photo under drawings: each gets its own WebP copy and the photo's pixel size as its view box.
 * When the empty cut is found in the photo, every layer is fitted into it and the drawing is published, so
 * customers see the photo with the default sponge and cream straight away. When it isn't, a drawing whose
 * shapes no longer fit is unpublished until the cut is marked by hand.
 */
class ApplyCakePhoto
{
    public function __construct(private PublicUploads $uploads, private CutFaceFinder $finder) {}

    /**
     * @param  iterable<CakeVariant>  $variants
     * @return bool whether the cut was found and the layers fitted
     */
    public function handle(UploadedFile $photo, iterable $variants): bool
    {
        $cut = $this->finder->find((string) $photo->getRealPath());

        foreach ($variants as $variant) {
            DB::transaction(function () use ($variant, $photo, $cut) {
                $old = $variant->base_image;
                $path = $this->uploads->storeAsWebp($photo, 'cake-bases');
                [$width, $height] = $this->uploads->size($path) ?? [$variant->view_box_width, $variant->view_box_height];
                $resized = $width !== $variant->view_box_width || $height !== $variant->view_box_height;

                $variant->update([
                    'base_image' => $path,
                    'view_box_width' => $width,
                    'view_box_height' => $height,
                    ...($cut ? ['cut_face' => [...$cut, 'sponge_share' => $variant->cut_face['sponge_share'] ?? $cut['sponge_share']]] : []),
                    ...(! $cut && $resized ? ['cut_face' => null, 'is_published' => false] : []),
                ]);
                $this->uploads->delete($old);

                if ($cut) {
                    $variant->fitSlotsToCutFace();
                    $variant->update(['is_published' => $variant->canPublish()]);
                }
            });
        }

        return $cut !== null;
    }
}
