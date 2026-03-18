<?php

namespace App\Exports;

use App\Models\Guest;
use App\Models\Tamu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuestsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Guest::where(function ($query) {
            $query->whereBetween('jam_keluar', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59']) // Pastikan mencakup seluruh hari
                ->orWhere(function ($q) {
                    $q->whereNull('jam_keluar')
                        ->whereBetween('jam_masuk', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59']);
                });
        })->get();
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama Depan',
            'Nama Belakang',
            'Perusahaan',
            'No HP',
            'Jam Masuk',
            'Jam Keluar',
            'Kepentingan',
            'Lokasi Tujuan',
            'Visit Location',
            'Access Card'
        ];
    }

    public function map($tamu): array
    {
        return [
            // "".$tamu->nik,
            "'" . $tamu->nik,
            $tamu->nama_depan,
            $tamu->nama_belakang,
            $tamu->perusahaan,
            $tamu->no_hp,
            $tamu->jam_masuk,
            $tamu->jam_keluar ?? '-',
            $tamu->kepentingan,
            $tamu->lokasi_tujuan,
            $tamu->visit_location ?? '-',
            $tamu->access_card ?? '-',
        ];
    }
}
