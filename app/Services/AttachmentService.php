<?php

namespace App\Services;

use App\Contracts\AttachmentRepositoryInterface;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    public function __construct(
        protected AttachmentRepositoryInterface $repository
    ) {}

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    public function find(int $id, array $columns = ['*'], array $relations = [], array $appends = []): ?Attachment
    {
        return $this->repository->find($id, $columns, $relations, $appends);
    }

    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Attachment
    {
        return $this->repository->findOrFail($id, $columns, $relations);
    }

    public function create(array $data, ?UploadedFile $file = null): Attachment
    {
        if ($file) {
            $path = $file->store('attachments', 'public');
            $data['file_path'] = $path;
            $data['name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['size'] = $file->getSize();
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $attachment = $this->findOrFail($id);
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        return $this->repository->delete($id);
    }

    public function getByTask(int $taskId): Collection
    {
        return $this->repository->getByTask($taskId);
    }
}
