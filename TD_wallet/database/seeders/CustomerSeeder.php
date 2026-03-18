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
                'notelp'        => '081234567890',
                'password'      => 'password', // hashed
                'f_aktif'       => true,
            ],
            [
                'membership_id' => null,
                'nama'          => 'Siti Aminah',
                'email'         => 'siti@gmail.com',
                'notelp'        => '089876543210',
                'password'      => 'password',
                'f_aktif'       => true,
            ],
            [
                'membership_id' => null,
                'nama'          => 'Andi Wijaya',
                'email'         => 'andi@gmail.com',
                'notelp'        => '085678901234',
                'password'      => 'password',
                'f_aktif'       => true,
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
