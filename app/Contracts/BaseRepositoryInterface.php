<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?object;

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?object;

    public function create(array $data): object;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
