<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // Gunakan ini jika operator bisa login
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Authenticatable
{
    protected $fillable = [
        'nama',
        'email',
        'role', 
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // --- Helper Functions untuk Cek Role ---
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        // Superadmin biasanya juga memiliki akses admin
        return $this->role === 'admin' || $this->role === 'superadmin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    // --- Relasi ---
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'operator_id');
    }
}
