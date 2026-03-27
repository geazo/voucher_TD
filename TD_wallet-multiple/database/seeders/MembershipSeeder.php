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
                'prefix'         => 'RG',
                'harga'          => 2000000,
                'bonus_topup'    => 5,
                'diskon_belanja' => 5,
                'color_start'    => '#146c43',
                'color_end'      => '#0a3622',
                'text_color'     => 'text-white',
            ],
            [
                'name'           => 'Silver',
                'prefix'         => 'SL',
                'harga'          => 5000000,
                'bonus_topup'    => 10,
                'diskon_belanja' => 10,
                'color_start'    => '#95a5a6',
                'color_end'      => '#4a4e69',
                'text_color'     => 'text-white',
            ],
            [
                'name'           => 'Gold',
                'prefix'         => 'GL',
                'harga'          => 10000000,
                'bonus_topup'    => 20,
                'diskon_belanja' => 20,
                'color_start'    => '#d4af37',
                'color_end'      => '#8b6508',
                'text_color'     => 'text-white',
            ],
            [
                'name'           => 'Platinum',
                'prefix'         => 'PL',
                'harga'          => 50000000,
                'bonus_topup'    => 40,
                'diskon_belanja' => 40,
                'color_start'    => '#2b2d42',
                'color_end'      => '#11121a',
                'text_color'     => 'text-white',
            ],
        ];

        foreach ($pakets as $paket) {
            // Menggunakan updateOrCreate agar aman jika di-run berkali-kali (tidak duplikat)
            Membership::updateOrCreate(
                ['name' => $paket['name']], // Cari berdasarkan nama
                $paket // Update/Create dengan data ini
            );
        }
    }
}
