<?php

namespace Database\Seeders;

use App\Models\SpongeFlavor;
use Illuminate\Database\Seeder;

class SpongeFlavorSeeder extends Seeder
{
    public function run(): void
    {
        // New flavors start at 0 extra. Existing rows keep whatever price you already set.
        $flavors = [
            ['name' => 'Vanilla',    'extra_price' => 0, 'color' => '#edd08a'],
            ['name' => 'Chocolate',  'extra_price' => 0, 'color' => '#4e2e20'],
            ['name' => 'Pandan',     'extra_price' => 0, 'color' => '#9cc472'],
            ['name' => 'Red Velvet', 'extra_price' => 0, 'color' => '#a3262f'],
            ['name' => 'Tiramisu',   'extra_price' => 0, 'color' => '#a9794d'],
        ];

        foreach ($flavors as $data) {
            $flavor = SpongeFlavor::firstOrCreate(
                ['name' => $data['name']],
                ['extra_price' => $data['extra_price']]
            );

            $flavor->update([
                'color' => $flavor->color ?? $data['color'],
                'is_active' => true,
            ]);
        }

        // Anything else stays in the database (old orders point at it) but leaves the studio.
        SpongeFlavor::whereNotIn('name', array_column($flavors, 'name'))->update(['is_active' => false]);

        // The design page opens on Chocolate. Keep the admin's own default while it is still on the menu.
        if (! SpongeFlavor::where('is_default', true)->where('is_active', true)->exists()) {
            SpongeFlavor::query()->update(['is_default' => false]);
            SpongeFlavor::where('name', 'Chocolate')->update(['is_default' => true]);
        }
    }
}
