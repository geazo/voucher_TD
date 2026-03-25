<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $fillable = [
        'no_rekening',
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
     * Saldo = Total Kredit Asli (masuk) - Total Debit Asli (keluar)
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

    /**
     * Fungsi Static untuk membuat Nomor Rekening 10 Digit
     */
    public static function generateNoRekening($tierName = null)
    {
        $prefix = match (strtolower($tierName)) {
            'reguler'  => 'RG',
            'silver'   => 'SL',
            'gold'     => 'GL',
            'platinum' => 'PL',
            default    => 'CS',
        };

        $monthYear = date('my');

        $latestWallet = self::where(DB::raw('SUBSTRING(no_rekening, 3, 4)'), $monthYear)
                            ->orderBy(DB::raw('CAST(SUBSTRING(no_rekening, 7, 4) AS UNSIGNED)'), 'desc')
                            ->first();

        if ($latestWallet) {
            $lastSequence = (int) substr($latestWallet->no_rekening, 6, 4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . $monthYear . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
}
