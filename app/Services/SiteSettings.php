<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Database\QueryException;

/**
 * The shop's words and pictures, set by the admin on the "Shop & pictures" page. Anything not set falls back to
 * config/studio.php (STUDIO_* env) for the words and to the bundled pictures in resources/images for the pictures.
 * Read once per request.
 */
class SiteSettings
{
    /** The words the admin can change, with what each is for. */
    public const TEXTS = [
        'name' => 'Shop name',
        'tagline' => 'Tagline',
        'phone' => 'Phone',
        'email' => 'Email',
        'hero_title' => 'Heading on the design page',
        'hero_text' => 'Text under the heading',
    ];

    /** The pictures the admin can replace. */
    public const IMAGES = ['logo', 'favicon', 'banner', 'scene'];

    /** Where the cake stands in the bundled scene (resources/images/cake-scene.svg, 300 x 400): y = 298 of 400. */
    public const DEFAULT_SCENE_FOOT = 0.745;

    public const DEFAULT_SCENE_ASPECT = 0.75;

    /** @var array<string, string|null>|null */
    private ?array $values = null;

    public function __construct(private PublicUploads $uploads) {}

    public function get(string $key): ?string
    {
        $value = $this->all()[$key] ?? null;

        if ($value !== null && $value !== '') {
            return $value;
        }

        return match ($key) {
            'name' => (string) config('studio.name'),
            'tagline' => (string) config('studio.tagline'),
            'phone', 'email' => config("studio.{$key}"),
            'hero_title' => 'Design your cake',
            default => null,
        };
    }

    /** Whether the admin set this one (rather than it falling back to the default). */
    public function has(string $key): bool
    {
        $value = $this->all()[$key] ?? null;

        return $value !== null && $value !== '';
    }

    /** The URL of an uploaded picture, or null when the bundled default is used (or its file is missing). */
    public function imageUrl(string $key): ?string
    {
        $path = $this->all()["{$key}_image"] ?? null;

        return $path && $this->uploads->file($path) ? asset($path) : null;
    }

    public function imagePath(string $key): ?string
    {
        return $this->all()["{$key}_image"] ?? null;
    }

    /** How far down the scene picture the cake's stand stands, as a share of its height. */
    public function sceneFoot(): float
    {
        $foot = (float) ($this->all()['scene_foot'] ?? 0);

        return $this->imageUrl('scene') && $foot > 0 ? $foot : self::DEFAULT_SCENE_FOOT;
    }

    /** The scene picture's width per height. */
    public function sceneAspect(): float
    {
        $size = $this->imageUrl('scene') ? $this->uploads->size($this->imagePath('scene')) : null;

        return $size ? $size[0] / $size[1] : self::DEFAULT_SCENE_ASPECT;
    }

    /** @param  array<string, string|null>  $values */
    public function put(array $values): void
    {
        foreach ($values as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->values = null;
    }

    /** @return array<string, string|null> */
    private function all(): array
    {
        if ($this->values === null) {
            try {
                $this->values = SiteSetting::pluck('value', 'key')->all();
            } catch (QueryException) {
                // Before the first migration (e.g. while installing) there is no table yet.
                $this->values = [];
            }
        }

        return $this->values;
    }
}
