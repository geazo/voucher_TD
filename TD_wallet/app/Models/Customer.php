<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Customer extends Authenticatable
{
    protected $fillable = [
        'membership_id',
        'nama',
        'email',
        'notelp',
        'password',
        'f_aktif'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'f_aktif'  => 'boolean',
        ];
    }


    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_id');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'customer_id');
    }
}
