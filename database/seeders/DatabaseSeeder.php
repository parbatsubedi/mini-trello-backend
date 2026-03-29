<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            TagSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
            CommentSeeder::class,
            AttachmentSeeder::class,
        ]);
    }
}
