<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'avatar_url',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin || $this->role === UserRole::SuperAdmin;
    }

    public function canManageBudget(): bool
    {
        return $this->is_active && ($this->role === UserRole::SuperAdmin || $this->role === UserRole::Admin);
    }

    public function canDistribute(): bool
    {
        return $this->is_active && ($this->role === UserRole::SuperAdmin || $this->role === UserRole::Admin);
    }

    public function canViewReports(): bool
    {
        return $this->is_active && ($this->role === UserRole::SuperAdmin || $this->role === UserRole::Admin);
    }

    public function canViewGgms(): bool
    {
        return $this->is_active && ($this->role === UserRole::SuperAdmin || $this->role === UserRole::Admin);
    }
}
