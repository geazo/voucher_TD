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
        'operator_id',
        'membership_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_id');
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

                // Gunakan whereIn untuk menggabungkan debit dan adjustment sebagai pengurang
                $pengeluaran = $this->transactions()->whereIn('type', ['debit', 'adjustment'])->sum('nominal');

                return $kredit - $pengeluaran;
            }
        );
    }

    /**
     * Fungsi Static untuk membuat Nomor Rekening 10 Digit
     */
    public static function generateNoRekening($prefix = 'CS') // Ubah parameter menjadi $prefix
    {
        // Pastikan UPPERCASE dan potong paksa hanya 2 huruf pertama agar format string tidak pernah rusak
        $prefix = strtoupper(substr($prefix, 0, 2));

        $monthYear = date('my');

        // Cari wallet terakhir di bulan dan tahun ini (Apapun prefix-nya)
        $latestWallet = self::where(DB::raw('SUBSTRING(no_rekening, 3, 4)'), $monthYear)
            ->orderBy(DB::raw('CAST(SUBSTRING(no_rekening, 7, 4) AS UNSIGNED)'), 'desc')
            ->first();

        if ($latestWallet) {
            // Index 6 di PHP = Karakter ke-7 (Awal nomor urut)
            $lastSequence = (int) substr($latestWallet->no_rekening, 6, 4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        // Contoh hasil: PL + 0326 + 0001 = PL03260001
        return $prefix . $monthYear . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
}
