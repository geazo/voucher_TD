<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\AdminRepositoryInterfaces;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VouchersAndBenefitsExport;
use App\Models\Outlet;

class DashboardController extends Controller
{
    public function __construct(protected AdminRepositoryInterfaces $dashboardReporitory) {}

    public function indeX()
    {
        return $this->dashboardReporitory->index();
    }

    public function scanIndex()
    {
        return $this->dashboardReporitory->scanIndex();
    }

    public function scanBarcode(Request $request)
    {
        return $this->dashboardReporitory->scanBarcode($request);
    }

    public function outletIndex()
    {
        return $this->dashboardReporitory->outletIndex();
    }

    public function outletStore(Request $request)
    {
        return $this->dashboardReporitory->outletStore($request);
    }

    public function outletUpdate(Request $request, $id)
    {
        return $this->dashboardReporitory->outletUpdate($request, $id);
    }

    public function outletDestroy($id)
    {
        return $this->dashboardReporitory->outletDestroy($id);
    }

    // Voucher
    public function voucherIndex(Request $request)
    {
        return $this->dashboardReporitory->voucherIndex($request);
    }

    public function voucherStore(Request $request)
    {
        return $this->dashboardReporitory->voucherStore($request);
    }

    public function searchVoucher(Request $request)
    {
        return $this->dashboardReporitory->searchVoucher($request);
    }

    // getBenefits
    public function getBenefits($id)
    {
        return $this->dashboardReporitory->getBenefits($id);
    }


    public function transaksiIndex()
    {
        return $this->dashboardReporitory->transaksiIndex();
    }

    public function sendVoucher($penerimaId)
    {
        return $this->dashboardReporitory->sendVoucher($penerimaId);
    }

    public function sendVoucherWhatsApp($penerimaId)
    {
        return $this->dashboardReporitory->sendVoucherWhatsApp($penerimaId);
    }

    public function searchTransaksi(Request $request)
    {
        return $this->dashboardReporitory->searchTransaksi($request);
    }


    public function userIndex(Request $request)
    {
        return $this->dashboardReporitory->userIndex($request);
    }

    public function userStore(Request $request)
    {
        return $this->dashboardReporitory->userStore($request);
    }

    public function userUpdate(Request $request, $id)
    {
        return $this->dashboardReporitory->userUpdate($request, $id);
    }

    public function userDestroy($id)
    {
        return $this->dashboardReporitory->userDestroy($id);
    }

    public function userShow($id)
    {
        return $this->dashboardReporitory->userShow($id);
    }

    public function importExcel(Request $request)
    {
        return $this->dashboardReporitory->importExcel($request);
    }

    public function exportAllExcel(Request $request)
    {
        // Bawa filter yang sama dengan search (opsional)
        $filters = $request->only(['q', 'status', 'from', 'to']);

        $file = 'voucher-export-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new VouchersAndBenefitsExport($filters), $file);
    }

    public function exportPadel()
    {
        $padel = Outlet::where('kode', 'PDM')
            ->orWhereRaw('LOWER(name) = ?', ['padel malang'])
            ->firstOrFail();

        // hanya outlet Padel Malang
        $filters = ['include_outlet_ids' => [$padel->id]];

        return Excel::download(
            new VouchersAndBenefitsExport($filters),
            'export_padel_malang.xlsx'
        );
    }

    public function exportOthers()
    {
        $padel = Outlet::where('kode', 'PDM')
            ->orWhereRaw('LOWER(name) = ?', ['padel malang'])
            ->firstOrFail();

        // semua outlet kecuali Padel Malang
        $filters = ['exclude_outlet_ids' => [$padel->id]];

        return Excel::download(
            new VouchersAndBenefitsExport($filters),
            'export_outlet_lain.xlsx'
        );
    }

    // Benefit
    public function benefitIndex(Request $request)
    {
        return $this->dashboardReporitory->benefitIndex($request);
    }

    public function benefitStore(Request $request)
    {
        return $this->dashboardReporitory->benefitStore($request);
    }

    public function benefitUpdate(Request $request, $id)
    {
        return $this->dashboardReporitory->benefitUpdate($request, $id);
    }


    public function benefitDestroy($id)
    {
        return $this->dashboardReporitory->benefitDestroy($id);
    }

    public function resetVoucher($id)
    {
        return $this->dashboardReporitory->resetVoucher($id);
    }

    public function editPenerima($id)
    {
        return $this->dashboardReporitory->editPenerima($id);
    }

    public function updatePenerima(Request $request, $id)
    {
        return $this->dashboardReporitory->updatePenerima($request, $id);
    }
}
