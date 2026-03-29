<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $totalProjects = Project::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        $completedTasks = Task::whereHas('project.members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('status', 'done')->count();

        $inProgressTasks = Task::whereHas('project.members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereIn('status', ['in_progress', 'review'])->count();

        $totalTasks = Task::whereHas('project.members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        $efficiency = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $stats = [
            ['label' => 'Total Projects', 'value' => $totalProjects, 'change' => $this->getProjectChange(), 'color' => 'bg-blue-500'],
            ['label' => 'Completed Tasks', 'value' => $completedTasks, 'change' => $this->getCompletedTaskChange(), 'color' => 'bg-green-500'],
            ['label' => 'In Progress', 'value' => $inProgressTasks, 'change' => $this->getInProgressChange(), 'color' => 'bg-yellow-500'],
            ['label' => 'Efficiency', 'value' => $efficiency.'%', 'change' => $this->getEfficiencyChange(), 'color' => 'bg-purple-500'],
        ];

        $recentProjects = $this->getRecentProjects($userId);
        $recentActivity = $this->getRecentActivity($userId);

        return response()->json([
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function getProjectChange(): string
    {
        $currentMonth = now()->month;
        $count = Project::whereMonth('created_at', $currentMonth)->count();

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

    private function getRecentProjects(int $userId): array
    {
        $projects = Project::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->withCount('tasks')
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
            ];
        })->toArray();
    }

    private function getRecentActivity(int $userId): array
    {
        $taskActivities = Task::whereHas('project.members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with('creator')
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
                ];
            });

        $commentActivities = Comment::whereHas('task.project.members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with('user')
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
            ->sortByDesc(fn ($item) => str_replace([' hour', ' day', 's ago'], ['', '', ''], $item['time']))
            ->take(5)
            ->values();

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
        $diff = now()->diffInMinutes($date);

        if ($diff < 60) {
            return $diff.' minutes ago';
        }

        $hours = floor($diff / 60);
        if ($hours < 24) {
            return $hours.' hour'.($hours > 1 ? 's' : '').' ago';
        }

        $days = floor($hours / 24);

        return $days.' day'.($days > 1 ? 's' : '').' ago';
    }
}
