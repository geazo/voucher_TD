<?php

// app/Exports/Sheets/VouchersSheet.php
namespace App\Exports\Sheets;

use App\Models\Voucher;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VouchersSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithChunkReading
{
    public function __construct(private array $filters = [])
    {}

    public function title(): string
    {
        return 'Stok Voucher';
    }

    public function headings(): array
    {
        return ['ID','Kode Voucher','Outlet','Status'];
    }

    // public function query()
    // {
    //     $q      = $this->filters['q']      ?? null;
    //     $status = $this->filters['status'] ?? null;
    //     $from   = $this->filters['from']   ?? null;
    //     $to     = $this->filters['to']     ?? null;

    //     return Voucher::query()
    //         ->with('outlet')
    //         ->when($q, function ($w) use ($q) {
    //             $w->where(function($x) use ($q) {
    //                 $x->where('kode_voucher', 'like', "%{$q}%")
    //                   ->orWhere('description', 'like', "%{$q}%");
    //             });
    //         })
    //         ->when($status, fn($w) => $w->where('status', $status))
    //         ->when($from,   fn($w) => $w->whereDate('created_at', '>=', $from))
    //         ->when($to,     fn($w) => $w->whereDate('created_at', '<=', $to))
    //         ->orderBy('id');
    // }

    public function query()
    {
        $q = Voucher::query()
            ->with('outlet')
            ->orderBy('id', 'asc');

        // include specific outlet ids
        if (!empty($this->filters['include_outlet_ids']) && is_array($this->filters['include_outlet_ids'])) {
            $q->whereIn('outlet_id', $this->filters['include_outlet_ids']);
        }

        // exclude specific outlet ids
        if (!empty($this->filters['exclude_outlet_ids']) && is_array($this->filters['exclude_outlet_ids'])) {
            $q->whereNotIn('outlet_id', $this->filters['exclude_outlet_ids']);
        }

        return $q;
    }

    public function map($v): array
    {
        return [
            $v->id,
            $v->kode_voucher,
            optional($v->outlet)->name,
            $this->mapStatus($v->status),
        ];
    }

    private function mapStatus($status): string
    {
        $code = is_numeric($status) ? (int)$status : $null;

        return match ($code) {
            0 => 'Sudah Di-Scan',
            1 => 'Voucher Tersedia',
            2 => 'Voucher Sudah Dikirim',
            default => 'Status Tidak Dikenal',
        };
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
