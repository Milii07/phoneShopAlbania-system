<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── PERMISSIONS ──────────────────────────────────────────
        $permissions = [
            // Dashboard / Statistics
            'view statistics',

            // Purchases
            'view purchases',
            'create purchases',
            'edit purchases',
            'delete purchases',

            // Sales
            'view sales',
            'create sales',
            'edit sales',
            'delete sales',

            // Products
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Online Orders
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',

            // Categories
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Brands
            'view brands',
            'create brands',
            'edit brands',
            'delete brands',

            // Currencies
            'view currencies',
            'create currencies',
            'edit currencies',
            'delete currencies',

            // Partners
            'view partners',
            'create partners',
            'edit partners',
            'delete partners',

            // Seller Bonuses
            'view seller-bonuses',
            'create seller-bonuses',
            'edit seller-bonuses',
            'delete seller-bonuses',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── ROLES ─────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // Admin merr të gjitha
        $admin->syncPermissions(Permission::all());

        // Staff — vetëm akses bazë
        $staff->syncPermissions([
            'view statistics',
            'create purchases',
            'create products',
            'view sales',
            'create sales',
            'edit sales',
            'delete sales',
            'view orders',
            'view categories',
            'view brands',
            'view currencies',
            'view partners',
            'create partners',
            'edit partners',
            'view seller-bonuses',
        ]);

        // ── CAKTO ROLE ADMIN USERIT TË PARË ──────────────────────
        $adminUser = User::find(1);
        if ($adminUser && !$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        $staffUser = User::find(2);
        if ($staffUser) {
            $staffUser->assignRole('staff');
        }
    }
}
