<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cake_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponge_flavor_id')->constrained('sponge_flavors')->onDelete('cascade');
            $table->foreignId('cream_flavor_id')->constrained('cream_flavors')->onDelete('cascade');
            $table->string('image');
            $table->timestamps();

            $table->unique(['sponge_flavor_id', 'cream_flavor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cake_previews');
    }
};
