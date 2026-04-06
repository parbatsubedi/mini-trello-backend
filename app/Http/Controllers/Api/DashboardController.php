<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DashboardResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $user = User::find($userId);
            $isAdmin = $user && $user->isAdmin();

            $projectQuery = Project::query();
            $taskQuery = Task::query();

            if (! $isAdmin) {
                $projectQuery->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->orWhereHas('members', fn ($m) => $m->where('user_id', $userId));
                });

                $taskQuery->whereHas('project', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->orWhereHas('members', fn ($m) => $m->where('user_id', $userId));
                });
            }

            $totalProjects = $projectQuery->count();
            $completedTasks = (clone $taskQuery)->where('status', 'done')->count();
            $inProgressTasks = (clone $taskQuery)->whereIn('status', ['in_progress', 'review'])->count();
            $totalTasks = $taskQuery->count();

            $efficiency = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            $stats = [
                ['label' => 'Total Projects', 'value' => $totalProjects, 'change' => $this->getProjectChange($isAdmin), 'color' => 'bg-blue-500'],
                ['label' => 'Completed Tasks', 'value' => $completedTasks, 'change' => $this->getCompletedTaskChange(), 'color' => 'bg-green-500'],
                ['label' => 'In Progress', 'value' => $inProgressTasks, 'change' => $this->getInProgressChange(), 'color' => 'bg-yellow-500'],
                ['label' => 'Efficiency', 'value' => $efficiency.'%', 'change' => $this->getEfficiencyChange(), 'color' => 'bg-purple-500'],
            ];

            $recentProjects = $this->getRecentProjects($userId, $isAdmin);
            $recentActivity = $this->getRecentActivity($userId, $isAdmin);

            $dashboardResponse = new DashboardResource((object) [
                'stats' => $stats,
                'recentProjects' => $recentProjects,
                'recentActivity' => $recentActivity,
            ]);

            return $this->successResponse($dashboardResponse, 'Dashboard data fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch dashboard data: '.$e->getMessage(), 500);
        }
    }

    private function getProjectChange(bool $isAdmin): string
    {
        $query = Project::query();
        if (! $isAdmin) {
            $userId = Auth::id();
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $userId));
            });
        }
        $currentMonth = now()->month;
        $count = $query->whereMonth('created_at', $currentMonth)->count();

        return $count > 0 ? "+{$count} this month" : 'No new projects';
    }

    private function getCompletedTaskChange(): string
    {
        $currentWeek = now()->startOfWeek();
        $count = Task::where('status', 'done')
            ->where('updated_at', '>=', $currentWeek)
            ->count();

        return $count > 0 ? "+{$count} this week" : 'No completed tasks';
    }

    private function getInProgressChange(): string
    {
        $soon = now()->addDays(3);
        $count = Task::whereIn('status', ['in_progress', 'review'])
            ->where('due_date', '<=', $soon)
            ->where('due_date', '>=', now())
            ->count();

        return $count > 0 ? "{$count} due soon" : 'No tasks due soon';
    }

    private function getEfficiencyChange(): string
    {
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $currentCompleted = Task::where('status', 'done')
            ->whereMonth('updated_at', $currentMonth)
            ->count();
        $currentTotal = Task::whereMonth('created_at', $currentMonth)->count();

        $lastCompleted = Task::where('status', 'done')
            ->whereMonth('updated_at', $lastMonth)
            ->count();
        $lastTotal = Task::whereMonth('created_at', $lastMonth)->count();

        $currentRate = $currentTotal > 0 ? round(($currentCompleted / $currentTotal) * 100) : 0;
        $lastRate = $lastTotal > 0 ? round(($lastCompleted / $lastTotal) * 100) : 0;

        $diff = $currentRate - $lastRate;
        $sign = $diff >= 0 ? '+' : '';

        return "{$sign}{$diff}% vs last month";
    }

    private function getRecentProjects(int $userId, bool $isAdmin = false): array
    {
        $query = Project::query();

        if (! $isAdmin) {
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $userId));
            });
        }

        $projects = $query->withCount('tasks')
            ->with('members')
            ->latest()
            ->limit(5)
            ->get();

        return $projects->map(function ($project) {
            $totalTasks = $project->tasks_count;
            $completedTasks = $project->tasks()->where('status', 'done')->count();
            $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            return [
                'id' => $project->id,
                'name' => $project->name,
                'progress' => $progress,
                'members' => $project->members->count(),
                'tasks' => $totalTasks,
                'status' => $project->status ?? 'active',
                'updated_at' => $project->updated_at->diffForHumans(),
            ];
        })->toArray();
    }

    private function getRecentActivity(int $userId, bool $isAdmin = false): array
    {
        $taskQuery = Task::query();

        if (! $isAdmin) {
            $taskQuery->whereHas('project', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $userId));
            });
        }

        $taskActivities = $taskQuery->with('creator')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($task) {
                $action = match ($task->status) {
                    'done' => 'completed task',
                    'in_progress' => 'started working on',
                    'review' => 'submitted for review',
                    default => 'created',
                };

                return [
                    'id' => 'task-'.$task->id,
                    'user' => $task->creator?->name ?? 'Unknown',
                    'avatar' => $this->getAvatar($task->creator?->name),
                    'action' => $action,
                    'target' => $task->title,
                    'time' => $this->formatTime($task->updated_at),
                    'timestamp' => $task->updated_at,
                ];
            });

        $commentQuery = Comment::query();

        if (! $isAdmin) {
            $commentQuery->whereHas('task.project', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('members', fn ($m) => $m->where('user_id', $userId));
            });
        }

        $commentActivities = $commentQuery->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => 'comment-'.$comment->id,
                    'user' => $comment->user?->name ?? 'Unknown',
                    'avatar' => $this->getAvatar($comment->user?->name),
                    'action' => 'commented on',
                    'target' => $comment->task?->title ?? 'Task',
                    'time' => $this->formatTime($comment->created_at),
                ];
            });

        $merged = $taskActivities->concat($commentActivities)
            ->sortByDesc('timestamp')
            ->take(5)
            ->values()
            ->map(function ($item) {
                return $item;
            });

        return $merged->toArray();
    }

    private function getAvatar(?string $name): string
    {
        if (! $name) {
            return 'UN';
        }
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            return strtoupper($parts[0][0].$parts[1][0]);
        }

        return strtoupper(substr($name, 0, 2));
    }

    private function formatTime($date): string
    {
        return Carbon::parse($date)->diffForHumans([
            'parts' => 2,
            'short' => false,
        ]);
    }
}
