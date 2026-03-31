<?php

namespace App\Repositories;

use App\Contracts\CommentRepositoryInterface;
use App\Models\ActivityLog;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
    protected $model;

    public function __construct(Comment $model)
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

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Comment
    {
        return $this->model->with($relations)->find($id, $columns)?->append($appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): ?Comment
    {
        $model = $this->model->with($relations)->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Comment not found with ID: {$id}");
        }

        return $model;
    }

    public function create(array $data): Comment
    {
        $comment = $this->model->create($data);

        ActivityLog::log(
            'comment_created',
            'Created comment',
            $comment,
            null,
            $data,
            Comment::class
        );

        return $comment;
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);
        $oldValues = $model->toArray();

        $updated = $model->update($data);

        if ($updated) {
            ActivityLog::log(
                'comment_updated',
                'Updated comment',
                $model,
                $oldValues,
                $data,
                Comment::class
            );
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $model = $this->findOrFail($id);

        $deleted = $model->delete();

        if ($deleted) {
            ActivityLog::log(
                'comment_deleted',
                'Deleted comment',
                null,
                ['id' => $id],
                null,
                Comment::class
            );
        }

        return $deleted;
    }

    public function getByTask(int $taskId): Collection
    {
        return $this->model->where('task_id', $taskId)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
