<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The icing color the customer picked for the message on the cake (a hex color from config('studio.message_colors')). */
    public function up(): void
    {
        Schema::table('cake_customizations', function (Blueprint $table) {
            $table->string('message_color', 7)->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('cake_customizations', function (Blueprint $table) {
            $table->dropColumn('message_color');
        });
    }
};
