<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Where the cake sits in a design's cover photo, so the 3D cake can wear that photo. Null = the usual place. */
    public function up(): void
    {
        Schema::table('cake_designs', function (Blueprint $table) {
            $table->json('fit')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('cake_designs', function (Blueprint $table) {
            $table->dropColumn('fit');
        });
    }
};
