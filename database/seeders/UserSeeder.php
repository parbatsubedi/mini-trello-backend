<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $engineeringDept = Department::where('name', 'Engineering')->first();

        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->roles()->sync([1]);

        $devLead = User::updateOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Developer',
                'password' => Hash::make('password'),
                'department_id' => $engineeringDept->id ?? null,
            ]
        );
        $devLead->roles()->sync([4]);

        $developer = User::updateOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Coder',
                'password' => Hash::make('password'),
                'department_id' => $engineeringDept->id ?? null,
            ]
        );
        $developer->roles()->sync([5]);
    }
}
