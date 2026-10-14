<?php

namespace Database\Seeders;

use App\Models\CakeSize;
use Illuminate\Database\Seeder;

class CakeSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            ['label' => '1 kg', 'weight_kg' => 1, 'price' => 1200],
            ['label' => '2 kg', 'weight_kg' => 2, 'price' => 2300],
            ['label' => '3 kg', 'weight_kg' => 3, 'price' => 3300],
        ];

        foreach ($sizes as $size) {
            CakeSize::firstOrCreate(
                ['label' => $size['label']],
                ['weight_kg' => $size['weight_kg'], 'price' => $size['price']]
            );
        }
    }
}
