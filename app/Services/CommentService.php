<?php

namespace App\Services;

use App\Contracts\CommentRepositoryInterface;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    public function __construct(
        protected CommentRepositoryInterface $repository
    ) {}

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Comment
    {
        return $this->repository->find($id, $columns, $relations, $appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Comment
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    public function create(array $data): Comment
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

    public function getByTask(int $taskId): Collection
    {
        return $this->repository->getByTask($taskId);
    }
}
