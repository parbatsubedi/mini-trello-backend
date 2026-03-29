<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tasks = Task::all();

        $fileTemplates = [
            ['design-mockup.png', 'image/png', 245000],
            ['project-specs.pdf', 'application/pdf', 1250000],
            ['screenshot.jpg', 'image/jpeg', 850000],
            ['wireframe.fig', 'application/octet-stream', 4500000],
            ['api-documentation.pdf', 'application/pdf', 890000],
            ['test-results.csv', 'text/csv', 25000],
            ['presentation.pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 5500000],
            ['code-snippet.js', 'text/javascript', 12000],
            ['database-schema.sql', 'application/sql', 35000],
            ['logo.svg', 'image/svg+xml', 45000],
        ];

        foreach ($tasks as $task) {
            $numAttachments = rand(0, 3);

            for ($i = 0; $i < $numAttachments; $i++) {
                $user = $users->random();
                $file = $fileTemplates[array_rand($fileTemplates)];
                $createdDaysAgo = rand(1, 10);

                Attachment::updateOrCreate(
                    ['file_path' => "/attachments/{$task->id}/".$file[0]],
                    [
                        'name' => $file[0],
                        'mime_type' => $file[1],
                        'size' => $file[2],
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                        'created_at' => now()->subDays($createdDaysAgo),
                        'updated_at' => now()->subDays($createdDaysAgo),
                    ]
                );
            }
        }
    }
}
