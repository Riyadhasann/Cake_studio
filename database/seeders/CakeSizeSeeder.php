<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CakeSize;

class CakeSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            ['label' => '1 kg', 'price' => 1200],
            ['label' => '2 kg', 'price' => 2300],
            ['label' => '3 kg', 'price' => 3300],
        ];

        foreach ($sizes as $size) {
            CakeSize::firstOrCreate(
                ['label' => $size['label']],
                ['price' => $size['price']]
            );
        }
    }
}
