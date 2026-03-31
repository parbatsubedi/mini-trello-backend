<?php

namespace App\Repositories;

use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    protected $model;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Task
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Task
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Task not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Task
    {
        $task = $this->model->create($data);

        if (isset($data['assigned_users'])) {
            $task->assignedUsers()->sync($data['assigned_users']);
        }
        if (isset($data['tags'])) {
            $task->tags()->sync($data['tags']);
        }
        if (isset($data['labels'])) {
            $task->labels()->sync($data['labels']);
        }

        return $task;
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);

        $updated = $model->update($data);

        if (isset($data['assigned_users'])) {
            $model->assignedUsers()->sync($data['assigned_users']);
        }
        if (isset($data['tags'])) {
            $model->tags()->sync($data['tags']);
        }
        if (isset($data['labels'])) {
            $model->labels()->sync($data['labels']);
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);

        return $model->delete();
    }

    public function assignUser(int $taskId, int $userId): bool
    {
        $task = $this->findOrFail($taskId);
        $task->assignedUsers()->attach($userId);

        return true;
    }

    public function removeUser(int $taskId, int $userId): bool
    {
        $task = $this->findOrFail($taskId);
        $task->assignedUsers()->detach($userId);

        return true;
    }

    public function attachTag(int $taskId, int $tagId): bool
    {
        $task = $this->findOrFail($taskId);
        $task->tags()->attach($tagId);

        return true;
    }

    public function detachTag(int $taskId, int $tagId): bool
    {
        $task = $this->findOrFail($taskId);
        $task->tags()->detach($tagId);

        return true;
    }

    public function getByProject(int $projectId): Collection
    {
        return $this->model->where('project_id', $projectId)
            ->with(['creator', 'assignee', 'tags', 'assignedUsers'])
            ->get();
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->where('assigned_to', $userId)
            ->orWhere('user_id', $userId)
            ->orWhereHas('assignedUsers', fn ($q) => $q->where('user_id', $userId))
            ->with(['project', 'creator', 'assignee', 'tags'])
            ->get();
    }

    public function filter(array $filters): Collection
    {
        $query = $this->model->with([
            'project',
            'creator',
            'assignee',
            'tags',
            'assignedUsers',
        ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('user_id', $filters['user_id'])
                    ->orWhere('assigned_to', $filters['user_id'])
                    ->orWhereHas('assignedUsers', fn ($sq) => $sq->where('user_id', $filters['user_id']));
            });
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag_id']));
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('due_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('due_date', '<=', $filters['to_date']);
        }

        if (! empty($filters['overdue']) && $filters['overdue']) {
            $query->where('due_date', '<', now()->toDateString())
                ->whereNotIn('status', ['done']);
        }

        if (! empty($filters['created_by'])) {
            $query->where('user_id', $filters['created_by']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->get();
    }

    public function search(string $query, array $columns = ['title', 'description']): Collection
    {
        return $this->model->with(['project', 'creator', 'assignee', 'tags'])
            ->where(function ($q) use ($query, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$query}%");
                }
            })
            ->get();
    }
}
