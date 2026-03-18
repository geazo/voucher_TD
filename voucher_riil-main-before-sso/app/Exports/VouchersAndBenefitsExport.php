<?php

// app/Exports/VouchersAndBenefitsExport.php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\VouchersSheet;
use App\Exports\Sheets\VoucherBenefitsSheet;

class VouchersAndBenefitsExport implements WithMultipleSheets
{
    public function __construct(private array $filters = [])
    {}

    public function sheets(): array
    {
        return [
            new VouchersSheet($this->filters),
            new VoucherBenefitsSheet($this->filters),
        ];
    }
}