<?php

namespace Database\Seeders;

use App\Models\CreamFlavor;
use Illuminate\Database\Seeder;

class CreamFlavorSeeder extends Seeder
{
    public function run(): void
    {
        // New flavors start at 0 extra. Existing rows keep whatever price you already set.
        $flavors = [
            ['name' => 'Butter Cream',    'extra_price' => 0, 'color' => '#fff3d1'],
            ['name' => 'Chocolate Cream', 'extra_price' => 0, 'color' => '#8a5238'],
            ['name' => 'Fresh Cream',     'extra_price' => 0, 'color' => '#fffdf8'],
            ['name' => 'Tiramisu Cream',  'extra_price' => 0, 'color' => '#e6d3b3'],
        ];

        foreach ($flavors as $data) {
            $flavor = CreamFlavor::firstOrCreate(
                ['name' => $data['name']],
                ['extra_price' => $data['extra_price']]
            );

            $flavor->update([
                'color' => $flavor->color ?? $data['color'],
                'is_active' => true,
            ]);
        }

        // Anything else stays in the database (old orders point at it) but leaves the studio.
        CreamFlavor::whereNotIn('name', array_column($flavors, 'name'))->update(['is_active' => false]);

        // The design page opens on Butter Cream. Keep the admin's own default while it is still on the menu.
        if (! CreamFlavor::where('is_default', true)->where('is_active', true)->exists()) {
            CreamFlavor::query()->update(['is_default' => false]);
            CreamFlavor::where('name', 'Butter Cream')->update(['is_default' => true]);
        }
    }
}
