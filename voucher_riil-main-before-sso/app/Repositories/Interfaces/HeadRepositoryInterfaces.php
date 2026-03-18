<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface HeadRepositoryInterfaces
{
    public function voucherHeadIndex();

    public function voucherHeadStore(Request $request);

    public function searchVoucherHead(Request $request);

    public function getBenefits($id);

    public function transaksiHeadIndex();

    public function searchTransaksiHead(Request $request);

    public function scanHeadIndex();

    public function scanHeadBarcode(Request $request);
}