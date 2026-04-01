<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    public function assignUser(int $taskId, int $userId): bool;

    public function assignUsers(int $taskId, array $userIds): bool;

    public function assignCollaborators(int $taskId, array $userIds): bool;

    public function removeUser(int $taskId, int $userId): bool;

    public function removeCollaborator(int $taskId, int $userId): bool;

    public function attachTag(int $taskId, int $tagId): bool;

    public function detachTag(int $taskId, int $tagId): bool;

    public function getByProject(int $projectId): Collection;

    public function getByUser(int $userId): Collection;

    public function filter(array $filters): Collection;

    public function search(string $query, array $columns = ['title', 'description']): Collection;
}
