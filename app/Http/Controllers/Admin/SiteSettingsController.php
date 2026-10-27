<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSiteImageRequest;
use App\Services\PublicUploads;
use App\Services\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\View;

/** The "Shop & pictures" page: the shop's name and contact details, the design page's heading, and every picture of the shop. */
class SiteSettingsController extends Controller
{
    /** What each picture is, for the page: [title, what it is for]. */
    public const IMAGE_LABELS = [
        'logo' => ['Logo', 'At the top of every page, next to the shop name. A square picture works best.'],
        'favicon' => ['Browser tab icon', 'The small icon in the browser tab. A square PNG or SVG, at least 32 × 32 pixels.'],
        'banner' => ['Title banner', 'The wide picture behind the heading on the design page. Keep its left side calm: the words sit there.'],
        'scene' => ['Cake background', 'The room every cake picture stands in: the 3D view, previews, designs and the cart.'],
    ];

    public function __construct(private SiteSettings $settings, private PublicUploads $uploads) {}

    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => $this->settings,
            'images' => self::IMAGE_LABELS,
            // The bundled pictures shown while the admin hasn't uploaded one (no logo: the drawn cake icon)
            'defaults' => [
                'logo' => null,
                'favicon' => asset('favicon.svg'),
                'banner' => Vite::asset('resources/images/title-banner.webp'),
                'scene' => Vite::asset('resources/images/cake-scene.svg'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[\d\s()+\-.]+$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'hero_title' => ['nullable', 'string', 'max:80'],
            'hero_text' => ['nullable', 'string', 'max:400'],
        ], [
            'phone.regex' => 'The phone number may only have digits, spaces and + - ( ).',
        ]);

        $this->settings->put($validated);

        return back()->with('success', 'Shop details saved.');
    }

    public function updateImage(UpdateSiteImageRequest $request, string $key): RedirectResponse
    {
        $svg = $request->sanitizedSvg();
        $old = $this->settings->imagePath($key);
        /** @var UploadedFile $file */
        $file = $request->file('image');

        $path = match (true) {
            $svg !== null => $this->uploads->storeContents($svg, 'cake-site', 'svg'),
            // The browser tab icon is kept as uploaded: not every browser shows a WebP icon.
            $key === 'favicon' => $this->uploads->store($file, 'cake-site'),
            default => $this->uploads->storeAsWebp($file, 'cake-site'),
        };

        $this->settings->put(["{$key}_image" => $path]);
        $this->uploads->delete($old);

        return back()->with('success', self::IMAGE_LABELS[$key][0].' saved.');
    }

    public function destroyImage(string $key): RedirectResponse
    {
        $this->uploads->delete($this->settings->imagePath($key));
        $this->settings->put(["{$key}_image" => null, ...($key === 'scene' ? ['scene_foot' => null] : [])]);

        return back()->with('success', self::IMAGE_LABELS[$key][0].' is back to the original.');
    }

    /** Where the cake stands in an uploaded background (percent from the top), so every cake picture stands on its table. */
    public function updateScene(Request $request): RedirectResponse
    {
        $validated = $request->validate(['scene_foot' => ['required', 'numeric', 'between:40,95']]);

        $this->settings->put(['scene_foot' => (string) round((float) $validated['scene_foot'] / 100, 4)]);

        return back()->with('success', 'Table position saved.');
    }
}
