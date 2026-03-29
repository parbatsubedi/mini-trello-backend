<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?User
    {
        return $this->repository->find($id, $columns, $relations, $appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): User
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    public function create(array $data): User
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        return $this->repository->assignRole($userId, $roleId);
    }

    public function removeRole(int $userId, int $roleId): bool
    {
        return $this->repository->removeRole($userId, $roleId);
    }

    public function assignProject(int $userId, int $projectId): bool
    {
        return $this->repository->assignProject($userId, $projectId);
    }

    public function removeProject(int $userId, int $projectId): bool
    {
        return $this->repository->removeProject($userId, $projectId);
    }
}
