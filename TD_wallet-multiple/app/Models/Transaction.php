<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'order_id',   // Pastikan order_id juga masuk fillable
        'nominal',
        'sisa_saldo',
        'expired_at',
        'type',
        'operator_id',
        'keterangan'  // Pastikan keterangan masuk fillable
    ];

    protected function casts(): array
    {
        return [
            'nominal'    => 'decimal:2',
            'sisa_saldo' => 'decimal:2',
            'expired_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    // --- TAMBAHKAN RELASI INI ---
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // --- PASTIKAN RELASI INI JUGA ADA ---
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
}
