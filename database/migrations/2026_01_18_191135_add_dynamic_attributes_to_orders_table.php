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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('sale_channel')->nullable();
            $table->string('sale_agent')->nullable();
            $table->boolean('is_organic')->default(false);
            $table->string('video_link')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('agent_username')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['sale_channel', 'sale_agent', 'is_organic', 'video_link', 'delivery_date', 'agent_username']);
        });
    }
};
