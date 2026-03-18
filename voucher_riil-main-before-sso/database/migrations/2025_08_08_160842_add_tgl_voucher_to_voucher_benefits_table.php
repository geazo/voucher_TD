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
        Schema::table('voucher_benefits', function (Blueprint $table) {
            $table->date('tgl_terbit_voucher')->nullable()->after('discan');
            $table->date('tgl_exp_voucher')->nullable()->after('tgl_terbit_voucher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voucher_benefits', function (Blueprint $table) {
            $table->dropColumn(['tgl_terbit_voucher', 'tgl_exp_voucher']);
        });
    }
};
