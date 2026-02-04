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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // User who created the list
            $table->unsignedBigInteger('supplier_id');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0); // For partial payments if needed later
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash, credit
            $table->string('image')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('not_entered'); // not_entered, entered
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('system_price', 15, 2)->default(0); // Read only price from system at time of purchase
            $table->decimal('purchase_price', 15, 2)->default(0); // Actual price bought at
            $table->decimal('total_price', 15, 2)->default(0); // quantity * purchase_price
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('purchase_id')->nullable(); // Optional link to specific purchase
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('image')->nullable(); // Receipt image
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('purchase_details');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
    }
};
