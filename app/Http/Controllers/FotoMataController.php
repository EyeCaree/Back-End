<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FotoMata;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FotoMataController extends Controller
{
    // Menampilkan semua foto
    public function index()
    {
        $fotos = FotoMata::all();
        return response()->json($fotos);
    }

    // Menampilkan foto berdasarkan ID
    public function show($id)
    {
        $foto = FotoMata::find($id);
        if (!$foto) {
            return response()->json(['message' => 'Foto tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $foto->id,
            'user_id' => $foto->user_id,
            'file_path' => asset($foto->file_path), // akses URL publik
            'upload_date' => $foto->upload_date,
            'created_at' => $foto->created_at,
            'updated_at' => $foto->updated_at
        ]);
    }

    // Menambahkan foto baru
    public function store(Request $request)
    {
        // Cek apakah ada file
        if (!$request->hasFile('file_path')) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan dalam request'
            ], 400);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'file_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Simpan gambar ke storage/app/public/uploads
        $image = $request->file('file_path');
        $imagePath = $image->store('uploads', 'public');

        // Simpan ke DB
        $foto = FotoMata::create([
            'user_id' => $request->user_id,
            'file_path' => 'storage/' . $imagePath, // agar bisa diakses publik
            'upload_date' => now(),
        ]);

        return response()->json([
            'message' => 'Foto berhasil diunggah!',
            'data' => $foto
        ]);
    }

    // Memperbarui data foto
    public function update(Request $request, $id)
    {
        $foto = FotoMata::find($id);
        if (!$foto) {
            return response()->json(['message' => 'Foto tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'file_path' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ganti file jika ada file baru
        if ($request->hasFile('file_path')) {
            // Hapus file lama
            Storage::disk('public')->delete(str_replace('storage/', '', $foto->file_path));

            // Simpan file baru
            $imagePath = $request->file('file_path')->store('uploads', 'public');
            $foto->file_path = 'storage/' . $imagePath;
        }

        $foto->save();
        return response()->json($foto);
    }

    // Menghapus foto
    public function destroy($id)
    {
        $foto = FotoMata::find($id);
        if (!$foto) {
            return response()->json(['message' => 'Foto tidak ditemukan'], 404);
        }

        // Hapus file dari storage
        Storage::disk('public')->delete(str_replace('storage/', '', $foto->file_path));

        $foto->delete();
        return response()->json(['message' => 'Foto berhasil dihapus']);
    }
}