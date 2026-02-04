<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPermissionsTables extends Migration
{
    public function up()
    {
        // 1. Update admins table if columns don't exist
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'username')) {
                $table->string('username')->unique()->nullable();
            }
            if (!Schema::hasColumn('admins', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('admins', 'status')) {
                $table->boolean('status')->default(1);
            }
            if (!Schema::hasColumn('admins', 'name')) {
                $table->string('name')->nullable(); // Ensure name exists
            }
        });

        // 2. Create permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique(); // e.g., purchase.create
                $table->string('name'); // e.g., Create Purchase
                $table->string('module'); // e.g., Purchases
                $table->text('description')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        // 3. Create admin_permissions pivot table
        if (!Schema::hasTable('admin_permissions')) {
            Schema::create('admin_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id');
                $table->unsignedBigInteger('permission_id');
                $table->timestamps();

                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

                // Prevent duplicate assignments
                $table->unique(['admin_id', 'permission_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('permissions');

        Schema::table('admins', function (Blueprint $table) {
            // We typically don't drop columns in down() for existing tables unless we are sure we added them
            // leaving them is safer in this context or checking if we added them.
            // For now, let's keep it simple.
        });
    }
}
