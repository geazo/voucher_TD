<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;
use App\Models\Benefit;

class BenefitPadelSeeder extends Seeder
{
    public function run(): void
    {
        $outlet = Outlet::where('kode', 'PDM')->first();

        if (!$outlet) {
            throw new \RuntimeException('Outlet Padel Malang belum ada. Jalankan OutletSeeder terlebih dahulu atau buat via UI.');
        }

        $items = [
            [
                'name'       => 'Voucher lunch or dinner at 209 Dining for 2 persons',
                'kode'       => '209D',
                'redemption' => '209 Dining',
                'status'     => null,
            ],
            [
                'name'       => 'Voucher lunch or dinner at Chamas Brazilian Churrascaria for 2 persons',
                'kode'       => 'CH',
                'redemption' => 'Chamas Brazilian Churrascaria, Lobby Floor',
                'status'     => null,
            ],
            [
                'name'       => 'Voucher all you can eat dimsum at XFH Cuisine for 2 persons',
                'kode'       => 'XFH',
                'redemption' => 'Xiang Fu Hai Cuisine, 6th Lobby Floor',
                'status'     => null,
            ],
        ];

        foreach ($items as $i) {
            Benefit::firstOrCreate(
                ['outlet_id' => $outlet->id, 'kode' => $i['kode']],
                [
                    'name'       => $i['name'],
                    'status'     => $i['status'],
                    'redemption' => $i['redemption'],
                    'kode'       => $i['kode'],
                    // opsional:
                    // 'photo' => 'benefits/xxx.jpg',
                    // 'tc'    => 'S&K ...',
                    // 'logo1' => null,
                    // 'logo2' => null,
                ]
            );
        }
    }
}
