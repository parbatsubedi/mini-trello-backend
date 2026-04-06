<?php

namespace App\Repositories;

use App\Contracts\TaskRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskRepository implements TaskRepositoryInterface
{
    protected $model;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }

    /**
     * Apply project visibility access-control to a task query.
     * Tasks in open projects are visible to everyone.
     * Tasks in closed projects are only visible to the project creator or members.
     * Admins can see all tasks regardless of project visibility.
     */
    protected function applyProjectVisibilityScope(Builder $query, int $userId): Builder
    {
        $user = User::find($userId);

        if ($user && $user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('project', function (Builder $q) use ($userId) {
            $q->where('visibility', 'open')
                ->orWhere(function (Builder $inner) use ($userId) {
                    $inner->where('visibility', 'closed')
                        ->where(function (Builder $access) use ($userId) {
                            $access->where('user_id', $userId)
                                ->orWhereHas('members', fn (Builder $m) => $m->where('user_id', $userId));
                        });
                });
        });
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        $userId = auth()->id();

        return $this->applyProjectVisibilityScope($this->model->with($relations)->newQuery(), $userId)
            ->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Task
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Task
    {
        $userId = auth()->id();

        $model = $this->applyProjectVisibilityScope(
            $this->model->with($relations)->newQuery(),
            $userId
        )->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Task not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Task
    {
        try{
            return DB::transaction(function () use ($data) {
                $data['user_id'] = auth()->id();
                $task = $this->model->create($data);

                if (isset($data['assigned_users'])) {
                    $task->assignedUsers()->sync($this->formatSyncData($data['assigned_users'], false));
                }
                if (isset($data['tags'])) {
                    $task->tags()->sync($data['tags']);
                }
                if (isset($data['labels'])) {
                    $task->labels()->sync($data['labels']);
                }
                if (isset($data['collaborators'])) {
                    $task->collaborators()->sync($this->formatSyncData($data['collaborators'], true));
                }

                    ActivityLog::log(
                    'task_created',
                    'Created task: '.$task->title,
                    $task,
                    null,
                    $data,
                    Task::class
                );

                TaskHistory::log(
                    $task->id,
                    'task_created',
                    'Task created',
                    null,
                    null,
                    $data
                );                

                return $task;
            });
        } catch (\Exception $e) {
            \Log::error('Failed to create task: ',[$e]);
            throw new \Exception('Failed to create task: '.$e->getMessage());
        }
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->findOrFail($id);
            $oldValues = $model->toArray();

            $updated = $model->update($data);

            if (isset($data['assigned_users'])) {
                $model->assignedUsers()->sync($this->formatSyncData($data['assigned_users'], false));
            }
            if (isset($data['tags'])) {
                $model->tags()->sync($data['tags']);
            }
            if (isset($data['labels'])) {
                $model->labels()->sync($data['labels']);
            }
            if (isset($data['collaborators'])) {
                $model->collaborators()->sync($this->formatSyncData($data['collaborators'], true));
            }

            ActivityLog::log(
                'task_updated',
                'Updated task: '.$model->title,
                $model,
                $oldValues,
                $data,
                Task::class
            );

            foreach ($data as $field => $newValue) {
                if (isset($oldValues[$field]) && $oldValues[$field] != $newValue) {
                    TaskHistory::log(
                        $id,
                        'field_changed',
                        'Field changed: '.$field,
                        $field,
                        (string) $oldValues[$field],
                        (string) $newValue
                    );
                }
            }

            return $updated;
        });
    }

    protected function formatSyncData(array $userIds, bool $isCollaborator): array
    {
        $syncData = [];
        foreach ($userIds as $userId) {
            $syncData[$userId] = ['is_collaborator' => $isCollaborator];
        }

        return $syncData;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);
        $taskTitle = $model->title;

        $deleted = $model->delete();

        if ($deleted) {
            ActivityLog::log(
                'task_deleted',
                'Deleted task: '.$taskTitle,
                null,
                ['id' => $id, 'title' => $taskTitle],
                null,
                Task::class
            );

            TaskHistory::log(
                $id,
                'task_deleted',
                'Task deleted'
            );
        }

        return $deleted;
    }

    public function assignUser(int $taskId, int $userId): bool
    {
        return $this->assignUsers($taskId, [$userId]);
    }

    public function assignUsers(int $taskId, array $userIds): bool
    {
        return DB::transaction(function () use ($taskId, $userIds) {
            $task = $this->findOrFail($taskId);
            $task->assignedUsers()->sync($this->formatSyncData($userIds, false));

            ActivityLog::log(
                'task_users_assigned',
                'Assigned users to task: '.$task->title,
                $task,
                null,
                ['user_ids' => $userIds],
                Task::class
            );

            TaskHistory::log(
                $taskId,
                'users_assigned',
                'Users assigned to task',
                'assigned_users',
                null,
                implode(',', $userIds)
            );

            return true;
        });
    }

    public function assignCollaborators(int $taskId, array $userIds): bool
    {
        return DB::transaction(function () use ($taskId, $userIds) {
            $task = $this->findOrFail($taskId);
            $task->collaborators()->sync($this->formatSyncData($userIds, true));

            ActivityLog::log(
                'task_collaborators_assigned',
                'Assigned collaborators to task: '.$task->title,
                $task,
                null,
                ['user_ids' => $userIds],
                Task::class
            );

            TaskHistory::log(
                $taskId,
                'collaborators_assigned',
                'Collaborators assigned to task',
                'collaborators',
                null,
                implode(',', $userIds)
            );

            return true;
        });
    }

    public function removeUser(int $taskId, int $userId): bool
    {
        return DB::transaction(function () use ($taskId, $userId) {
            $task = $this->findOrFail($taskId);
            $task->assignedUsers()->detach($userId);

            ActivityLog::log(
                'task_user_removed',
                'Removed user from task: '.$task->title,
                $task,
                ['user_id' => $userId],
                null,
                Task::class
            );

            TaskHistory::log(
                $taskId,
                'user_removed',
                'User removed from task',
                'assigned_user',
                (string) $userId,
                null
            );

            return true;
        });
    }

    public function removeCollaborator(int $taskId, int $userId): bool
    {
        return DB::transaction(function () use ($taskId, $userId) {
            $task = $this->findOrFail($taskId);
            $task->collaborators()->detach($userId);

            ActivityLog::log(
                'task_collaborator_removed',
                'Removed collaborator from task: '.$task->title,
                $task,
                ['user_id' => $userId],
                null,
                Task::class
            );

            TaskHistory::log(
                $taskId,
                'collaborator_removed',
                'Collaborator removed from task',
                'collaborator',
                (string) $userId,
                null
            );

            return true;
        });
    }

    public function attachTag(int $taskId, int $tagId): bool
    {
        return DB::transaction(function () use ($taskId, $tagId) {
            $task = $this->findOrFail($taskId);
            $task->tags()->attach($tagId);

            ActivityLog::log(
                'task_tag_attached',
                'Attached tag to task: '.$task->title,
                $task,
                null,
                ['tag_id' => $tagId],
                Task::class
            );

            TaskHistory::log(
                $taskId,
                'tag_attached',
                'Tag attached to task',
                'tag',
                null,
                (string) $tagId
            );

            return true;
        });
    }

    public function detachTag(int $taskId, int $tagId): bool
    {
        return DB::transaction(function () use ($taskId, $tagId) {
            $task = $this->findOrFail($taskId);
            $task->tags()->detach($tagId);

            ActivityLog::log(
                'task_tag_detached',
                'Detached tag from task: '.$task->title,
                $task,
                ['tag_id' => $tagId],
                null,
                Task::class
            );

            TaskHistory::log(
                $taskId,
                'tag_detached',
                'Tag detached from task',
                'tag',
                (string) $tagId,
                null
            );

            return true;
        });
    }

    public function getByProject(int $projectId): Collection
    {
        $userId = auth()->id();

        return $this->applyProjectVisibilityScope($this->model->newQuery(), $userId)
            ->where('project_id', $projectId)
            ->with(['creator', 'assignee', 'tags', 'assignedUsers'])
            ->get();
    }

    public function getByUser(int $userId): Collection
    {
        $authUserId = auth()->id();
        $authUser = User::find($authUserId);

        $query = $this->model->newQuery();

        if (! $authUser || ! $authUser->isAdmin()) {
            $query->where('assigned_to', $userId)
                ->orWhere('user_id', $userId)
                ->orWhereHas('assignedUsers', fn ($q) => $q->where('user_id', $userId));
        }

        return $this->applyProjectVisibilityScope($query, $authUserId)
            ->with(['project', 'creator', 'assignee', 'tags'])
            ->get();
    }

    public function filter(array $filters): Collection
    {
        $userId = auth()->id();

        $query = $this->applyProjectVisibilityScope(
            $this->model->with([
                'project',
                'creator',
                'assignee',
                'tags',
                'assignedUsers',
            ])->newQuery(),
            $userId
        );

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
        $userId = auth()->id();

        return $this->applyProjectVisibilityScope(
            $this->model->with(['project', 'creator', 'assignee', 'tags'])->newQuery(),
            $userId
        )
            ->where(function ($q) use ($query, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$query}%");
                }
            })
            ->get();
    }
}
