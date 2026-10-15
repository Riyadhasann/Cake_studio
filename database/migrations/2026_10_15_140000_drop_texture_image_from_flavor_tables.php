<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Flavor textures were removed: a flavor's look is its layer image, else its color. */
    public function up(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('texture_image');
            });
        }
    }

    public function down(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('texture_image')->nullable()->after('layer_image');
            });
        }
    }
};
