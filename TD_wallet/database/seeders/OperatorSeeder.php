<?php

namespace Database\Seeders;

use App\Models\Operator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operators = [
            [
                'nama'     => 'Super Admin',
                'email'    => 'super.admin@gmail.com',
                'role'     => 'superadmin',
                'password' => 'password', // hashed
            ],
            [
                'nama'     => 'Admin Outlet',
                'email'    => 'admin@gmail.com',
                'role'     => 'admin',
                'password' => 'password',
            ],
            [
                'nama'     => 'Cashier',
                'email'    => 'kasir01@gmail.com',
                'role'     => 'kasir',
                'password' => 'password',
            ],
        ];

        foreach ($operators as $op) {
            Operator::create($op);
        }
    }
}
