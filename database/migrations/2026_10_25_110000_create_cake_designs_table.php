<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finished cake designs (the decorated outside of a cake) that customers can pick, each with its photos:
 * one photo, or a full turn of photos taken around the cake that customers drag to spin (360°).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cake_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0); // menu order, ties broken by id
            $table->timestamps();
        });

        Schema::create('cake_design_frames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cake_design_id')->constrained()->cascadeOnDelete();
            $table->string('image'); // a path inside public/, e.g. storage/cake-designs/x.webp
            $table->unsignedInteger('position'); // spin order; the first is the cover photo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cake_design_frames');
        Schema::dropIfExists('cake_designs');
    }
};
