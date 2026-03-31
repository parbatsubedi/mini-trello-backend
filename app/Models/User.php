<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locked_at',
        'login_attempts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'locked_at',
        'login_attempts',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locked_at' => 'datetime',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function isAdmin(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists();
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null && $this->locked_at->isFuture();
    }

    public function lock(): void
    {
        $this->locked_at = now()->addMinutes(config('auth.lockout_duration'));
        $this->save();
    }

    public function unlock(): void
    {
        $this->locked_at = null;
        $this->login_attempts = 0;
        $this->save();
    }

    public function incrementLoginAttempts(): void
    {
        $this->increment('login_attempts');
    }

    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->save();
    }
}
