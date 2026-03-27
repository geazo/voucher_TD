<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'nama'          => 'Budi Santoso',
                'email'         => 'budi@gmail.com',
                'notelp'        => '+6281234567890',
                'password'      => 'password',
                'f_aktif'       => true,
                'kota_domisili' => 'Bandung',
                'gender'        => 'Laki-laki',
            ],
            [
                'nama'          => 'Siti Aminah',
                'email'         => 'siti@gmail.com',
                'notelp'        => '+6289876543210',
                'password'      => 'password',
                'f_aktif'       => true,
                'kota_domisili' => 'Surabaya',
                'gender'        => 'Perempuan',
            ],
            [
                'nama'          => 'Andi Wijaya',
                'email'         => 'andi@gmail.com',
                'notelp'        => '+6285678901234',
                'password'      => 'password',
                'f_aktif'       => true,
                'kota_domisili' => 'Jakarta',
                'gender'        => 'Laki-laki',
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
