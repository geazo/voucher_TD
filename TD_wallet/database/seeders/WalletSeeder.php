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
        // Ambil semua customer
        $customers = Customer::all();

        // Ambil ID operator pertama (misal: Superadmin) untuk penanggung jawab pembuatan dompet
        $operatorId = Operator::first()->id ?? 1;

        foreach ($customers as $customer) {
            // 1. Buatkan Wallet Tipe Uang
            Wallet::firstOrCreate([
                'customer_id' => $customer->id,
                'type'        => 'Uang',
            ], [
                'operator_id' => $operatorId,
            ]);

            // 2. Buatkan Wallet Tipe Poin
            Wallet::firstOrCreate([
                'customer_id' => $customer->id,
                'type'        => 'Poin',
            ], [
                'operator_id' => $operatorId,
            ]);
        }
    }
}
