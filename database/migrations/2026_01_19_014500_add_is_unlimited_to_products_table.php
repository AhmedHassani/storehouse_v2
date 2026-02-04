<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsUnlimitedToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('products', 'is_unlimited')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_unlimited')->default(0)->after('total_stock')->comment('1=unlimited stock (will not decrease), 0=limited stock (will decrease)');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_unlimited');
        });
    }
}
