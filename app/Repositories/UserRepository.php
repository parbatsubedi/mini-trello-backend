<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
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

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?User
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?User
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("User not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): User
    {
        $user = $this->model->create($data);

        if (isset($data["roles"])) {
            $user->roles()->sync($data["roles"]);
        }
        if (isset($data["projects"])) {
            $user->projects()->sync($data["projects"]);
        }

        return $user;
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);

        $updated = $model->update($data);

        if (isset($data["roles"])) {
            $model->roles()->sync($data["roles"]);
        }
        if (isset($data["projects"])) {
            $model->projects()->sync($data["projects"]);
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);

        return $model->delete();
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        $user = $this->findOrFail($userId);
        $user->roles()->attach($roleId);

        return true;
    }

    public function removeRole(int $userId, int $roleId): bool
    {
        $user = $this->findOrFail($userId);
        $user->roles()->detach($roleId);

        return true;
    }

    public function assignProject(int $userId, int $projectId): bool
    {
        $user = $this->findOrFail($userId);
        $user->projects()->attach($projectId);

        return true;
    }

    public function removeProject(int $userId, int $projectId): bool
    {
        $user = $this->findOrFail($userId);
        $user->projects()->detach($projectId);

        return true;
    }
}
