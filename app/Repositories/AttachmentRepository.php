<?php

namespace App\Repositories;

use App\Contracts\AttachmentRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    protected $model;

    public function __construct(Attachment $model)
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

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Attachment
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Attachment
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Attachment not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Attachment
    {
        return DB::transaction(function () use ($data) {
            $attachment = $this->model->create($data);

            ActivityLog::log(
                'attachment_created',
                'Created attachment: '.$attachment->file_name,
                $attachment,
                null,
                $data,
                Attachment::class
            );

            return $attachment;
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->findOrFail($id);
            $oldValues = $model->toArray();

            $updated = $model->update($data);

            ActivityLog::log(
                'attachment_updated',
                'Updated attachment: '.$model->file_name,
                $model,
                $oldValues,
                $data,
                Attachment::class
            );

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $model = $this->findOrFail($id);
            $fileName = $model->file_name;

            $deleted = $model->delete();

            if ($deleted) {
                ActivityLog::log(
                    'attachment_deleted',
                    'Deleted attachment: '.$fileName,
                    null,
                    ['id' => $id, 'file_name' => $fileName],
                    null,
                    Attachment::class
                );
            }

            return $deleted;
        });
    }
}