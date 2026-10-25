<?php

namespace App\Services;

use GdImage;

/**
 * Turns a close-up photo of crumb or cream into a picture the 3D cake can repeat without seams:
 * crops it square, evens out light that falls more on one side, and cross-fades the edges so it tiles.
 */
class SeamlessTexture
{
    /** The largest side of the result; smaller photos keep their own size. */
    public const MAX_SIZE = 1024;

    /** Wider than this (width / height) is a strip, wrapped once around the cake: kept as it is. */
    public const STRIP_RATIO = 6;

    /** How much of the uneven light is removed (0 = none, 1 = all). */
    private const FLATTEN = 0.85;

    /**
     * The photo as WebP bytes. Tileable pictures are made square and seamless when $seamless is true; a strip
     * or a top picture ($seamless false) is only cropped or kept. Null when GD can't read or write the image.
     */
    public function make(string $contents, bool $seamless = true, bool $square = true): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return null;
        }

        imagepalettetotruecolor($source);
        $width = imagesx($source);
        $height = imagesy($source);

        $image = $this->isStrip($width, $height) || ! $square
            ? $this->fit($source, $width, $height)
            : $this->squareCrop($source, $width, $height);
        imagedestroy($source);

        if ($seamless && ! $this->isStrip(imagesx($image), imagesy($image))) {
            $this->flattenLight($image);
            $image = $this->crossFade($this->crossFade($image, horizontal: true), horizontal: false);
        }

        ob_start();
        $written = imagewebp($image, null, 88);
        $webp = (string) ob_get_clean();
        imagedestroy($image);

        return $written && $webp !== '' ? $webp : null;
    }

    public function isStrip(int $width, int $height): bool
    {
        return $height > 0 && $width / $height > self::STRIP_RATIO;
    }

    /** The centered square of the photo, scaled down to at most MAX_SIZE. */
    private function squareCrop(GdImage $source, int $width, int $height): GdImage
    {
        $side = min($width, $height);
        $size = min($side, self::MAX_SIZE);
        $square = $this->canvas($size, $size);

        imagecopyresampled($square, $source, 0, 0, intdiv($width - $side, 2), intdiv($height - $side, 2), $size, $size, $side, $side);

        return $square;
    }

    /** The whole photo, scaled down so its long side (its width, for a strip) is at most 4 × MAX_SIZE. */
    private function fit(GdImage $source, int $width, int $height): GdImage
    {
        $scale = min(1, (self::MAX_SIZE * 4) / max($width, $height));
        $w = max(1, (int) round($width * $scale));
        $h = max(1, (int) round($height * $scale));
        $image = $this->canvas($w, $h);

        imagecopyresampled($image, $source, 0, 0, 0, 0, $w, $h, $width, $height);

        return $image;
    }

    /**
     * Divides out the slow change in brightness (a 4 × 4 blur of the picture, scaled back up), so one side
     * isn't darker than the other and the repeats don't show as stripes of light and shade.
     */
    private function flattenLight(GdImage $image): void
    {
        $size = imagesx($image);
        $small = $this->canvas(4, 4);
        imagecopyresampled($small, $image, 0, 0, 0, 0, 4, 4, $size, imagesy($image));
        $light = $this->canvas($size, imagesy($image));
        imagecopyresampled($light, $small, 0, 0, 0, 0, $size, imagesy($image), 4, 4);
        imagedestroy($small);

        // The picture's average color, which every spot is brought toward.
        $one = $this->canvas(1, 1);
        imagecopyresampled($one, $image, 0, 0, 0, 0, 1, 1, $size, imagesy($image));
        $mean = $this->pixel($one, 0, 0);
        imagedestroy($one);

        for ($y = 0, $h = imagesy($image); $y < $h; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $pixel = $this->pixel($image, $x, $y);
                $local = $this->pixel($light, $x, $y);
                $out = [];

                foreach ([0, 1, 2] as $c) {
                    $gain = min(1.8, max(0.55, $mean[$c] / max(1, $local[$c])));
                    $out[$c] = $this->clamp($pixel[$c] * (1 + (($gain - 1) * self::FLATTEN)));
                }

                imagesetpixel($image, $x, $y, ($out[0] << 16) | ($out[1] << 8) | $out[2]);
            }
        }

        imagedestroy($light);
    }

    /**
     * Makes the picture repeat without a seam along one axis: near its edges it shows a copy shifted by half
     * (whose edges are the photo's middle, so they meet seamlessly), and in the middle the photo itself.
     */
    private function crossFade(GdImage $image, bool $horizontal): GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $half = $horizontal ? intdiv($w, 2) : intdiv($h, 2);

        $shifted = $this->canvas($w, $h);
        if ($horizontal) {
            imagecopy($shifted, $image, 0, 0, $half, 0, $w - $half, $h);
            imagecopy($shifted, $image, $w - $half, 0, 0, 0, $half, $h);
        } else {
            imagecopy($shifted, $image, 0, 0, 0, $half, $w, $h - $half);
            imagecopy($shifted, $image, 0, $h - $half, 0, 0, $w, $half);
        }

        $length = $horizontal ? $w : $h;
        $weights = [];
        for ($i = 0; $i < $length; $i++) {
            // 0 at the edges, 1 across the middle half, smooth in between.
            $t = min(1, (1 - abs((2 * $i / max(1, $length - 1)) - 1)) * 2);
            $weights[$i] = $t * $t * (3 - 2 * $t);
        }

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $weight = $weights[$horizontal ? $x : $y];

                if ($weight >= 1) {
                    continue;
                }

                $a = $this->pixel($image, $x, $y);
                $b = $this->pixel($shifted, $x, $y);
                $mix = fn (int $c) => $this->clamp($a[$c] * $weight + $b[$c] * (1 - $weight));
                imagesetpixel($image, $x, $y, ($mix(0) << 16) | ($mix(1) << 8) | $mix(2));
            }
        }

        imagedestroy($shifted);

        return $image;
    }

    private function canvas(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor(max(1, $width), max(1, $height));
        imagealphablending($image, false);

        return $image;
    }

    /**
     * The red, green and blue of one pixel (black outside the picture).
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private function pixel(GdImage $image, int $x, int $y): array
    {
        $color = imagecolorat($image, $x, $y);

        if ($color === false) {
            return [0, 0, 0];
        }

        return [($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF];
    }

    private function clamp(float $value): int
    {
        return (int) max(0, min(255, round($value)));
    }
}
