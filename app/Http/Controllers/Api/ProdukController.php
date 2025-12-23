<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Produk;

class ProdukController extends Controller
{
    /**
     * Get all APPROVED UMKM (for public katalog)
     */
    public function index()
    {
        try {
            $produks = Produk::where('status', Produk::STATUS_APPROVED)
                ->orderBy('created_at', 'desc')
                ->get();

            // Format URL foto
            $produks = $produks->map(function ($item) {
                $item->foto_produk = $item->foto_produk
                    ? url('/storage/produks/' . $item->foto_produk)
                    : "https://via.placeholder.com/400x300?text=No+Image";
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar UMKM yang Disetujui',
                'data' => $produks
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ALL UMKM regardless of status (Admin only)
     */
    public function indexAll(Request $request)
    {
        try {
            // Check if user is admin
            if ($request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin only.'
                ], 403);
            }

            $produks = Produk::with('user')->orderBy('created_at', 'desc')->get();

            // Format URL foto
            $produks = $produks->map(function ($item) {
                $item->foto_produk = $item->foto_produk
                    ? url('/storage/produks/' . $item->foto_produk)
                    : "https://via.placeholder.com/400x300?text=No+Image";
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Semua Data UMKM',
                'data' => $produks
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own UMKM submissions
     */
    public function mySubmissions(Request $request)
    {
        try {
            $produks = Produk::where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Format URL foto
            $produks = $produks->map(function ($item) {
                $item->foto_produk = $item->foto_produk
                    ? url('/storage/produks/' . $item->foto_produk)
                    : "https://via.placeholder.com/400x300?text=No+Image";
                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar UMKM Anda',
                'data' => $produks
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new UMKM (status = pending for users, approved for admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'kategori' => 'required|string',
            'skala_usaha' => 'required|string',
            'deskripsi' => 'required|string',
            'whatsapp' => 'required|string',
            'alamat' => 'required|string',
            'foto_produk' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle image upload
            $imageName = null;
            if ($request->hasFile('foto_produk')) {
                $image = $request->file('foto_produk');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/produks', $imageName);
            }

            // Determine status based on user role
            $status = ($request->user()->role === 'admin')
                ? Produk::STATUS_APPROVED
                : Produk::STATUS_PENDING;

            // Create UMKM
            $produk = Produk::create([
                'nama_produk' => $request->nama_produk,
                'harga' => $request->harga,
                'kategori' => $request->kategori,
                'skala_usaha' => $request->skala_usaha,
                'deskripsi' => $request->deskripsi,
                'whatsapp' => $request->whatsapp,
                'alamat' => $request->alamat,
                'foto_produk' => $imageName,
                'user_id' => $request->user()->id,
                'status' => $status,
            ]);

            return response()->json([
                'success' => true,
                'message' => $status === Produk::STATUS_PENDING
                    ? 'UMKM berhasil didaftarkan! Menunggu persetujuan admin.'
                    : 'UMKM berhasil ditambahkan!',
                'data' => $produk
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single UMKM detail
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Only show approved UMKM to public
        if ($produk->status !== Produk::STATUS_APPROVED) {
            return response()->json([
                'success' => false,
                'message' => 'UMKM belum disetujui'
            ], 403);
        }

        $produk->foto_produk = $produk->foto_produk
            ? url('/storage/produks/' . $produk->foto_produk)
            : "https://via.placeholder.com/400x300?text=No+Image";

        return response()->json([
            'success' => true,
            'data' => $produk
        ], 200);
    }

    /**
     * Update UMKM (Admin can edit any, users can edit only their own pending)
     */
    public function update(Request $request, $id)
    {
        try {
            $produk = Produk::find($id);

            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Authorization check
            $user = $request->user();
            if ($user->role !== 'admin' && $produk->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengedit UMKM ini'
                ], 403);
            }

            // Users can only edit pending submissions
            if ($user->role !== 'admin' && $produk->status !== Produk::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya UMKM dengan status pending yang bisa diedit'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'nama_produk' => 'sometimes|required|string|max:255',
                'harga' => 'sometimes|required|numeric',
                'kategori' => 'sometimes|required|string',
                'skala_usaha' => 'sometimes|required|string',
                'deskripsi' => 'sometimes|required|string',
                'whatsapp' => 'sometimes|required|string',
                'alamat' => 'sometimes|required|string',
                'foto_produk' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update fields
            if ($request->has('nama_produk'))
                $produk->nama_produk = $request->nama_produk;
            if ($request->has('harga'))
                $produk->harga = $request->harga;
            if ($request->has('kategori'))
                $produk->kategori = $request->kategori;
            if ($request->has('skala_usaha'))
                $produk->skala_usaha = $request->skala_usaha;
            if ($request->has('deskripsi'))
                $produk->deskripsi = $request->deskripsi;
            if ($request->has('whatsapp'))
                $produk->whatsapp = $request->whatsapp;
            if ($request->has('alamat'))
                $produk->alamat = $request->alamat;

            // Handle new image upload
            if ($request->hasFile('foto_produk')) {
                // Delete old image
                if ($produk->foto_produk) {
                    Storage::delete('public/produks/' . $produk->foto_produk);
                }

                $image = $request->file('foto_produk');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/produks', $imageName);
                $produk->foto_produk = $imageName;
            }

            $produk->save();

            return response()->json([
                'success' => true,
                'message' => 'UMKM berhasil diperbarui',
                'data' => $produk
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve UMKM (Admin only)
     */
    public function approve(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin only.'
                ], 403);
            }

            $produk = Produk::find($id);

            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $produk->status = Produk::STATUS_APPROVED;
            $produk->save();

            return response()->json([
                'success' => true,
                'message' => 'UMKM berhasil disetujui'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject UMKM (Admin only)
     */
    public function reject(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin only.'
                ], 403);
            }

            $produk = Produk::find($id);

            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $produk->status = Produk::STATUS_REJECTED;
            $produk->save();

            return response()->json([
                'success' => true,
                'message' => 'UMKM ditolak'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete UMKM (Admin or owner)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $produk = Produk::find($id);

            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ada'
                ], 404);
            }

            // Authorization: admin or owner
            $user = $request->user();
            if ($user->role !== 'admin' && $produk->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus UMKM ini'
                ], 403);
            }

            // Delete image file
            if ($produk->foto_produk) {
                Storage::delete('public/produks/' . $produk->foto_produk);
            }

            $produk->delete();

            return response()->json([
                'success' => true,
                'message' => 'UMKM berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }
}