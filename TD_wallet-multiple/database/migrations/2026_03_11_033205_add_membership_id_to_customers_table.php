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
        Schema::table('customers', function (Blueprint $table) {
            // Menambahkan foreign key membership_id
            // nullable() digunakan agar customer lama atau baru yang belum punya tier tidak error
            // nullOnDelete() agar jika suatu membership dihapus, data customer tetap aman (hanya membership_id nya yang jadi NULL)
            $table->foreignId('membership_id')
                  ->nullable()
                  ->after('id') // Menempatkan kolom ini setelah kolom ID (opsional agar rapi di database)
                  ->constrained('memberships')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->dropColumn('membership_id');
        });
    }
};
