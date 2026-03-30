<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Pastikan Adjustment ikut masuk ke dalam filter 'payment'
     */
    public function query()
    {
        // PENTING 1: Ubah Eager Loading.
        // Membership sekarang dipanggil lewat wallet (wallet.membership), bukan lewat customer lagi.
        $query = Transaction::query()->with(['wallet.customer', 'wallet.membership', 'operator']);

        if ($this->type === 'topup') {
            $query->where('type', 'kredit');
        } else {
            // Gabungkan Debit (Pembayaran) dan Adjustment (Expired/Koreksi)
            $query->whereIn('type', ['debit', 'adjustment']);
        }

        return $query->latest();
    }

    /**
     * Menambahkan kolom Tier dan Keterangan untuk transparansi data
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'Nama Customer',
            'No Telp',
            'Tier Kartu',     // Sedikit penyesuaian nama kolom agar lebih jelas
            'Jenis Dompet',   // (Uang / Poin)
            'Tipe Transaksi', // (KREDIT/DEBIT/ADJUSTMENT)
            'Nominal',
            'Keterangan',
            'Operator'
        ];
    }

    /**
     * Mapping data ke kolom Excel
     */
    public function map($t): array
    {
        return [
            $t->created_at->format('d/m/Y'),
            $t->created_at->format('H:i:s'),
            $t->wallet->customer->nama ?? 'N/A',
            // Paksa jadi teks agar 0 di depan tidak hilang
            " " . ($t->wallet->customer->notelp ?? '-'),

            // PENTING 2: Ambil nama Tier langsung dari relasi dompetnya
            $t->wallet->membership->name ?? 'Reguler', // Silakan ganti 'Reguler' dengan nama fallback Anda yang baru

            $t->wallet->type ?? '-',
            strtoupper($t->type),
            $t->nominal,
            $t->keterangan ?? '-',
            $t->operator->nama ?? 'System'
        ];
    }

    /**
     * Menyesuaikan format kolom setelah penambahan kolom baru
     */
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT, // No Telp
            'H' => '#,##0',                   // Nominal (geser ke H)
        ];
    }
}
