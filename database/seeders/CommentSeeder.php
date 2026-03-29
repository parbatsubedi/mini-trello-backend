<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tasks = Task::all();

        $commentTemplates = [
            'Great work on this task!',
            'Please review the latest changes.',
            'I have a question about the requirements.',
            'This is ready for testing.',
            'Can we schedule a call to discuss?',
            'Updated the design based on feedback.',
            'Fixed the bug mentioned earlier.',
            'Need clarification on this point.',
            'This task is blocked by another task.',
            'Completed! Ready for review.',
            'Working on this now.',
            'Almost done, need another day.',
            'Please check the attachment.',
            'Approved! Great job.',
            'Needs some adjustments.',
        ];

        foreach ($tasks as $task) {
            $numComments = rand(0, 5);

            for ($i = 0; $i < $numComments; $i++) {
                $user = $users->random();
                $createdDaysAgo = rand(1, 15);

                Comment::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'content' => $commentTemplates[array_rand($commentTemplates)],
                    'created_at' => now()->subDays($createdDaysAgo),
                    'updated_at' => now()->subDays($createdDaysAgo),
                ]);
            }
        }
    }
}
