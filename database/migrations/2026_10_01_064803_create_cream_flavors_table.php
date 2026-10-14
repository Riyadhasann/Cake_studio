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
        Schema::create('cream_flavors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('extra_price', 8, 2)->default(0);
            $table->string('swatch_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cream_flavors');
    }
};
