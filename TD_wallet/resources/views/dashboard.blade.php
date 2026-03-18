@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Admin Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Customer Terdaftar</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ number_format($totalCustomer) }} Orang</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Transaksi Topup Hari Ini</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ number_format($totalTopupHariIni) }} Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Transaksi Belanja Hari Ini</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ number_format($totalBelanjaHariIni) }} Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="alert alert-info border-0 shadow-sm">
        <h5 class="fw-bold"><i class="bi bi-info-circle"></i> Selamat Datang, {{ Auth::user()->nama }}!</h5>
        <p class="mb-0">Gunakan menu navigasi untuk mengelola data customer atau melihat riwayat transaksi master secara detail.</p>
    </div>
</div>
@endsection
