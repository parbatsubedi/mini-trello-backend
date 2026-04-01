<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Label::updateOrCreate(
            ['name' => 'Urgent'],
            ['color' => '#ff0000',
                'type' => Label::TYPE_BOTH
                ]
        );

        Label::updateOrCreate(
            ['name' => 'High Priority'],
            ['color' => '#ffa500',
                'type' => Label::TYPE_TASK
                ]
        );

        Label::updateOrCreate(
            ['name' => 'Low Priority'],
            ['color' => '#00ff00',
                'type' => Label::TYPE_TASK
                ]
        );

        Label::updateOrCreate(
            ['name' => 'Bug'],
            ['color' => '#0000ff',
                'type' => Label::TYPE_TASK
                ]
        );

        Label::updateOrCreate(
            ['name' => 'Feature'],
            ['color' => '#800080',
                'type' => Label::TYPE_TASK
                ]
        );

        Label::updateOrCreate(
            ['name' => 'Project A'],
            ['color' => '#800080',
                'type' => Label::TYPE_PROJECT
                ]
        );
    }
}
