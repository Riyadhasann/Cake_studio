<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CakeDesign;
use App\Models\CakeSize;
use App\Models\CakeVariant;
use App\Models\CreamFlavor;
use App\Models\Order;
use App\Models\SpongeFlavor;
use App\Services\SiteSettings;
use Illuminate\View\View;

/** The admin home: today's work, and a checklist of what the shop still needs. */
class DashboardController extends Controller
{
    public function __invoke(SiteSettings $settings): View
    {
        $today = now()->toDateString();
        $flavors = SpongeFlavor::where('is_active', true)->get()->concat(CreamFlavor::where('is_active', true)->get());

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Orders waiting', 'value' => Order::where('status', 'pending')->count(), 'route' => 'admin.orders.index'],
                ['label' => 'To deliver today', 'value' => Order::where('status', 'pending')->whereDate('delivery_date', $today)->count(), 'route' => 'admin.orders.index'],
                ['label' => 'Flavors on the menu', 'value' => $flavors->count(), 'route' => 'admin.cake-visuals.index'],
                ['label' => 'Designs on sale', 'value' => CakeDesign::where('is_active', true)->count(), 'route' => 'admin.designs.index'],
            ],
            // [what, done, why it matters, where to fix it, required for the shop to look right]
            'checklist' => [
                [
                    'label' => 'Add at least one size with a price',
                    'done' => CakeSize::exists(),
                    'hint' => 'The size sets the price of a cake.',
                    'route' => 'admin.sizes.index',
                    'required' => true,
                ],
                [
                    'label' => 'Upload the cake photo',
                    'done' => CakeVariant::whereNotNull('base_image')->exists(),
                    'hint' => 'The cut cake customers see with their chosen layers.',
                    'route' => 'admin.cake-visuals.index',
                    'required' => true,
                ],
                [
                    'label' => 'Give every flavor a picture',
                    'done' => $flavors->every(fn ($flavor) => $flavor->layer_image !== null),
                    'hint' => ($missing = $flavors->whereNull('layer_image')->count())
                        ? "{$missing} ".str('flavor')->plural($missing).' still '.($missing === 1 ? 'shows' : 'show').' a plain color.'
                        : 'Each sponge and cream shows its own picture.',
                    'route' => 'admin.cake-visuals.index',
                    'required' => true,
                ],
                [
                    'label' => 'Add a cake design',
                    'done' => CakeDesign::exists(),
                    'hint' => 'Finished looks customers can pick for the outside of the cake.',
                    'route' => 'admin.designs.index',
                    'required' => false,
                ],
                [
                    'label' => 'Upload your logo',
                    'done' => $settings->imageUrl('logo') !== null,
                    'hint' => 'Shown at the top of every page.',
                    'route' => 'admin.settings.index',
                    'required' => false,
                ],
                [
                    'label' => 'Add a phone number or email',
                    'done' => (bool) ($settings->get('phone') || $settings->get('email')),
                    'hint' => 'Shown at the bottom of every page so customers can reach you.',
                    'route' => 'admin.settings.index',
                    'required' => false,
                ],
            ],
            'upcoming' => Order::where('status', 'pending')->orderByRaw('delivery_date is null')->orderBy('delivery_date')->oldest()->limit(5)->get(),
        ]);
    }
}
