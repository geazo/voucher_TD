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
        Schema::table('benefits', function (Blueprint $table) {
            $table->longText('tc')->nullable()->after('photo');
            $table->string('logo1')->nullable()->after('tc');
            $table->string('logo2')->nullable()->after('logo1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->dropColumn(['tc', 'logo1', 'logo2']);
        });
    }
};
