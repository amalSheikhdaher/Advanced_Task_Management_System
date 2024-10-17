<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        Permission::create(['name' => 'assign tasks']);
        Permission::create(['name' => 'update tasks']);
        Permission::create(['name' => 'close tasks']);

        // Create Roles and assign created permissions
        $taskAssigner = Role::create(['name' => 'task_assigner']);
        $taskAssigner->givePermissionTo('assign tasks');

        $taskUpdater = Role::create(['name' => 'task_updater']);
        $taskUpdater->givePermissionTo('update tasks');

        $taskCloser = Role::create(['name' => 'task_closer']);
        $taskCloser->givePermissionTo('close tasks');
    }
}
