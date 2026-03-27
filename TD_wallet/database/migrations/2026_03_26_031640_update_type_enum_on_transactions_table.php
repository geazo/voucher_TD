<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ubah enum untuk menambahkan 'adjustment'
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('kredit', 'debit', 'adjustment') NOT NULL DEFAULT 'debit'");
    }

    public function down()
    {
        // Kembalikan ke semula jika di-rollback
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('kredit', 'debit') NOT NULL DEFAULT 'debit'");
    }
};
