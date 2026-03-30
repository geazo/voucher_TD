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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate; // Tambahkan ini untuk hitung abjad kolom

class CustomersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    protected $memberships;

    // TERIMA DATA MEMBERSHIP DARI CONTROLLER
    public function __construct($memberships)
    {
        $this->memberships = $memberships;
    }

    public function query()
    {
        return Customer::query()->with(['wallets.membership', 'wallets.transactions']);
    }

    public function headings(): array
    {
        $headings = [
            'ID',
            'Nama Customer',
            'Email',
            'No. WhatsApp',
            'Status',
            'Tgl Bergabung'
        ];

        // 1. Buat Header Dinamis untuk setiap Membership
        foreach ($this->memberships as $m) {
            $headings[] = "Uang - " . strtoupper($m->name);
            $headings[] = "Poin - " . strtoupper($m->name);
        }

        // 2. Buat Header untuk dompet Reguler (Default tanpa tier)
        $headings[] = "Uang - REGULER";
        $headings[] = "Poin - REGULER";

        // 3. Buat Header Grand Total
        $headings[] = 'GRAND TOTAL UANG (Rp)';
        $headings[] = 'GRAND TOTAL POIN (Pts)';

        return $headings;
    }

    public function map($customer): array
    {
        $row = [
            $customer->id,
            $customer->nama,
            $customer->email ?? '-',
            " " . ($customer->notelp ?? '-'), // Spasi agar dibaca teks
            $customer->f_aktif ? 'Aktif' : 'Non-Aktif',
            $customer->created_at->format('d/m/Y H:i'),
        ];

        $wallets = $customer->wallets;
        $grandTotalUang = 0;
        $grandTotalPoin = 0;

        // 1. Isi saldo berdasarkan masing-masing tier membership
        foreach ($this->memberships as $m) {
            $uang = $wallets->where('membership_id', $m->id)->where('type', 'Uang')->sum('balance');
            $poin = $wallets->where('membership_id', $m->id)->where('type', 'Poin')->sum('balance');

            $row[] = $uang;
            $row[] = $poin;

            $grandTotalUang += $uang;
            $grandTotalPoin += $poin;
        }

        // 2. Isi saldo untuk dompet Reguler (yang membership_id-nya null)
        $uangReguler = $wallets->whereNull('membership_id')->where('type', 'Uang')->sum('balance');
        $poinReguler = $wallets->whereNull('membership_id')->where('type', 'Poin')->sum('balance');

        $row[] = $uangReguler;
        $row[] = $poinReguler;

        $grandTotalUang += $uangReguler;
        $grandTotalPoin += $poinReguler;

        // 3. Isi Grand Total di kolom paling akhir
        $row[] = $grandTotalUang;
        $row[] = $grandTotalPoin;

        return $row;
    }

    public function columnFormats(): array
    {
        $formats = [
            'D' => NumberFormat::FORMAT_TEXT, // No Telp
        ];

        // Hitung mulai dari kolom ke-7 (Kolom G) karena A-F berisi data profil
        $startColIndex = 7;

        // Total kolom angka = (Jumlah tier * 2) + 2 Reguler + 2 Grand Total
        $jumlahKolomAngka = (count($this->memberships) * 2) + 4;

        // Beri format ribuan ke semua kolom angka secara dinamis
        for ($i = 0; $i < $jumlahKolomAngka; $i++) {
            $abjadKolom = Coordinate::stringFromColumnIndex($startColIndex + $i);
            $formats[$abjadKolom] = '#,##0';
        }

        return $formats;
    }
}
