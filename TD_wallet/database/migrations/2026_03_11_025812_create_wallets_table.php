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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Uang', 'Poin']);
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade'); // Operator yang membuat wallet
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
