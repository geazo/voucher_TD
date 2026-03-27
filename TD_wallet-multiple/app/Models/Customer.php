<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Customer extends Authenticatable
{
    protected $fillable = [
        'nama',
        'notelp',
        'email',
        'password',
        'f_aktif',
        'membership_id',
        'kota_domisili',
        'gender',
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

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'customer_id');
    }

    public function active_memberships()
    {
        return $this->wallets()->with('membership')->get()->pluck('membership')->unique('id');
    }
}
