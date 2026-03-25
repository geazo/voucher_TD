<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ExpireWalletBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:expire-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command untuk menandai transaksi yang sudah expired dan update agar hangus tidak dihitung lagi di saldo aktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan saldo kedaluwarsa...');
        // Cari topup yang masih punya sisa saldo dan sudah lewat hari ini
        $kreditHangus = Transaction::where('type', 'kredit')
            ->where('sisa_saldo', '>', 0)
            ->where('expired_at', '<', now()->toDateString())
            ->get();

        $jumlahHangus = 0;

        DB::beginTransaction();
        try {
            foreach ($kreditHangus as $kredit) {
                // 1. Buat transaksi debit sebagai catatan uang hangus
                Transaction::create([
                    'wallet_id'   => $kredit->wallet_id,
                    'type'        => 'debit',
                    'nominal'     => $kredit->sisa_saldo,
                    'operator_id' => 1, // Pastikan ID 1 adalah akun Superadmin/Sistem di database Anda
                    'keterangan'  => 'Saldo kedaluwarsa dari Topup tanggal ' . $kredit->created_at->format('d-m-Y')
                ]);

                // 2. Nol-kan sisa_saldo di data aslinya
                $kredit->update(['sisa_saldo' => 0]);
                $jumlahHangus++;
            }
            DB::commit();
            $this->info("Selesai! Berhasil menghanguskan {$jumlahHangus} riwayat topup.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
