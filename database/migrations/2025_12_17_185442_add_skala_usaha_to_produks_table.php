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
        Schema::table('umkms', function (Blueprint $table) {
            // Kita tambahkan kolom skala_usaha, defaultnya 'Mikro'
            // agar data lama tidak error saat migration dijalankan
            $table->string('skala_usaha')->default('Mikro')->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            $table->dropColumn('skala_usaha');
        });
    }
};