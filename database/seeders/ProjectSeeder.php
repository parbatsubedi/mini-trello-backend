<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $engineeringDept = Department::where('name', 'Engineering')->first();
        $designDept = Department::where('name', 'Design')->first();
        $marketingDept = Department::where('name', 'Marketing')->first();

        $projects = [
            [
                'name' => 'Website Redesign',
                'description' => 'Complete overhaul of the company website with modern design',
                'user_id' => $admin->id,
                // 'department_id' => $designDept?->id,
                'status' => 'active',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonth(2),
            ],
            [
                'name' => 'Mobile App Launch',
                'description' => 'Develop and launch iOS and Android mobile applications',
                'user_id' => $admin->id,
                // 'department_id' => $engineeringDept?->id,
                'status' => 'active',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(3),
            ],
            [
                'name' => 'Marketing Campaign Q1',
                'description' => 'Q1 marketing initiatives and brand awareness campaign',
                'user_id' => $admin->id,
                // 'department_id' => $marketingDept?->id,
                'status' => 'active',
                'start_date' => now()->subWeeks(2),
                'end_date' => now()->addMonth(),
            ],
            [
                'name' => 'Backend Migration',
                'description' => 'Migrate legacy backend systems to new infrastructure',
                'user_id' => $admin->id,
                // 'department_id' => $engineeringDept?->id,
                'status' => 'active',
                'start_date' => now()->subWeeks(3),
                'end_date' => now()->addMonths(2),
            ],
            [
                'name' => 'Brand Guidelines Update',
                'description' => 'Refresh brand identity and create comprehensive style guide',
                'user_id' => $admin->id,
                // 'department_id' => $designDept?->id,
                'status' => 'completed',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->subMonth(),
            ],
            [
                'name' => 'API Integration Phase 1',
                'description' => 'Integrate third-party APIs for payment and analytics',
                'user_id' => $admin->id,
                // 'department_id' => $engineeringDept?->id,
                'status' => 'in_progress',
                'start_date' => now()->subWeeks(4),
                'end_date' => now()->addWeeks(2),
            ],
            [
                'name' => 'Customer Portal',
                'description' => 'Build self-service customer portal for account management',
                'user_id' => $admin->id,
                // 'department_id' => $engineeringDept?->id,
                'status' => 'active',
                'start_date' => now()->subWeek(),
                'end_date' => now()->addMonths(4),
            ],
            [
                'name' => 'SEO Optimization',
                'description' => 'Improve search engine rankings and website visibility',
                'user_id' => $admin->id,
                // 'department_id' => $marketingDept?->id,
                'status' => 'in_progress',
                'start_date' => now()->subWeeks(2),
                'end_date' => now()->addWeeks(3),
            ],
            [
                'name' => 'Mobile App v2.0',
                'description' => 'Major update with new features and performance improvements',
                'user_id' => $admin->id,
                // 'department_id' => $engineeringDept?->id,
                'status' => 'review',
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addWeek(),
            ],
            [
                'name' => 'Analytics Dashboard',
                'description' => 'Real-time analytics dashboard for business insights',
                'user_id' => $admin->id,
                // 'department_id' => $engineeringDept?->id,
                'status' => 'active',
                'start_date' => now()->subWeeks(5),
                'end_date' => now()->addMonths(2),
            ],
            [
                'name' => 'User Research Study',
                'description' => 'Conduct user interviews and usability testing',
                'user_id' => $admin->id,
                // 'department_id' => $designDept?->id,
                'status' => 'completed',
                'start_date' => now()->subMonths(4),
                'end_date' => now()->subMonths(2),
            ],
            [
                'name' => 'Email Marketing Automation',
                'description' => 'Set up automated email campaigns and drip sequences',
                'user_id' => $admin->id,
                // 'department_id' => $marketingDept?->id,
                'status' => 'active',
                'start_date' => now()->subWeeks(3),
                'end_date' => now()->addWeeks(4),
            ],
        ];

        $users = User::all();

        foreach ($projects as $projectData) {
            $project = Project::updateOrCreate(
                ['name' => $projectData['name']],
                $projectData
            );

            $randomUsers = $users->random(min(3, $users->count()));
            $project->members()->sync($randomUsers->pluck('id')->toArray());
        }
    }
}
