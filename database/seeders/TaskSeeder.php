<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $users = User::where('email', '!=', 'admin@example.com')->get();

        $projects = Project::all();

        $taskTemplates = [
            'Design Phase' => [
                ['Create wireframes for homepage', 'high'],
                ['Design login page mockup', 'medium'],
                ['Create icon set', 'low'],
                ['Design dashboard layout', 'high'],
                ['Create mobile responsive designs', 'medium'],
            ],
            'Development Phase' => [
                ['Set up development environment', 'high'],
                ['Implement authentication', 'high'],
                ['Build API endpoints', 'high'],
                ['Create database schema', 'high'],
                ['Implement user dashboard', 'medium'],
                ['Add search functionality', 'medium'],
            ],
            'Testing Phase' => [
                ['Write unit tests', 'medium'],
                ['Perform integration testing', 'high'],
                ['Conduct user acceptance testing', 'high'],
                ['Fix bugs and issues', 'medium'],
                ['Performance optimization', 'low'],
            ],
            'Deployment Phase' => [
                ['Configure staging environment', 'high'],
                ['Deploy to production', 'high'],
                ['Set up monitoring', 'medium'],
                ['Documentation', 'low'],
            ],
        ];

        $statuses = ['todo', 'in_progress', 'review', 'done'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($projects as $project) {
            $phaseIndex = 0;
            foreach ($taskTemplates as $phase => $tasks) {
                $phaseIndex++;

                foreach ($tasks as $taskData) {
                    $status = $statuses[array_rand($statuses)];
                    $assignee = $users->random();
                    $creator = $admin;

                    $createdDaysAgo = rand(1, 30);
                    $dueDaysFromNow = rand(-5, 30);

                    Task::create([
                        'title' => $taskData[0],
                        'description' => "{$phase}: {$taskData[0]} for {$project->name}",
                        'project_id' => $project->id,
                        'user_id' => $creator->id,
                        'assigned_to' => $assignee->id,
                        'priority' => $taskData[1],
                        'status' => $status,
                        'due_date' => now()->addDays($dueDaysFromNow),
                        'created_at' => now()->subDays($createdDaysAgo),
                        'updated_at' => now()->subDays(rand(0, $createdDaysAgo)),
                    ]);
                }
            }

            for ($i = 0; $i < 10; $i++) {
                $status = $statuses[array_rand($statuses)];
                $assignee = $users->random();
                $creator = $admin;
                $createdDaysAgo = rand(1, 30);
                $dueDaysFromNow = rand(-5, 30);

                Task::create([
                    'title' => 'Additional task '.($i + 1)." for {$project->name}",
                    'description' => "Additional task to support {$project->name} project goals",
                    'project_id' => $project->id,
                    'user_id' => $creator->id,
                    'assigned_to' => $assignee->id,
                    'priority' => $priorities[array_rand($priorities)],
                    'status' => $status,
                    'due_date' => now()->addDays($dueDaysFromNow),
                    'created_at' => now()->subDays($createdDaysAgo),
                    'updated_at' => now()->subDays(rand(0, $createdDaysAgo)),
                ]);
            }
        }

        $overdueTasks = Task::where('due_date', '<', now())
            ->whereIn('status', ['todo', 'in_progress'])
            ->limit(5)
            ->get();

        foreach ($overdueTasks as $task) {
            $task->update(['status' => 'review']);
        }
    }
}
