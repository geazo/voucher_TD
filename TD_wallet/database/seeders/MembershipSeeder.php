<?php

namespace Database\Seeders;

use App\Models\Membership;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $pakets = [
            [
                'name'           => 'Reguler',
                'harga'          => 2000000,
                'bonus_topup'    => 5,
                'diskon_belanja' => 5,
            ],
            [
                'name'           => 'Silver',
                'harga'          => 5000000,
                'bonus_topup'    => 10,
                'diskon_belanja' => 10,
            ],
            [
                'name'           => 'Gold',
                'harga'          => 10000000,
                'bonus_topup'    => 20,
                'diskon_belanja' => 20,
            ],
            [
                'name'           => 'Platinum',
                'harga'          => 50000000,
                'bonus_topup'    => 40,
                'diskon_belanja' => 40,
            ],
        ];

        foreach ($pakets as $paket) {
            Membership::create($paket);
        }
    }
}
