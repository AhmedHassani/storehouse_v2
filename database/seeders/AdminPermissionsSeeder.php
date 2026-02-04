<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            'Dashboard' => ['view'],
            'POS' => ['view', 'create'],
            'Order' => ['view', 'edit', 'delete', 'update_status'],
            'Product' => ['view', 'add', 'edit', 'delete'],
            'Category' => ['view', 'add', 'edit', 'delete'],
            'SubCategory' => ['view', 'add', 'edit', 'delete'],
            'Brand' => ['view', 'add', 'edit', 'delete'],
            'Attribute' => ['view', 'add', 'edit', 'delete'],
            'Customer' => ['view', 'add', 'edit', 'delete'],
            'Supplier' => ['view', 'add', 'edit', 'delete'],
            'Purchase' => ['view', 'add', 'edit', 'delete'],
            'SupplierPayment' => ['view', 'add', 'delete'],
            'Employee' => ['view', 'add', 'edit', 'delete'], // Admin Management
            'Permission' => ['view', 'add', 'edit', 'delete'],
            'Report' => ['view'],
            'Settings' => ['view', 'edit'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $key = strtolower($module) . '.' . $action;
                $name = ucfirst($action) . ' ' . $module;

                // Check if exists
                $exists = DB::table('permissions')->where('key', $key)->first();

                if (!$exists) {
                    DB::table('permissions')->insert([
                        'key' => $key, // e.g., product.view
                        'name' => $name, // e.g., View Product
                        'module' => $module, // e.g., Product
                        'description' => "Allow admin to $action $module",
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
