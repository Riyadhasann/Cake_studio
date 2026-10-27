<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The cover photo with the cut-out toppings painted out of the top, which the 3D cake's top wears. */
    public function up(): void
    {
        Schema::table('cake_designs', function (Blueprint $table) {
            $table->string('top_image')->nullable()->after('toppings');
        });
    }

    public function down(): void
    {
        Schema::table('cake_designs', function (Blueprint $table) {
            $table->dropColumn('top_image');
        });
    }
};
