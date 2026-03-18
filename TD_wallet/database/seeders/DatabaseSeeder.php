<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MembershipSeeder::class,
            CustomerSeeder::class,
            OperatorSeeder::class,
            WalletSeeder::class,
        ]);
    }
}
