<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    protected $table = 'task_user';

    protected $fillable = ['task_id', 'user_id', 'is_collaborator'];

    protected $casts = [
        'is_collaborator' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
