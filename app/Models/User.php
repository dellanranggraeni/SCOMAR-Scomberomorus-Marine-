<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Memeriksa jenis role pengguna.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Memeriksa apakah pengguna merupakan Admin
     * atau Master Admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'master_admin'], true);
    }

    /**
     * Memeriksa apakah pengguna merupakan Master Admin.
     */
    public function isMasterAdmin(): bool
    {
        return $this->role === 'master_admin';
    }
}