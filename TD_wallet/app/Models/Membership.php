<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    protected $fillable = [
        'name',
        'harga',
        'prefix',
        'bonus_topup',
        'diskon_belanja',
        'color_start',
        'color_end',
        'text_color'
    ];

    protected function casts(): array
    {
        return [
            'harga'          => 'decimal:2', // Pastikan tercetak sebagai desimal presisi
            'bonus_topup'    => 'decimal:2',
            'diskon_belanja' => 'decimal:2',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'membership_id');
    }
}
