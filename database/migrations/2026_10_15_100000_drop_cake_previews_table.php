<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retires the per-combination preview images (one image per sponge + cream pair), which the SVG
 * drawings replaced. Uploaded files in storage/app/public/cake-previews are left on disk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cake_previews');
    }

    /** Brings the empty table back; the rows dropped by up() are not restored. */
    public function down(): void
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
};
