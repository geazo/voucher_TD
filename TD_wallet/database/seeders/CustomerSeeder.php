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
                'membership_id' => null, // Dikosongkan
                'nama'          => 'Budi Santoso',
                'email'         => 'budi@gmail.com',
                'notelp'        => '+6281234567890',
                'password'      => 'password', // hashed secara otomatis jika di Model menggunakan mutator/casts, atau biarkan plain jika logika hash Anda ada di controller saat login
                'f_aktif'       => true,
                'kota_domisili' => 'Bandung',
                'gender'        => 'Laki-laki',
            ],
            [
                'membership_id' => null,
                'nama'          => 'Siti Aminah',
                'email'         => 'siti@gmail.com',
                'notelp'        => '+6289876543210',
                'password'      => 'password',
                'f_aktif'       => true,
                'kota_domisili' => 'Surabaya',
                'gender'        => 'Perempuan',
            ],
            [
                'membership_id' => null,
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
