<?php

namespace Database\Seeders;

use App\Models\CakePreview;
use App\Models\CreamFlavor;
use App\Models\SpongeFlavor;
use Illuminate\Database\Seeder;

class CakePreviewSeeder extends Seeder
{
    public function run(): void
    {
        $sponges = SpongeFlavor::all();
        $creams = CreamFlavor::all();

        foreach ($sponges as $sponge) {
            foreach ($creams as $cream) {
                CakePreview::firstOrCreate(
                    [
                        'sponge_flavor_id' => $sponge->id,
                        'cream_flavor_id' => $cream->id,
                    ],
                    [
                        'image' => 'cake-previews/placeholder.jpg',
                    ]
                );
            }
        }
    }
}
