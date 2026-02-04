<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_location_city', function (Blueprint $table) {
            $table->id();
            $table->string('province_code')->nullable();
            $table->string('province_en_name')->nullable();
            $table->string('province')->nullable();
            $table->string('area_name')->nullable();
            $table->string('area_name_slug')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_location_city');
    }
};
