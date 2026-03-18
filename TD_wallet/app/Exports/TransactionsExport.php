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

    public function query()
    {
        $query = Transaction::query()->with(['wallet.customer', 'operator']);

        if ($this->type === 'topup') {
            $query->where('type', 'kredit');
        } else {
            $query->where('type', 'debit');
        }

        return $query;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Waktu', 'Nama Customer', 'No Telp', 'Dompet', 'Tipe', 'Nominal', 'Operator'];
    }

    public function map($t): array
    {
        return [
            $t->created_at->format('d/m/Y'),
            $t->created_at->format('H:i:s'),
            $t->wallet->customer->nama ?? 'N/A',
            // Menambahkan spasi kosong agar Excel membacanya sebagai teks mutlak
            " " . ($t->wallet->customer->notelp ?? '-'),
            $t->wallet->type ?? '-',
            strtoupper($t->type),
            $t->nominal,
            $t->operator->nama ?? 'System'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT, // Memaksa Kolom D (No Telp) jadi Teks
            'G' => '#,##0',                   // Format Ribuan otomatis untuk Nominal
        ];
    }
}
