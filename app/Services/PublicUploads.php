<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Admin uploads on the public disk. Paths are saved as "storage/<folder>/<file>", a path inside public/
 * (through the storage link) that views turn into a URL with asset().
 */
class PublicUploads
{
    /** The only folders this app writes to, and so the only ones it ever deletes from. */
    public const FOLDERS = ['cake-layers', 'cake-photos', 'cake-shading', 'cake-bases', 'cake-textures', 'cake-designs', 'cake-site'];

    /** Larger images are stored as uploaded: decoding them could exhaust PHP's memory (about 4 bytes per pixel). */
    private const MAX_CONVERT_PIXELS = 2048 * 2048;

    /** Stores the file under a random name and returns its path inside public/. */
    public function store(UploadedFile $file, string $folder): string
    {
        if (! in_array($folder, self::FOLDERS, true)) {
            throw new \InvalidArgumentException("Uploads can't be stored in \"{$folder}\".");
        }

        return 'storage/'.$file->store($folder, 'public');
    }

    /** Saves already-cleaned file contents (e.g. a sanitized SVG) under a random name and returns its path inside public/. */
    public function storeContents(string $contents, string $folder, string $extension): string
    {
        if (! in_array($folder, self::FOLDERS, true) || preg_match('/^[a-z0-9]{2,5}$/', $extension) !== 1) {
            throw new \InvalidArgumentException("Uploads can't be stored in \"{$folder}\" as .{$extension}.");
        }

        $path = $folder.'/'.Str::random(40).'.'.$extension;
        Storage::disk('public')->put($path, $contents);

        return 'storage/'.$path;
    }

    /**
     * Stores the image as WebP (smaller, keeps transparency) and returns its path inside public/.
     * Keeps the original file when it is already WebP, very large, or this PHP can't convert it.
     */
    public function storeAsWebp(UploadedFile $file, string $folder, int $quality = 85): string
    {
        $size = @getimagesize((string) $file->getRealPath());
        $convertible = function_exists('imagewebp')
            && $size !== false
            && in_array($size[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)
            && $size[0] * $size[1] <= self::MAX_CONVERT_PIXELS;

        $image = $convertible ? @imagecreatefromstring((string) $file->get()) : false;

        if ($image === false) {
            return $this->store($file, $folder);
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $written = imagewebp($image, null, $quality);
        $webp = (string) ob_get_clean();
        imagedestroy($image);

        if (! $written || $webp === '' || ! in_array($folder, self::FOLDERS, true)) {
            return $this->store($file, $folder);
        }

        $path = $folder.'/'.Str::random(40).'.webp';
        Storage::disk('public')->put($path, $webp);

        return 'storage/'.$path;
    }

    /** Deletes a file this app stored. A path placed in public/ by hand is never touched. */
    public function delete(?string $path): void
    {
        if (! $path || str_contains($path, '..')) {
            return;
        }

        foreach (self::FOLDERS as $folder) {
            if (str_starts_with($path, "storage/{$folder}/")) {
                Storage::disk('public')->delete(substr($path, strlen('storage/')));

                return;
            }
        }
    }

    /**
     * The pixel size of an image inside public/, or null when it is missing or not an image.
     *
     * @return array{0: int, 1: int}|null
     */
    public function size(?string $path): ?array
    {
        $file = $this->file($path);

        if (! $file) {
            return null;
        }

        if (str_ends_with(strtolower($file), '.svg')) {
            return $this->svgSize((string) file_get_contents($file, length: 8192));
        }

        $info = @getimagesize($file);

        return $info && $info[0] > 0 && $info[1] > 0 ? [$info[0], $info[1]] : null;
    }

    /** The file behind a path inside public/, or null when it is missing. */
    public function file(?string $path): ?string
    {
        if (! $path || str_contains($path, '..')) {
            return null;
        }

        // Read uploads from the disk itself rather than through the storage link.
        $file = str_starts_with($path, 'storage/')
            ? Storage::disk('public')->path(substr($path, strlen('storage/')))
            : public_path($path);

        return is_file($file) ? $file : null;
    }

    /**
     * An SVG's size from its viewBox, else its width and height (in user units, rounded).
     *
     * @return array{0: int, 1: int}|null
     */
    private function svgSize(string $start): ?array
    {
        if (preg_match('/<svg\b[^>]*>/i', $start, $tag) !== 1) {
            return null;
        }

        $attribute = fn (string $name): ?string => preg_match('/\s'.$name.'\s*=\s*["\']([^"\']*)["\']/i', $tag[0], $m) === 1 ? $m[1] : null;
        $viewBox = preg_split('/[\s,]+/', trim((string) $attribute('viewBox')));

        [$width, $height] = $viewBox !== false && count($viewBox) === 4
            ? [(float) $viewBox[2], (float) $viewBox[3]]
            : [(float) $attribute('width'), (float) $attribute('height')];

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        // Scale tiny view boxes up so rounding to whole units keeps the proportions.
        $scale = max(1, 1000 / max($width, $height));

        return [max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale))];
    }
}
