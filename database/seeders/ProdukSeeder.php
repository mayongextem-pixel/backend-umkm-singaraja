<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    \App\Models\Produk::create([
        'nama_produk' => 'Kopi Banyuatis',
        'harga' => 35000,
        'foto_produk' => 'kopi.jpg',
        'deskripsi' => 'Kopi khas Singaraja',
        'kategori' => 'Minuman'
    ]);
}
}
