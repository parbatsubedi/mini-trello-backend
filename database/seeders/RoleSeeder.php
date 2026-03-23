<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access with all permissions',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access to manage resources',
            ],
            [
                'name' => 'Project Manager',
                'slug' => 'project-manager',
                'description' => 'Can manage projects and teams',
            ],
            [
                'name' => 'Team Lead',
                'slug' => 'team-lead',
                'description' => 'Lead a team and assign tasks',
            ],
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Standard developer access',
            ],
            [
                'name' => 'Designer',
                'slug' => 'designer',
                'description' => 'Design and creative team access',
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Read-only access',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
