<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface extends BaseRepositoryInterface
{
    public function assignMember(int $projectId, int $userId): bool;

    public function removeMember(int $projectId, int $userId): bool;

    public function getByUser(int $userId): Collection;

    public function filter(array $filters): Collection;

    public function search(string $query, array $columns = ['name', 'description']): Collection;
}
