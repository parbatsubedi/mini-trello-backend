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
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->roles()->attach(1);

        $engineeringDept = Department::where('name', 'Engineering')->first();

        $devLead = User::create([
            'name' => 'John Developer',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'department_id' => $engineeringDept->id ?? null,
        ]);
        $devLead->roles()->attach(4);

        $developer = User::create([
            'name' => 'Jane Coder',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'department_id' => $engineeringDept->id ?? null,
        ]);
        $developer->roles()->attach(5);
    }
}
