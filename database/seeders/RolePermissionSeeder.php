<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $admin = Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access', 'is_system' => true]);
        $staff = Role::create(['name' => 'Staff', 'slug' => 'staff', 'description' => 'Operational staff', 'is_system' => true]);
        $client = Role::create(['name' => 'Client', 'slug' => 'client', 'description' => 'Medical shop client', 'is_system' => true]);

        // Permissions by module
        $modules = [
            'dashboard' => ['dashboard.view'],
            'products' => ['products.view', 'products.create', 'products.edit', 'products.delete', 'products.import'],
            'inventory' => ['inventory.view', 'inventory.adjust', 'inventory.transfer'],
            'orders' => ['orders.view', 'orders.create', 'orders.approve', 'orders.cancel', 'orders.modify'],
            'invoices' => ['invoices.view', 'invoices.create', 'invoices.cancel'],
            'payments' => ['payments.view', 'payments.create', 'payments.confirm'],
            'clients' => ['clients.view', 'clients.create', 'clients.edit', 'clients.kyc'],
            'delivery' => ['delivery.view', 'delivery.assign', 'delivery.update'],
            'reports' => ['reports.view', 'reports.export'],
            'settings' => ['settings.view', 'settings.edit'],
        ];

        $allPermissions = [];
        foreach ($modules as $module => $perms) {
            foreach ($perms as $perm) {
                $allPermissions[$perm] = Permission::create([
                    'name' => ucwords(str_replace('.', ' ', $perm)),
                    'slug' => $perm,
                    'module' => $module,
                ]);
            }
        }

        // Admin gets all permissions (but we check via isAdmin() in code)
        $admin->permissions()->attach(array_map(fn($p) => $p->id, $allPermissions));

        // Staff permissions
        $staffPerms = [
            'dashboard.view', 'products.view', 'inventory.view',
            'orders.view', 'orders.approve', 'orders.modify',
            'invoices.view', 'invoices.create',
            'payments.view', 'payments.create',
            'clients.view',
            'delivery.view', 'delivery.assign', 'delivery.update',
            'reports.view',
        ];
        $staff->permissions()->attach(
            collect($staffPerms)->map(fn($p) => $allPermissions[$p]->id)->toArray()
        );

        // Client permissions
        $clientPerms = [
            'dashboard.view', 'products.view', 'orders.view', 'orders.create',
            'invoices.view', 'payments.view',
        ];
        $client->permissions()->attach(
            collect($clientPerms)->map(fn($p) => $allPermissions[$p]->id)->toArray()
        );
    }
}
