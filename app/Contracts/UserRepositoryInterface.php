<?php

namespace App\Contracts;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function assignRole(int $userId, int $roleId): bool;

    public function removeRole(int $userId, int $roleId): bool;

    public function assignProject(int $userId, int $projectId): bool;

    public function removeProject(int $userId, int $projectId): bool;
}
