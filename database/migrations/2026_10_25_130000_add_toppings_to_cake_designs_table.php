<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The toppings the 3D cake stands on a design's top: cut-out pictures, each placed round the cake some number of times. */
    public function up(): void
    {
        Schema::table('cake_designs', function (Blueprint $table) {
            $table->json('toppings')->nullable()->after('fit');
        });
    }

    public function down(): void
    {
        Schema::table('cake_designs', function (Blueprint $table) {
            $table->dropColumn('toppings');
        });
    }
};
