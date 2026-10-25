<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The design a customer picked for an ordered cake (none for a plain cake). The name is kept if the design is deleted. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cake_customizations', function (Blueprint $table) {
            $table->foreignId('cake_design_id')->nullable()->constrained()->nullOnDelete();
            $table->string('design_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cake_customizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cake_design_id');
            $table->dropColumn('design_name');
        });
    }
};
