<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('secondary_phone')->nullable()->after('phone');
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('governate')->nullable();
            $table->string('district')->nullable();
            $table->string('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('secondary_phone');
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('governate');
            $table->dropColumn('district');
            $table->dropColumn('description');
        });
    }
};
