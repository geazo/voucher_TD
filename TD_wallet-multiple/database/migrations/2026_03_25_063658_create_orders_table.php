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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Contoh: INV-20260325-001
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('operator_id')->constrained('operators'); // ID Kasir

            $table->bigInteger('total_tagihan'); // Total sebelum diskon poin
            $table->bigInteger('bayar_uang')->default(0); // Porsi yang dibayar pakai Uang
            $table->bigInteger('bayar_poin')->default(0); // Porsi yang dibayar pakai Poin

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
