<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskHistory extends Model
{
    protected $table = 'task_histories';

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'description',
        'field_changed',
        'old_value',
        'new_value',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        int $taskId,
        string $action,
        ?string $description = null,
        ?string $fieldChanged = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): self {
        return static::create([
            'task_id' => $taskId,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'field_changed' => $fieldChanged,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}
