<?php

namespace App\Repositories;

use App\Contracts\ProjectRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository implements ProjectRepositoryInterface
{
    protected $model;

    public function __construct(Project $model)
    {
        $this->model = $model;
    }

    /**
     * Apply visibility access-control constraint to a query.
     * Open projects are visible to everyone.
     * Closed projects are only visible to the creator or assigned members.
     * Admins can see all projects regardless of visibility.
     */
    protected function applyVisibilityScope(Builder $query, int $userId): Builder
    {
        $user = User::find($userId);

        if ($user && $user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($userId) {
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

        return $this->applyVisibilityScope($this->model->with($relations)->newQuery(), $userId)
            ->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Project
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Project
    {
        $userId = auth()->id();

        $model = $this->applyVisibilityScope(
            $this->model->with($relations)->newQuery(),
            $userId
        )->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Project not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $data['user_id'] = auth()->id();
            $project = $this->model->create($data);

            if (isset($data['members'])) {
                $project->members()->sync($data['members']);
            }
            if (isset($data['labels'])) {
                $project->labels()->sync($data['labels']);
            }

            ActivityLog::log(
                'project_created',
                'Created project: '.$project->name,
                $project,
                null,
                $data,
                Project::class
            );

            return $project;
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->findOrFail($id);
            $oldValues = $model->toArray();

            $updated = $model->update($data);

            if (isset($data['members'])) {
                $model->members()->sync($data['members']);
            }
            if (isset($data['labels'])) {
                $model->labels()->sync($data['labels']);
            }

            ActivityLog::log(
                'project_updated',
                'Updated project: '.$model->name,
                $model,
                $oldValues,
                $data,
                Project::class
            );

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->findOrFail($id);
            $projectName = $model->name;

            $deleted = $model->delete();

            if ($deleted) {
                ActivityLog::log(
                    'project_deleted',
                    'Deleted project: '.$projectName,
                    null,
                    ['id' => $id, 'name' => $projectName],
                    null,
                    Project::class
                );
            }

            return $deleted;
        });
    }

    public function assignMember(int $projectId, int $userId): bool
    {
        return DB::transaction(function () use ($projectId, $userId) {
            $project = $this->findOrFail($projectId);
            $project->members()->syncWithoutDetaching([$userId => ['role' => 'member']]);

            ActivityLog::log(
                'project_member_assigned',
                'Assigned member to project: '.$project->name,
                $project,
                null,
                ['user_id' => $userId],
                Project::class
            );

            return true;
        });
    }

    public function removeMember(int $projectId, int $userId): bool
    {
        return DB::transaction(function () use ($projectId, $userId) {
            $project = $this->findOrFail($projectId);
            $project->members()->detach($userId);

            ActivityLog::log(
                'project_member_removed',
                'Removed member from project: '.$project->name,
                $project,
                ['user_id' => $userId],
                null,
                Project::class
            );

            return true;
        });
    }

    public function getByUser(int $userId): Collection
    {
        $authUserId = auth()->id();
        $authUser = User::find($authUserId);

        $query = $this->model->newQuery();

        if (! $authUser || ! $authUser->isAdmin()) {
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('members', fn ($sq) => $sq->where('user_id', $userId));
            });
        }

        return $this->applyVisibilityScope($query, $authUserId)
            ->with(['creator', 'department', 'members'])
            ->get();
    }

    public function filter(array $filters): Collection
    {
        $userId = auth()->id();

        $query = $this->applyVisibilityScope(
            $this->model->with(['creator', 'department', 'members'])->newQuery(),
            $userId
        );

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        // if (! empty($filters['department_id'])) {
        //     $query->where('department_id', $filters['department_id']);
        // }

        if (! empty($filters['user_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('user_id', $filters['user_id'])
                    ->orWhereHas('members', fn ($sq) => $sq->where('user_id', $filters['user_id']));
            });
        }

        if (! empty($filters['created_by'])) {
            $query->where('user_id', $filters['created_by']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('start_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('end_date', '<=', $filters['to_date']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->get();
    }

    public function search(string $query, array $columns = ['name', 'description']): Collection
    {
        $userId = auth()->id();

        return $this->applyVisibilityScope(
            $this->model->with(['creator', 'department', 'members'])->newQuery(),
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
