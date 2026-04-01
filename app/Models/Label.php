<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Label extends Model
{

    const TYPE_TASK = 'task';
    const TYPE_PROJECT = 'project';
    const TYPE_BOTH = 'both';
    
    protected $fillable = [
        'name',
        'color',
        'type',
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }
}
