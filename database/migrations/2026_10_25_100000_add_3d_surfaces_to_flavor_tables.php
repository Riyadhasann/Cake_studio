<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The pictures the 3D cake wraps around each layer (not the 2D layer_image, which is shaped like a cut). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('side_texture')->nullable(); // close-up crumb or cream: a tileable square or a wide strip, a path inside public/
                $table->decimal('tile_scale', 4, 2)->default(1); // how big one repeat of a tileable picture looks ("grain size")
            });
        }

        Schema::table('cream_flavors', function (Blueprint $table) {
            $table->string('top_texture')->nullable(); // square picture shown once on top of the cake
        });
    }

    public function down(): void
    {
        Schema::table('cream_flavors', function (Blueprint $table) {
            $table->dropColumn('top_texture');
        });

        foreach (['sponge_flavors', 'cream_flavors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['side_texture', 'tile_scale']);
            });
        }
    }
};
