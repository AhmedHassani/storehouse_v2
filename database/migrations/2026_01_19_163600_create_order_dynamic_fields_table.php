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
        if (!Schema::hasTable('order_dynamic_fields')) {
            Schema::create('order_dynamic_fields', function (Blueprint $table) {
                $table->id();
                $table->string('field_name'); // اسم الحقل (label)
                $table->string('field_key')->unique(); // المفتاح الفريد (field key)
                $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'select', 'checkbox', 'radio'])->default('text');
                $table->text('field_options')->nullable(); // للـ select/radio (JSON array)
                $table->string('default_value')->nullable(); // القيمة الافتراضية
                $table->boolean('is_required')->default(0); // هل الحقل مطلوب
                $table->boolean('is_active')->default(1); // هل الحقل نشط
                $table->integer('sort_order')->default(0); // ترتيب الظهور
                $table->timestamps();
            });
        }

        // جدول لحفظ قيم الحقول الديناميكية لكل طلب
        if (!Schema::hasTable('order_dynamic_field_values')) {
            Schema::create('order_dynamic_field_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->foreignId('field_id')->constrained('order_dynamic_fields')->onDelete('cascade');
                $table->text('field_value')->nullable();
                $table->timestamps();

                $table->unique(['order_id', 'field_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_dynamic_field_values');
        Schema::dropIfExists('order_dynamic_fields');
    }
};
