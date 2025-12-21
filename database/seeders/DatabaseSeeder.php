<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat User Test (Bawaan Laravel)
        User::factory()->create([
            'name' => 'Admin UMKM Singaraja',
            'email' => 'admin@singaraja.com',
        ]);

        // Memanggil ProdukSeeder agar data produk masuk ke database
        $this->call([
            ProdukSeeder::class,
        ]);
    }
}