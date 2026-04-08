<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TagRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Tag $model)
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

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Tag
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Tag
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Tag not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Tag
    {
        return DB::transaction(function () use ($data) {
            $tag = $this->model->create($data);

            ActivityLog::log(
                'tag_created',
                'Created tag: '.$tag->name,
                $tag,
                null,
                $data,
                Tag::class
            );

            return $tag;
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->findOrFail($id);
            $oldValues = $model->toArray();

            $updated = $model->update($data);

            ActivityLog::log(
                'tag_updated',
                'Updated tag: '.$model->name,
                $model,
                $oldValues,
                $data,
                Tag::class
            );

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->findOrFail($id);
            $tagName = $model->name;

            $deleted = $model->delete();

            if ($deleted) {
                ActivityLog::log(
                    'tag_deleted',
                    'Deleted tag: '.$tagName,
                    null,
                    ['id' => $id, 'name' => $tagName],
                    null,
                    Tag::class
                );
            }

            return $deleted;
        });
    }
}
