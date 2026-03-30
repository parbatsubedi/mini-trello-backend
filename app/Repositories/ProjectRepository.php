<?php

namespace App\Repositories;

use App\Contracts\ProjectRepositoryInterface;
use App\Models\Project;
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

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Project
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Project
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Project not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Project
    {
        $project = $this->model->create($data);

        if (isset($data['members'])) {
            $project->members()->sync($data['members']);
        }

        return $project;
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);

        $updated = $model->update($data);

        if (isset($data['members'])) {
            $model->members()->sync($data['members']);
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);

        return $model->delete();
    }

    public function assignMember(int $projectId, int $userId): bool
    {
        $project = $this->findOrFail($projectId);
        $project->members()->attach($userId);

        return true;
    }

    public function removeMember(int $projectId, int $userId): bool
    {
        $project = $this->findOrFail($projectId);
        $project->members()->detach($userId);

        return true;
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->whereHas('members', fn ($q) => $q->where('user_id', $userId))
            ->orWhere('user_id', $userId)
            ->with(['creator', 'department', 'members'])
            ->get();
    }

    public function filter(array $filters): Collection
    {
        $query = $this->model->with(['creator', 'department', 'members']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

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
        return $this->model->with(['creator', 'department', 'members'])
            ->where(function ($q) use ($query, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$query}%");
                }
            })
            ->get();
    }
}
