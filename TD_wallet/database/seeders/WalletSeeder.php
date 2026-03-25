<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Wallet;
use App\Models\Operator;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::with('membership')->get();
        $operatorId = Operator::first()->id ?? 1;

        foreach ($customers as $customer) {
            $tierName = $customer->membership->name ?? null;

            // Generate nomor rekening SATU KALI untuk customer ini
            $rekeningUtama = Wallet::generateNoRekening($tierName);

            // 1. Buatkan Wallet Tipe Uang
            Wallet::firstOrCreate([
                'customer_id' => $customer->id,
                'type'        => 'Uang',
            ], [
                'no_rekening' => $rekeningUtama, // Pakai rekening utama
                'operator_id' => $operatorId,
            ]);

            // 2. Buatkan Wallet Tipe Poin
            Wallet::firstOrCreate([
                'customer_id' => $customer->id,
                'type'        => 'Poin',
            ], [
                'no_rekening' => $rekeningUtama, // Pakai rekening utama yang SAMA
                'operator_id' => $operatorId,
            ]);
        }
    }
}
