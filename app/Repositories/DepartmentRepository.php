<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Department $model)
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

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Department
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Department
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Department not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            $department = $this->model->create($data);

            ActivityLog::log(
                'department_created',
                'Created department: '.$department->name,
                $department,
                null,
                $data,
                Department::class
            );

            return $department;
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->findOrFail($id);
            $oldValues = $model->toArray();

            $updated = $model->update($data);

            ActivityLog::log(
                'department_updated',
                'Updated department: '.$model->name,
                $model,
                $oldValues,
                $data,
                Department::class
            );

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () {
            $model = $this->findOrFail($id);
            $deptName = $model->name;

            $deleted = $model->delete();

            if ($deleted) {
                ActivityLog::log(
                    'department_deleted',
                    'Deleted department: '.$deptName,
                    null,
                    ['id' => $id, 'name' => $deptName],
                    null,
                    Department::class
                );
            }

            return $deleted;
        });
    }
}
