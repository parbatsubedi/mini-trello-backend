<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CommentRepositoryInterface extends BaseRepositoryInterface
{
    public function getByTask(int $taskId): Collection;
}
