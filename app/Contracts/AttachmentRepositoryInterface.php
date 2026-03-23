<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AttachmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getByTask(int $taskId): Collection;
}
