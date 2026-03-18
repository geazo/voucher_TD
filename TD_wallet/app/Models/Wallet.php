<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Wallet extends Model
{
    protected $fillable = [
        'type', // 'Uang' atau 'Poin'
        'customer_id',
        'operator_id'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    /**
     * Accessor untuk menghitung saldo secara live dari tabel transactions.
     * Saldo = Total Kredit (masuk) - Total Debit (keluar)
     */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: function () {
                $kredit = $this->transactions()->where('type', 'kredit')->sum('nominal');
                $debit  = $this->transactions()->where('type', 'debit')->sum('nominal');

                return $kredit - $debit;
            }
        );
    }
}
