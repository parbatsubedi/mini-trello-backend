<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
        // 'department_id',
        'status',
        'visibility',
        'start_date',
        'end_date',
        'client_id',
        'project_type',
        'price',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, ProjectUser::class, 'project_id', 'user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }

    /**
     * Returns true if the given user can see this project.
     * Open projects are visible to everyone; closed projects only to the creator and members.
     * Admins can see all projects.
     */
    public function isAccessibleBy(int $userId): bool
    {
        $user = User::find($userId);

        if ($user && $user->isAdmin()) {
            return true;
        }

        if ($this->visibility === 'open') {
            return true;
        }

        return $this->user_id === $userId
            || $this->members()->where('user_id', $userId)->exists();
    }
}
