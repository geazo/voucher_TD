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
        Schema::create('balance_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

            // ID transaksi Topup yang ingin diperpanjang
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');

            $table->text('alasan'); // Alasan customer
            $table->integer('tambahan_hari')->default(30); // Default perpanjangan 30 hari

            // Status pengajuan: pending, approved, rejected
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Admin yang memproses
            $table->foreignId('operator_id')->nullable()->constrained('operators')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_extensions');
    }
};
