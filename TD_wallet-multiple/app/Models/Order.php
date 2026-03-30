<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'operator_id',
        'total_tagihan',
        'bayar_uang',
        'bayar_poin'
    ];

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class); // Untuk melihat mutasi dompet dari order ini
    }
}
