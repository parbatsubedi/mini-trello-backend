<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data) {
            $role = $this->model->create($data);

            if (isset($data["users"])) {
                $role->users()->sync($data["users"]);
            }

            ActivityLog::log(
                'role_created',
                'Created role: '.$role->name,
                $role,
                null,
                $data,
                Role::class
            );

            return $role;
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->findOrFail($id);
            $oldValues = $model->toArray();

            $updated = $model->update($data);

            if (isset($data["users"])) {
                $model->users()->sync($data["users"]);
            }

            ActivityLog::log(
                'role_updated',
                'Updated role: '.$model->name,
                $model,
                $oldValues,
                $data,
                Role::class
            );

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->findOrFail($id);
            $roleName = $model->name;

            $deleted = $model->delete();

            if ($deleted) {
                ActivityLog::log(
                    'role_deleted',
                    'Deleted role: '.$roleName,
                    null,
                    ['id' => $id, 'name' => $roleName],
                    null,
                    Role::class
                );
            }

            return $deleted;
        });
    }
}
