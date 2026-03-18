<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface AdminRepositoryInterfaces
{
    public function index();

    public function scanIndex();

    public function scanBarcode(Request $request);

    public function outletIndex();

    public function outletStore(Request $request);

    public function outletUpdate(Request $request, $id);

    public function outletDestroy($id);

    public function voucherIndex(Request $request);

    public function voucherStore(Request $request);

    public function searchVoucher(Request $request);

    public function getBenefits($id);

    public function transaksiIndex();

    public function sendVoucher($penerimaId);

    public function sendVoucherWhatsApp($penerimaId);

    public function searchTransaksi(Request $request);

    public function userIndex(Request $request);

    public function userStore(Request $request);

    public function userUpdate(Request $request, $id);

    public function userDestroy($id);

    public function userShow($id);

    public function importExcel(Request $request);

    public function benefitIndex(Request $request);

    public function benefitStore(Request $request);

    public function benefitUpdate(Request $request, $id);

    public function benefitDestroy($id);

    public function resetVoucher($id);

    public function editPenerima($id);

    public function updatePenerima(Request $request, $id);
}
