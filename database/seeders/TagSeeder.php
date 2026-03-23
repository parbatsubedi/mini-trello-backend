<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Bug', 'slug' => 'bug', 'color' => '#ef4444'],
            ['name' => 'Feature', 'slug' => 'feature', 'color' => '#22c55e'],
            ['name' => 'Enhancement', 'slug' => 'enhancement', 'color' => '#3b82f6'],
            ['name' => 'Documentation', 'slug' => 'documentation', 'color' => '#8b5cf6'],
            ['name' => 'Urgent', 'slug' => 'urgent', 'color' => '#f97316'],
            ['name' => 'Backend', 'slug' => 'backend', 'color' => '#06b6d4'],
            ['name' => 'Frontend', 'slug' => 'frontend', 'color' => '#ec4899'],
            ['name' => 'DevOps', 'slug' => 'devops', 'color' => '#14b8a6'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
