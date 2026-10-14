<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The fixed parts of the cake preview that are not a flavor: the frosting on top and the plate.
        Schema::create('cake_parts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // frosting | plate (used by the code)
            $table->string('name');            // shown in the admin page
            $table->string('layer_image')->nullable(); // path inside public/, null = default drawing
            $table->timestamps();
        });

        $now = now();

        DB::table('cake_parts')->insert([
            ['key' => 'frosting', 'name' => 'Frosting on top', 'layer_image' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'plate',    'name' => 'Cake plate',      'layer_image' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cake_parts');
    }
};
