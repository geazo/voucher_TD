<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Menambahkan kolom kota_domisili dan gender (nullable agar tidak error pada data lama)
            $table->string('kota_domisili')->nullable()->after('email');
            $table->enum('gender', ['Laki-laki', 'Perempuan','lainnya'])->nullable()->after('kota_domisili');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['kota_domisili', 'gender']);
        });
    }
};
