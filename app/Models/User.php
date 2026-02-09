<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'must_change_password',
        'role',
        'deleted_by',
        'email_verified_at'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function getFirstNameAttribute(): string
    {
        $parts = explode(' ', $this->name, 2);
        return $parts[0] ?? '';
    }

    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', $this->name, 2);
        return $parts[1] ?? '';
    }

    public function isRoot(): bool
    {
        return $this->role === 'root';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['root', 'admin']);
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

}
