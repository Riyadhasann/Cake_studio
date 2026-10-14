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
                $table->string('texture_image')->nullable(); // tileable square image, a path inside public/
                $table->unsignedInteger('sort_order')->default(0); // menu order, ties broken by id
            });
        }
    }

    public function down(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['texture_image', 'sort_order']);
            });
        }
    }
};
