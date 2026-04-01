<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data) {
            $user = $this->model->create($data);

            if (isset($data["roles"])) {
                $user->roles()->sync($data["roles"]);
            }
            if (isset($data["projects"])) {
                $user->projects()->sync($data["projects"]);
            }

            ActivityLog::log(
                'user_created',
                'Created user: '.$user->name,
                $user,
                null,
                $data,
                User::class
            );

            return $user;
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->findOrFail($id);
            $oldValues = $model->toArray();

            $updated = $model->update($data);

            if (isset($data["roles"])) {
                $model->roles()->sync($data["roles"]);
            }
            if (isset($data["projects"])) {
                $model->projects()->sync($data["projects"]);
            }

            ActivityLog::log(
                'user_updated',
                'Updated user: '.$model->name,
                $model,
                $oldValues,
                $data,
                User::class
            );

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->findOrFail($id);
            $userName = $model->name;

            $deleted = $model->delete();

            if ($deleted) {
                ActivityLog::log(
                    'user_deleted',
                    'Deleted user: '.$userName,
                    null,
                    ['id' => $id, 'name' => $userName],
                    null,
                    User::class
                );
            }

            return $deleted;
        });
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        return DB::transaction(function () use ($userId, $roleId) {
            $user = $this->findOrFail($userId);
            $user->roles()->syncWithoutDetaching([$roleId]);

            ActivityLog::log(
                'user_role_assigned',
                'Assigned role to user: '.$user->name,
                $user,
                null,
                ['role_id' => $roleId],
                User::class
            );

            return true;
        });
    }

    public function removeRole(int $userId, int $roleId): bool
    {
        return DB::transaction(function () use ($userId, $roleId) {
            $user = $this->findOrFail($userId);
            $user->roles()->detach($roleId);

            ActivityLog::log(
                'user_role_removed',
                'Removed role from user: '.$user->name,
                $user,
                ['role_id' => $roleId],
                null,
                User::class
            );

            return true;
        });
    }

    public function assignProject(int $userId, int $projectId): bool
    {
        return DB::transaction(function () use ($userId, $projectId) {
            $user = $this->findOrFail($userId);
            $user->projects()->syncWithoutDetaching([$projectId => ['role' => 'member']]);

            ActivityLog::log(
                'user_project_assigned',
                'Assigned project to user: '.$user->name,
                $user,
                null,
                ['project_id' => $projectId],
                User::class
            );

            return true;
        });
    }

    public function removeProject(int $userId, int $projectId): bool
    {
        return DB::transaction(function () use ($userId, $projectId) {
            $user = $this->findOrFail($userId);
            $user->projects()->detach($projectId);

            ActivityLog::log(
                'user_project_removed',
                'Removed project from user: '.$user->name,
                $user,
                ['project_id' => $projectId],
                null,
                User::class
            );

            return true;
        });
    }
}
