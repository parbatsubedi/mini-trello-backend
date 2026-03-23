<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Engineering',
                'description' => 'Software development and technical team',
            ],
            [
                'name' => 'Marketing',
                'description' => 'Marketing and communications team',
            ],
            [
                'name' => 'Human Resources',
                'description' => 'HR and people operations',
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales and business development',
            ],
            [
                'name' => 'Product',
                'description' => 'Product management and design',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
