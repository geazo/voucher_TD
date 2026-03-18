<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer; // Pastikan Model Customer sudah ada
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'name'    => 'Budi Santoso',
            'email'   => 'budi@example.com',
            'notelp'  => '08123456789',
            'PIN'     => Hash::make('123456'), // PIN aman dengan hashing
            'F_aktif' => true,
        ]);

        // Contoh data non-aktif
        Customer::create([
            'name'    => 'Siti Aminah',
            'email'   => 'siti@example.com',
            'notelp'  => '08987654321',
            'PIN'     => Hash::make('654321'),
            'F_aktif' => false,
        ]);
    }
}
