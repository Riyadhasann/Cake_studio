<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('color', 7)->nullable();       // hex, e.g. #edd08a, used to draw the layer
                $table->string('layer_image')->nullable();    // optional photo slice, path inside public/
                $table->boolean('is_active')->default(true);  // false = hidden from the studio, kept for old orders
            });
        }
    }

    public function down(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['color', 'layer_image', 'is_active']);
            });
        }
    }
};
