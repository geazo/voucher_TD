<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function query()
    {
        // Load relasi membership dan wallets beserta transaksinya untuk hitung saldo
        return Customer::query()->with(['membership', 'wallets.transactions']);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Customer',
            'Email',
            'No. WhatsApp',
            'Membership',
            'Status',
            'Saldo Uang (Rp)',
            'Saldo Poin (Pts)',
            'Tgl Bergabung'
        ];
    }

    public function map($customer): array
    {
        // Tarik saldo menggunakan accessor 'balance' dari model Wallet kamu
        $dompetUang = $customer->wallets->where('type', 'Uang')->first();
        $dompetPoin = $customer->wallets->where('type', 'Poin')->first();

        return [
            $customer->id,
            $customer->nama,
            $customer->email ?? '-',
            " " . ($customer->notelp ?? '-'), // Spasi agar dibaca teks oleh Excel
            $customer->membership->name ?? 'Reguler',
            $customer->f_aktif ? 'Aktif' : 'Non-Aktif',
            $dompetUang->balance ?? 0,
            $dompetPoin->balance ?? 0,
            $customer->created_at->format('d/m/Y H:i'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT, // No Telp (Teks)
            'G' => '#,##0',                   // Saldo Uang (Ribuan)
            'H' => '#,##0',                   // Saldo Poin (Ribuan)
        ];
    }
}
