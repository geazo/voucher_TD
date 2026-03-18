<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\HeadRepositoryInterfaces;
use Illuminate\Http\Request;

class HeadController extends Controller
{
    public function __construct(protected HeadRepositoryInterfaces $headRepository) {}

    public function voucherHeadIndex()
    {
        return $this->headRepository->voucherHeadIndex();
    }

    public function voucherHeadStore(Request $request)
    {
        return $this->headRepository->voucherHeadStore($request);
    }

    public function searchVoucherHead(Request $request)
    {
        return $this->headRepository->searchVoucherHead($request);
    }

    public function getBenefits($id)
    {
        return $this->headRepository->getBenefits($id);
    }

    public function transaksiHeadIndex()
    {
        return $this->headRepository->transaksiHeadIndex();
    }

    public function searchTransaksiHead(Request $request)
    {
        return $this->headRepository->searchTransaksiHead($request);
    }

    public function scanHeadIndex()
    {
        return $this->headRepository->scanHeadIndex();
    }

    public function scanHeadBarcode(Request $request)
    {
        return $this->headRepository->scanHeadBarcode($request);
    }
}
