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
        Schema::create('webcams', function (Blueprint $table) {
            $table->id();
            $table->string('kategori');
            $table->bigInteger('nik');
            $table->string('nama_depan');
            $table->string('nama_belakang');
            $table->string('perusahaan');
            $table->string('sim')->nullable();
            $table->string('no_hp');
            $table->dateTime('jam_masuk')->nullable();
            $table->dateTime('jam_keluar')->nullable();
            // $table->string('tujuan');
            $table->string('visit_location')->nullable();
            $table->string('access_card')->nullable();

            $table->string('foto_wajah')->nullable(); // Column for storing face photo file path/URL
            $table->string('foto_ktp'); // Column for storing KTP photo file path/URL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webcams');
    }
};
