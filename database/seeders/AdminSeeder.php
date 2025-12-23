<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'adminumkm@gmail.com',
            'password' => Hash::make('adminumkm123'),
            'role' => 'admin',
        ]);

        echo "✅ Admin berhasil dibuat!\n";
        echo "Email: adminumkm@gmail.com\n";
        echo "Password: adminumkm123\n";
    }
}
