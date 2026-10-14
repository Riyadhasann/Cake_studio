<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->boolean('is_default')->default(false); // the flavor every new layer starts with
            });
        }

        // Starting defaults. Only set when that flavor is on the menu; the admin can change them later.
        DB::table('sponge_flavors')->where('name', 'Chocolate')->where('is_active', true)->update(['is_default' => true]);
        DB::table('cream_flavors')->where('name', 'Vanilla')->where('is_active', true)->update(['is_default' => true]);
    }

    public function down(): void
    {
        foreach (['sponge_flavors', 'cream_flavors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }
};
