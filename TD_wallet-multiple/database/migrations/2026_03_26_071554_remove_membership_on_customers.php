<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Hapus membership_id dari customers
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->dropColumn('membership_id');
        });

        // 2. Tambahkan membership_id ke wallets
        Schema::table('wallets', function (Blueprint $table) {
            $table->foreignId('membership_id')->nullable()->after('customer_id')->constrained('memberships');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
