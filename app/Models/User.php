<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'staff'   => 'Staff',
        'manager' => 'Manager',
        'admin'   => 'Admin',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'job_title'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isManager(): bool { return $this->role === 'manager'; }
    public function isStaff(): bool   { return $this->role === 'staff'; }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    /** Route name of the dashboard this user lands on after login. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin'   => 'admin.dashboard',
            'manager' => 'manager.dashboard',
            default   => 'staff.dashboard',
        };
    }
}
