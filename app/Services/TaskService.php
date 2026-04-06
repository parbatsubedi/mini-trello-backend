<?php

namespace App\Services;

use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        protected TaskRepositoryInterface $repository
    ) {}

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns, $relations);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Task
    {
        return $this->repository->find($id, $columns, $relations, $appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Task
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    public function create(array $data): Task
    {
        try {
            $task = $this->repository->create($data);
            return $task;
        } catch (\Exception $e) {
            \Log::error("Failed to create task: {$e->getMessage()}", ['data' => $data]);
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function assignUser(int $taskId, int $userId): bool
    {
        return $this->repository->assignUsers($taskId, [$userId]);
    }

    public function assignUsers(int $taskId, array $userIds): bool
    {
        return $this->repository->assignUsers($taskId, $userIds);
    }

    public function assignCollaborators(int $taskId, array $userIds): bool
    {
        return $this->repository->assignCollaborators($taskId, $userIds);
    }

    public function removeUser(int $taskId, int $userId): bool
    {
        return $this->repository->removeUser($taskId, $userId);
    }

    public function removeCollaborator(int $taskId, int $userId): bool
    {
        return $this->repository->removeCollaborator($taskId, $userId);
    }

    public function attachTag(int $taskId, int $tagId): bool
    {
        return $this->repository->attachTag($taskId, $tagId);
    }

    public function detachTag(int $taskId, int $tagId): bool
    {
        return $this->repository->detachTag($taskId, $tagId);
    }

    public function getByProject(int $projectId): Collection
    {
        return $this->repository->getByProject($projectId);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function filter(array $filters): Collection
    {
        return $this->repository->filter($filters);
    }

    public function search(string $query, array $columns = ['title', 'description']): Collection
    {
        return $this->repository->search($query, $columns);
    }
}
