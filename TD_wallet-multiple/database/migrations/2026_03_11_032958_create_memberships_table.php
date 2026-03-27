<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // Tambahkan kolom harga dengan kapasitas desimal besar (contoh: 50.000.000,00)
            $table->decimal('harga', 15, 2);
            $table->decimal('bonus_topup', 5, 2)->default(0);
            $table->decimal('diskon_belanja', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
