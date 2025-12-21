<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel umkms dari nol.
     */
    public function up(): void
    {
        Schema::create('umkms', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->integer('harga')->default(0);
            $table->string('kategori');
            $table->text('deskripsi');
            $table->string('whatsapp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_produk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi (Hapus tabel).
     */
    public function down(): void
    {
        Schema::dropIfExists('umkms');
    }
};