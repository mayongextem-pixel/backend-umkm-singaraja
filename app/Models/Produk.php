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
        'skala_usaha',
        'user_id',     // Link to owner
        'status'       // Approval status
    ];

    // Relationship: UMKM belongs to a User (owner)
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Status constants for easy reference
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
}