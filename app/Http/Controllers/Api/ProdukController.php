<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    /**
     * Menampilkan semua UMKM di Katalog
     */
    public function index()
    {
        try {
            // Mengambil semua data UMKM, yang terbaru muncul di atas
            $produks = DB::table('umkms')->orderBy('created_at', 'desc')->get();
            
            // Format URL foto agar bisa langsung tampil di Next.js
            $produks = $produks->map(function ($item) {
                $item->foto_produk = $item->foto_produk 
                    ? url('/storage/produks/' . $item->foto_produk) 
                    : "https://via.placeholder.com/400x300?text=No+Image"; 
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar Data Produk UMKM Singaraja',
                'data'    => $produks  
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan Pendaftaran UMKM Baru
     */
    public function store(Request $request)
    {
        // 1. Validasi Ketat: foto_produk wajib sebagai bukti legalitas/usaha
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255|unique:umkms,nama_produk',
            'harga'       => 'required|numeric',
            'kategori'    => 'required|string', 
            'skala_usaha' => 'required|string', 
            'deskripsi'   => 'required|string',
            'whatsapp'    => 'required|string',
            'alamat'      => 'required|string',
            'foto_produk' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran Gagal. Periksa kembali form anda.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // 2. Olah Foto (Sertifikat/Bukti Usaha)
            $imageName = null;
            if ($request->hasFile('foto_produk')) {
                $image = $request->file('foto_produk');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                // Simpan ke folder: storage/app/public/produks
                $image->storeAs('public/produks', $imageName);
            }

            // 3. Simpan ke Database
            $id = DB::table('umkms')->insertGetId([
                'nama_produk' => $request->nama_produk,
                'harga'       => $request->harga,
                'kategori'    => $request->kategori,
                'skala_usaha' => $request->skala_usaha,
                'deskripsi'   => $request->deskripsi,
                'whatsapp'    => $request->whatsapp,
                'alamat'      => $request->alamat,
                'foto_produk' => $imageName, 
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'UMKM Berhasil Terdaftar!',
                'data'    => DB::table('umkms')->where('id', $id)->first()
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan Detail Satu UMKM
     */
    public function show($id)
    {
        $produk = DB::table('umkms')->where('id', $id)->first();

        if (!$produk) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $produk->foto_produk = $produk->foto_produk 
            ? url('/storage/produks/' . $produk->foto_produk) 
            : "https://via.placeholder.com/400x300?text=No+Image";

        return response()->json([
            'success' => true,
            'data'    => $produk
        ], 200);
    }

    /**
     * Menghapus Data UMKM (Digunakan di Dashboard Admin)
     */
    public function destroy($id)
    {
        try {
            $produk = DB::table('umkms')->where('id', $id)->first();

            if (!$produk) {
                return response()->json(['success' => false, 'message' => 'Data tidak ada'], 404);
            }

            // Hapus File Foto dari Storage agar tidak memenuhi memori
            if ($produk->foto_produk) {
                Storage::delete('public/produks/' . $produk->foto_produk);
            }

            // Hapus Baris di Database
            DB::table('umkms')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'UMKM dan Bukti Usaha Berhasil Dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }
}