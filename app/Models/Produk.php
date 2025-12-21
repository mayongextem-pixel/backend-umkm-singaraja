<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    // Tambahkan baris ini karena nama tabel Anda 'umkms'
    protected $table = 'umkms';

    protected $fillable = [
        'nama_produk',
        'harga',
        'kategori',
        'deskripsi',
        'whatsapp',
        'alamat',
        'foto_produk',
        'skala_usaha' // Pastikan ini juga ada jika ingin digunakan
    ];
}