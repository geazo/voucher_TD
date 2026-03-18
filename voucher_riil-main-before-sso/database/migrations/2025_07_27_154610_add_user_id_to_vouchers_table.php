<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('outlet_id')
                ->constrained('penerimas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Pertama drop foreign key-nya dulu
            $table->dropForeign(['user_id']);
            // Lalu hapus kolomnya
            $table->dropColumn('user_id');
        });
    }
};
