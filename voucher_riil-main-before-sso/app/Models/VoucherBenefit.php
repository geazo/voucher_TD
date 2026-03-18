<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherBenefit extends Model
{
    protected $fillable = [
        'voucher_id',
        'benefit_id',
        'kode_benefit',
        'status',
        'discan'
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function benefit()
    {
        return $this->belongsTo(Benefit::class);
    }
}
