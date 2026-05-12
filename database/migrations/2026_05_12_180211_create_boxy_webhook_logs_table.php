<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boxy_webhook_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('boxy_uid')->nullable();
            $blueprint->string('event_type')->nullable();
            $blueprint->json('payload')->nullable();
            $blueprint->json('headers')->nullable();
            $blueprint->string('ip_address')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boxy_webhook_logs');
    }
};
