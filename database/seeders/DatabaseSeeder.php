<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Création d'un utilisateur de test uniquement s'il n'existe pas déjà
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Création des rôles principaux
        $roles = [
            'chauffeur',
            'commercial',
            'owner',
            'gestionnaire',
            'manager',
            'administrateur',
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Création des permissions principales
        $permissions = [
            'manage vehicles',
            'manage garages',
            'manage breakdowns',
            'manage maintenances',
            'manage notifications',
            'manage breakdown photos',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assignation des permissions aux rôles
        $admin = Role::where('name', 'administrateur')->first();
        if ($admin) {
            $admin->syncPermissions($permissions);
        }
        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $manager->syncPermissions([
                'manage vehicles',
                'manage garages',
                'manage breakdowns',
                'manage maintenances',
                'manage notifications',
                'manage breakdown photos',
            ]);
        }
        $gestionnaire = Role::where('name', 'gestionnaire')->first();
        if ($gestionnaire) {
            $gestionnaire->syncPermissions([
                'manage vehicles',
                'manage breakdowns',
                'manage maintenances',
                'manage breakdown photos',
            ]);
        }

        // Création d'un superadmin (après les rôles)
        if (!User::where('email', 'admin@admin.com')->exists()) {
            $adminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
            ]);
            $adminUser->assignRole('administrateur');
        }
    }
}
