<?php
namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $agentRole = Role::firstOrCreate(['name' => 'agent']);

        // Create default tags
        $defaultTags = [
            ['name' => 'VIP', 'color' => '#f59e0b'],
            ['name' => 'Prospect caldo', 'color' => '#ef4444'],
            ['name' => 'Da richiamare', 'color' => '#6366f1'],
            ['name' => 'Partner', 'color' => '#10b981'],
            ['name' => 'Fornitore', 'color' => '#8b5cf6'],
        ];
        foreach ($defaultTags as $tag) {
            Tag::firstOrCreate(['name' => $tag['name']], $tag);
        }

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.local'],
            [
                'name' => 'Amministratore',
                'password' => Hash::make('password'),
                'position' => 'Amministratore di Sistema',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Create sample users
        $manager = User::firstOrCreate(
            ['email' => 'manager@crm.local'],
            [
                'name' => 'Marco Bianchi',
                'password' => Hash::make('password'),
                'position' => 'Sales Manager',
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');

        $agent = User::firstOrCreate(
            ['email' => 'agente@crm.local'],
            [
                'name' => 'Sara Verdi',
                'password' => Hash::make('password'),
                'position' => 'Sales Agent',
                'is_active' => true,
            ]
        );
        $agent->assignRole('agent');
    }
}
