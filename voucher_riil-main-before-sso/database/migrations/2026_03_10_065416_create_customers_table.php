<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // ID (Primary Key)
            $table->string('name');
            $table->string('email')->unique();
            $table->string('notelp', 15);
            $table->string('PIN'); // Sebaiknya di-hash saat simpan
            $table->integer('f_aktif')->default(1); // Flag aktif
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
