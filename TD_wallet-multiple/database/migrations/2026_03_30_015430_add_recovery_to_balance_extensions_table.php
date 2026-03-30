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
        Schema::table('balance_extensions', function (Blueprint $table) {
            // Tambahkan kolom ini setelah kolom 'tambahan_hari' agar rapi
            $table->boolean('is_recovery')->default(false)->after('tambahan_hari');
            $table->decimal('nominal_uang', 15, 2)->default(0)->after('is_recovery');
            $table->decimal('nominal_poin', 15, 2)->default(0)->after('nominal_uang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_extensions', function (Blueprint $table) {
            // Hapus kembali jika di-rollback
            $table->dropColumn(['is_recovery', 'nominal_uang', 'nominal_poin']);
        });
    }
};
