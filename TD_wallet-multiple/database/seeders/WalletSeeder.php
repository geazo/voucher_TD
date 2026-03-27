<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Wallet;
use App\Models\Operator;
use App\Models\Membership;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil semua customer (tanpa load relasi membership karena sudah dihapus)
        $customers = Customer::all();
        $operatorId = Operator::first()->id ?? 1;

        // 2. Ambil semua data membership yang tersedia
        $memberships = Membership::all();

        foreach ($customers as $customer) {

            $randomMembership =  null;

            $membershipId = null;
            $prefix       = 'CS';

            // 4. Generate nomor rekening menggunakan Prefix dari tier tersebut
            $rekeningUtama = Wallet::generateNoRekening($prefix);

            // 5. Buatkan Wallet Tipe Uang
            Wallet::firstOrCreate([
                'customer_id'   => $customer->id,
                'membership_id' => $membershipId, // Masukkan ID Membership ke sini
                'type'          => 'Uang',
            ], [
                'no_rekening' => $rekeningUtama,
                'operator_id' => $operatorId,
            ]);

            // 6. Buatkan Wallet Tipe Poin
            Wallet::firstOrCreate([
                'customer_id'   => $customer->id,
                'membership_id' => $membershipId, // Masukkan ID Membership ke sini
                'type'          => 'Poin',
            ], [
                'no_rekening' => $rekeningUtama, // Nomor rekening sama dengan Uang
                'operator_id' => $operatorId,
            ]);
        }
    }
}
