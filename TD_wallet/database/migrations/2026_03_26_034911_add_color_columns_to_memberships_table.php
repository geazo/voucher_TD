<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            // Kita set default ke hijau khas Taman Dayu agar data lama tidak error
            $table->string('color_start', 10)->default('#146c43')->after('diskon_belanja');
            $table->string('color_end', 10)->default('#0a3622')->after('color_start');
            $table->enum('text_color', ['text-white', 'text-dark'])->default('text-white')->after('color_end');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn(['color_start', 'color_end', 'text_color']);
        });
    }
};
