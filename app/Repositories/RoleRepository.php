<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Role $model)
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

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Role
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Role
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Role not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Role
    {
        $role = $this->model->create($data);

        if (isset($data["users"])) {
            $role->users()->sync($data["users"]);
        }

        return $role;
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);

        $updated = $model->update($data);

        if (isset($data["users"])) {
            $model->users()->sync($data["users"]);
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);

        return $model->delete();
    }
}
