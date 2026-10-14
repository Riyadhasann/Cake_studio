<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CakeSizeSeeder::class,
            SpongeFlavorSeeder::class,
            CreamFlavorSeeder::class,
            CakePreviewSeeder::class,
            CakeVariantSeeder::class,
        ]);
    }
}
