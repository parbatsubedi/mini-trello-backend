<?php

namespace App\Services;

use App\Contracts\ProjectRepositoryInterface;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(
        protected ProjectRepositoryInterface $repository
    ) {}

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Project
    {
        return $this->repository->find($id, $columns, $relations, $appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Project
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    public function create(array $data): Project
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

    public function assignMember(int $projectId, int $userId): bool
    {
        return $this->repository->assignMember($projectId, $userId);
    }

    public function removeMember(int $projectId, int $userId): bool
    {
        return $this->repository->removeMember($projectId, $userId);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function filter(array $filters): Collection
    {
        return $this->repository->filter($filters);
    }

    public function search(string $query, array $columns = ['name', 'description']): Collection
    {
        return $this->repository->search($query, $columns);
    }
}
