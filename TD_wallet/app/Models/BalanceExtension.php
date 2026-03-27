<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceExtension extends Model
{
    protected $fillable = [
        'customer_id',
        'transaction_id',
        'alasan',
        'tambahan_hari',
        'status',
        'operator_id',
        'is_recovery',
        'nominal_uang',
        'nominal_poin'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }
}
