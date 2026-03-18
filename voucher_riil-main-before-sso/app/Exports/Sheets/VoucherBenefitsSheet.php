<?php

// app/Exports/Sheets/VoucherBenefitsSheet.php
namespace App\Exports\Sheets;

use App\Models\VoucherBenefit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Database\Eloquent\Builder;

class VoucherBenefitsSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithChunkReading
{
    public function __construct(private array $filters = [])
    {}

    public function title(): string
    {
        return 'Voucher';
    }

    public function headings(): array
    {
        return ['Voucher ID','Kode Voucher','Nama Benefit','Outlet','Status Benefit'];
    }

    // public function query()
    // {
    //     $q    = $this->filters['q']  ?? null;
    //     $from = $this->filters['from'] ?? null;
    //     $to   = $this->filters['to']   ?? null;

    //     return VoucherBenefit::query()
    //         ->with(['benefit.outlet','voucher'])
    //         ->when($q, function ($w) use ($q) {
    //             $w->where('kode_benefit', 'like', "%{$q}%");
    //         })
    //         ->when($from, fn($w) => $w->whereDate('created_at', '>=', $from))
    //         ->when($to,   fn($w) => $w->whereDate('created_at', '<=', $to))
    //         ->orderBy('voucher_id')
    //         ->orderBy('id');
    // }

    public function query()
    {
        $q = VoucherBenefit::query()
            ->with(['voucher.outlet', 'benefit'])
            ->orderBy('id', 'asc');

        // include
        if (!empty($this->filters['include_outlet_ids']) && is_array($this->filters['include_outlet_ids'])) {
            $ids = $this->filters['include_outlet_ids'];
            $q->whereHas('voucher', function (Builder $sub) use ($ids) {
                $sub->whereIn('outlet_id', $ids);
            });
        }

        // exclude
        if (!empty($this->filters['exclude_outlet_ids']) && is_array($this->filters['exclude_outlet_ids'])) {
            $ids = $this->filters['exclude_outlet_ids'];
            $q->whereHas('voucher', function (Builder $sub) use ($ids) {
                $sub->whereNotIn('outlet_id', $ids);
            });
        }

        return $q;
    }

    public function map($vb): array
    {
        return [
            // $vb->voucher_id,
            optional($vb->voucher)->kode_voucher,
            // $vb->benefit_id,
            $vb->kode_benefit,
            optional($vb->benefit)->name,
            optional(optional($vb->benefit)->outlet)->name,
            $this->mapStatus($vb->status),
        ];
    }

    private function mapStatus($status): string
    {
        $code = is_numeric($status) ? (int)$status : null;

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
